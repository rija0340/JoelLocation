<?php

namespace App\Service;

use App\Entity\Reservation;
use App\Entity\ReservationPhoto;
use Psr\Log\LoggerInterface;
use ZipArchive;

class PhotoZipService
{
    private $projectDir;
    private $logger;
    private $photoDir;

    public function __construct(string $projectDir, LoggerInterface $logger)
    {
        $this->projectDir = $projectDir;
        $this->logger = $logger;
        $this->photoDir = $projectDir . '/public/uploads/reservation_photos/';
    }

    /**
     * Generates a ZIP file for the specified type of photos in a reservation.
     * 
     * @param Reservation $reservation
     * @param string $type 'depart' or 'remise'
     * @return string|null Path to the generated ZIP file or null if no photos
     */
    public function createZipForReservation(Reservation $reservation, string $type): ?string
    {
        $photos = $reservation->getPhotos()->filter(function (ReservationPhoto $photo) use ($type) {
            return $photo->getType() === $type;
        });

        if ($photos->isEmpty()) {
            return null;
        }

        $zipFileName = sprintf('photos_%s_%s_%s.zip', $type, $reservation->getReference(), uniqid());
        $zipPath = sys_get_temp_dir() . '/' . $zipFileName;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->logger->error("Could not create ZIP file at $zipPath");
            return null;
        }

        $tempFiles = [];
        foreach ($photos as $photo) {
            $originalPath = $this->photoDir . $photo->getImage();
            if (!file_exists($originalPath) || !is_file($originalPath)) {
                continue;
            }

            // Process image: Resize/Compress if needed
            $processedPath = $this->getProcessedImage($originalPath);

            // If it's a new temp file, track it for deletion
            if ($processedPath !== $originalPath) {
                $tempFiles[] = $processedPath;
            }

            $zip->addFile($processedPath, $photo->getImage());
        }

        $zip->close();

        // Cleanup temporary processed images
        foreach ($tempFiles as $tempFile) {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }

        return $zipPath;
    }

    /**
     * Resizes and compresses an image if it's too large.
     * Returns the path to the processed image (temp file or original).
     */
    private function getProcessedImage(string $originalPath): string
    {
        $fileSize = filesize($originalPath);
        $maxSize = 1024 * 1024; // 1MB limit for processed image in ZIP

        // Get image info
        $imageInfo = @getimagesize($originalPath);
        if (!$imageInfo) {
            return $originalPath; // Not an image or unreadable
        }

        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $type = $imageInfo[2];

        // If it's already small enough, just return original
        if ($fileSize < $maxSize && $width <= 1920 && $height <= 1920) {
            return $originalPath;
        }

        // Needs processing
        try {
            switch ($type) {
                case IMAGETYPE_JPEG:
                    $src = imagecreatefromjpeg($originalPath);
                    break;
                case IMAGETYPE_PNG:
                    $src = imagecreatefrompng($originalPath);
                    break;
                case IMAGETYPE_GIF:
                    $src = imagecreatefromgif($originalPath);
                    break;
                default:
                    return $originalPath;
            }

            if (!$src) {
                return $originalPath;
            }

            // Calculate new dimensions
            $maxDim = 1920;
            if ($width > $maxDim || $height > $maxDim) {
                if ($width > $height) {
                    $newWidth = $maxDim;
                    $newHeight = floor($height * ($maxDim / $width));
                } else {
                    $newHeight = $maxDim;
                    $newWidth = floor($width * ($maxDim / $height));
                }
            } else {
                $newWidth = $width;
                $newHeight = $height;
            }

            $dst = imagecreatetruecolor($newWidth, $newHeight);

            // Handle transparency for PNG
            if ($type == IMAGETYPE_PNG) {
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
            }

            imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            $tempPath = sys_get_temp_dir() . '/processed_' . uniqid() . '.jpg';

            // Always save as JPEG with 70% quality (matches JS 0.7)
            imagejpeg($dst, $tempPath, 70);

            imagedestroy($src);
            imagedestroy($dst);

            return $tempPath;
        } catch (\Exception $e) {
            $this->logger->error("Error processing image $originalPath: " . $e->getMessage());
            return $originalPath;
        }
    }
}
