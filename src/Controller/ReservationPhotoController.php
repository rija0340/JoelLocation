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
        $uploadedFiles = $request->files->get('photos');
        $type = $request->request->get('type', 'depart');

        if (!$uploadedFiles) {
            return new JsonResponse(['error' => 'Aucun fichier reçu'], 400);
        }

        $uploadedPhotos = [];

        foreach ($uploadedFiles as $uploadedFile) {
            /** @var UploadedFile $uploadedFile */
            if ($uploadedFile->isValid()) {
                $photo = new ReservationPhoto();
                $photo->setReservation($reservation);
                $photo->setImageFile($uploadedFile);
                $photo->setType($type);

                $this->entityManager->persist($photo);
                $uploadedPhotos[] = [
                    'id' => null, // Will be set after flush
                    'name' => $uploadedFile->getClientOriginalName()
                ];
            }
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
                'url' => $photo->getImageUrl()
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
                'type' => $photo->getType()
            ];
        }

        return new JsonResponse(['photos' => $photos]);
    }
}