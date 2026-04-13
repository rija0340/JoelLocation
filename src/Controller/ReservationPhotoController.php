<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\ReservationPhoto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\ReservationPhotoRepository;

class ReservationPhotoController extends AbstractController
{
    private $entityManager;
    private $resaPhotoRepo;

    public function __construct(EntityManagerInterface $entityManager, ReservationPhotoRepository $resaPhotoRepo)
    {
        $this->entityManager = $entityManager;
        $this->resaPhotoRepo = $resaPhotoRepo;
    }

    /**
     * @Route("/backoffice/reservation/{id}/photos/upload", name="reservation_photos_upload", methods={"POST"})
     */
    public function uploadPhotos(Request $request, Reservation $reservation): JsonResponse
    {
        $uploadedFiles = $request->files->get('photos', []);
        $type = $request->request->get('type', 'depart');

        if ($uploadedFiles instanceof UploadedFile) {
            $uploadedFiles = [$uploadedFiles];
        }

        if (!$uploadedFiles) {
            return new JsonResponse(['error' => 'Aucun fichier reçu'], 400);
        }

        $uploadedPhotos = [];
        $exifDates = $request->request->all('exifDate') ?? [];

        $index = 0;
        foreach ($uploadedFiles as $uploadedFile) {
            /** @var UploadedFile $uploadedFile */
            if ($uploadedFile->isValid()) {
                // Fallback: date actuelle si pas de date EXIF
                $dateText = $exifDates[$index] ?? (new \DateTimeImmutable())->format('d/m/Y H:i');

                // Add date watermark to image before saving
                $watermarkedFile = $this->addDateWatermark($uploadedFile, $dateText);

                $photo = new ReservationPhoto();
                $photo->setReservation($reservation);
                $photo->setImageFile($watermarkedFile);
                $photo->setType($type);

                $this->entityManager->persist($photo);
                $uploadedPhotos[] = [
                    'id' => null,
                    'name' => $uploadedFile->getClientOriginalName()
                ];
            }
            $index++;
        }

        $this->entityManager->flush();

        // Return only photos of the requested type
        $photosList = $this->resaPhotoRepo->findBy([
            'reservation' => $reservation,
            'type' => $type
        ]);

        $result = [];
        foreach ($photosList as $photo) {
            $result[] = [
                'id' => $photo->getId(),
                'image' => $photo->getImage(),
                'url' => $photo->getImageUrl(),
                'uploadDate' => $photo->getUpdatedAt() ? $photo->getUpdatedAt()->format('d/m/Y H:i') : 'N/A'
            ];
        }

        return new JsonResponse([
            'success' => true,
            'photos' => $result,
            'message' => count($uploadedPhotos) . ' photo(s) uploadée(s) avec succès'
        ]);
    }

    /**
     * @Route("/backoffice/reservation/photo/{id}/delete", name="reservation_photo_delete", methods={"DELETE"})
     */
    public function deletePhoto(int $id): JsonResponse
    {

        $photo = $this->resaPhotoRepo->find($id);

        if (!$photo) {
            return new JsonResponse(['error' => 'Photo not found'], 404);
        }
        try {
            $this->entityManager->remove($photo);
            $this->entityManager->flush();

            return new JsonResponse(['success' => true, 'message' => 'Photo supprimée avec succès']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la suppression'], 500);
        }
    }
    /**
     * @Route("/backoffice/reservation/{id}/photos", name="reservation_photos_list", methods={"GET"})
     */
    public function listPhotos(Request $request, Reservation $reservation): JsonResponse
    {
        $type = $request->query->get('type');

        $criteria = ['reservation' => $reservation];
        if ($type) {
            $criteria['type'] = $type;
        }

        $photosList = $this->resaPhotoRepo->findBy($criteria, ['updatedAt' => 'DESC']);

        $photos = [];
        foreach ($photosList as $photo) {
            $photos[] = [
                'id' => $photo->getId(),
                'image' => $photo->getImage(),
                'url' => $photo->getImageUrl(),
                'type' => $photo->getType(),
                'uploadDate' => $photo->getUpdatedAt() ? $photo->getUpdatedAt()->format('d/m/Y H:i') : 'N/A'
            ];
        }

        return new JsonResponse(['photos' => $photos]);
    }

    /**
     * Add date watermark to image (bottom-left corner)
     */
    private function addDateWatermark(UploadedFile $file, string $dateText): UploadedFile
    {
        try {
            $filePath = $file->getPathname();
            $imageType = @exif_imagetype($filePath);

            if (!in_array($imageType, [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF], true)) {
                return $file;
            }

            $exifData = $imageType === IMAGETYPE_JPEG ? @exif_read_data($filePath) : [];

            $captureDate = $this->extractCaptureDate($exifData, $dateText);

            if ($captureDate === null) {
                return $file;
            }

            $image = $this->createImageResource($filePath, $imageType);
            if (!$image) {
                return $file;
            }

            if ($imageType === IMAGETYPE_JPEG) {
                $image = $this->applyExifOrientation($image, $exifData);
            }

            if (in_array($imageType, [IMAGETYPE_PNG, IMAGETYPE_GIF], true)) {
                imagealphablending($image, true);
                imagesavealpha($image, true);
            }

            $this->drawDateWatermark($image, $captureDate->format('d/m/Y H:i'));

            $watermarkedFile = $this->createWatermarkedUploadedFile($image, $file, $imageType);
            imagedestroy($image);

            return $watermarkedFile ?: $file;
        } catch (\Throwable $e) {
            return $file;
        }
    }

    private function extractCaptureDate(array $exifData, string $dateText): ?\DateTimeImmutable
    {
        // First try EXIF from file (in case file wasn't compressed)
        foreach (['DateTimeOriginal', 'DateTimeDigitized', 'DateTime'] as $key) {
            if (empty($exifData[$key]) || !is_string($exifData[$key])) {
                continue;
            }

            $captureDate = \DateTimeImmutable::createFromFormat('Y:m:d H:i:s', $exifData[$key]);
            if ($captureDate instanceof \DateTimeImmutable) {
                return $captureDate;
            }

            $timestamp = strtotime($exifData[$key]);
            if ($timestamp !== false) {
                return (new \DateTimeImmutable())->setTimestamp($timestamp);
            }
        }

        // Fallback: use dateText from client-side EXIF (before compression)
        $parsed = \DateTimeImmutable::createFromFormat('d/m/Y H:i', $dateText);
        if ($parsed !== false) {
            return $parsed;
        }

        return null;
    }

    private function createImageResource(string $filePath, int $imageType)
    {
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                return @imagecreatefromjpeg($filePath);
            case IMAGETYPE_PNG:
                return @imagecreatefrompng($filePath);
            case IMAGETYPE_GIF:
                return @imagecreatefromgif($filePath);
            default:
                return false;
        }
    }

    private function applyExifOrientation($image, array $exifData)
    {
        if (empty($exifData['Orientation'])) {
            return $image;
        }

        switch ((int) $exifData['Orientation']) {
            case 2:
                imageflip($image, IMG_FLIP_HORIZONTAL);
                return $image;
            case 3:
                return imagerotate($image, 180, 0);
            case 4:
                imageflip($image, IMG_FLIP_VERTICAL);
                return $image;
            case 5:
                $rotated = imagerotate($image, -90, 0);
                imageflip($rotated, IMG_FLIP_HORIZONTAL);
                imagedestroy($image);
                return $rotated;
            case 6:
                $rotated = imagerotate($image, -90, 0);
                imagedestroy($image);
                return $rotated;
            case 7:
                $rotated = imagerotate($image, 90, 0);
                imageflip($rotated, IMG_FLIP_HORIZONTAL);
                imagedestroy($image);
                return $rotated;
            case 8:
                $rotated = imagerotate($image, 90, 0);
                imagedestroy($image);
                return $rotated;
            default:
                return $image;
        }
    }

    private function drawDateWatermark($image, string $dateText): void
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $shortestSide = min($width, $height);
        $padding = max(12, (int) round($shortestSide * 0.02));
        $backgroundPadding = max(8, (int) round($padding / 2));
        $fontFile = $this->findWatermarkFont();
        $backgroundColor = imagecolorallocatealpha($image, 0, 0, 0, 70);
        $textColor = imagecolorallocate($image, 255, 255, 255);

        if ($fontFile !== null && function_exists('imagettfbbox') && function_exists('imagettftext')) {
            $fontSize = max(16, min(72, (int) round($shortestSide * 0.025)));
            $bbox = @imagettfbbox($fontSize, 0, $fontFile, $dateText);

            if (is_array($bbox)) {
                $textWidth = abs($bbox[4] - $bbox[0]);
                $textHeight = abs($bbox[5] - $bbox[1]);
                $x = $padding;
                $y = $height - $padding;

                imagefilledrectangle(
                    $image,
                    $x - $backgroundPadding,
                    $y - $textHeight - $backgroundPadding,
                    $x + $textWidth + $backgroundPadding,
                    $y + $backgroundPadding,
                    $backgroundColor
                );

                @imagettftext($image, $fontSize, 0, $x, $y, $textColor, $fontFile, $dateText);
                return;
            }
        }

        $font = 5;
        $textWidth = imagefontwidth($font) * strlen($dateText);
        $textHeight = imagefontheight($font);
        $x = $padding;
        $y = $height - $padding - $textHeight;

        imagefilledrectangle(
            $image,
            $x - $backgroundPadding,
            $y - $backgroundPadding,
            $x + $textWidth + $backgroundPadding,
            $y + $textHeight + $backgroundPadding,
            $backgroundColor
        );

        imagestring($image, $font, $x, $y, $dateText, $textColor);
    }

    private function findWatermarkFont(): ?string
    {
        $fontPaths = [
            '/var/www/html/assets/fonts/Roboto/Roboto-Bold.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
            '/usr/share/fonts/truetype/freefont/FreeSansBold.ttf',
            '/usr/share/fonts/truetype/ubuntu/Ubuntu-B.ttf',
            __DIR__ . '/../../assets/fonts/DejaVuSans-Bold.ttf',
        ];

        foreach ($fontPaths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    private function createWatermarkedUploadedFile($image, UploadedFile $originalFile, int $imageType): ?UploadedFile
    {
        $extensionByType = [
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG => 'png',
            IMAGETYPE_GIF => 'gif',
        ];

        $mimeTypeByType = [
            IMAGETYPE_JPEG => 'image/jpeg',
            IMAGETYPE_PNG => 'image/png',
            IMAGETYPE_GIF => 'image/gif',
        ];

        if (!isset($extensionByType[$imageType], $mimeTypeByType[$imageType])) {
            return null;
        }

        $tempFilePath = tempnam(sys_get_temp_dir(), 'watermark_');
        if ($tempFilePath === false) {
            return null;
        }

        $targetPath = $tempFilePath . '.' . $extensionByType[$imageType];
        if (!@rename($tempFilePath, $targetPath)) {
            @unlink($tempFilePath);
            return null;
        }

        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $saveResult = @imagejpeg($image, $targetPath, 90);
                break;
            case IMAGETYPE_PNG:
                $saveResult = @imagepng($image, $targetPath, 6);
                break;
            case IMAGETYPE_GIF:
                $saveResult = @imagegif($image, $targetPath);
                break;
            default:
                $saveResult = false;
        }

        if (!$saveResult || !is_file($targetPath)) {
            @unlink($targetPath);
            return null;
        }

        $baseName = pathinfo($originalFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeBaseName = $baseName !== '' ? $baseName : 'photo';
        $clientFileName = $safeBaseName . '.' . $extensionByType[$imageType];

        return new UploadedFile(
            $targetPath,
            $clientFileName,
            $mimeTypeByType[$imageType],
            null,
            true
        );
    }
}
