<?php

namespace App\Controller;

use App\Entity\Vehicule;
use App\Form\VehiculeType;
use App\Repository\ReservationRepository;
use App\Repository\VehiculeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Knp\Component\Pager\PaginatorInterface;


class VehiculeController extends AbstractController
{
    private $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    /**
     * @Route("/vehicule", name="vehicule_index", methods={"GET"})
     */
    public function index(VehiculeRepository $vehiculeRepository, Request $request, PaginatorInterface $paginator, ReservationRepository $reservationRepository): Response
    {


        $pagination = $paginator->paginate(
            $vehiculeRepository->findBy([], ["id" => "DESC"]), /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            20/*limit per page*/
        );
        return $this->render('admin/vehicule/index.html.twig', [
            'vehicules' => $pagination,
        ]);
    }

    /**
     * @Route("/vehicule/new", name="vehicule_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {

        $vehicule = new Vehicule();
        $form = $this->createForm(VehiculeType::class, $vehicule);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // $imageFile = $form->get('image')->getData();
            // if ($imageFile) {
            //     $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            //     //$safeFilename = $slugger->slug($originalFilename);
            //     $newFilename = $this->generateUniqueFileName() . '.' . $imageFile->guessExtension();
            //     try {
            //         $imageFile->move(
            //             $this->getParameter('vehicules_directory'),
            //             $newFilename
            //         );
            //     } catch (FileException $e) {
            //     }
            // }
            // $vehicule->setImage($newFilename);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($vehicule);
            $entityManager->flush();

            return $this->redirectToRoute('vehicule_index');
        }

        return $this->render('admin/vehicule/new.html.twig', [
            'vehicule' => $vehicule,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/vehicule/{id}", name="vehicule_show", methods={"GET"})
     */
    public function show(Vehicule $vehicule): Response
    {
        return $this->render('admin/vehicule/show.html.twig', [
            'vehicule' => $vehicule,
        ]);
    }

    /**
     * @Route("/vehicule/{id}/edit", name="vehicule_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Vehicule $vehicule): Response
    {
        $form = $this->createForm(VehiculeType::class, $vehicule);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $imageFile = $form->get('image')->getData();
            // if ($imageFile) {
            //     $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            //     //$safeFilename = $slugger->slug($originalFilename);
            //     $newFilename = $this->generateUniqueFileName() . '.' . $imageFile->guessExtension();
            //     try {
            //         $imageFile->move(
            //             $this->getParameter('vehicules_directory'),
            //             $newFilename
            //         );
            //     } catch (FileException $e) {
            //     }
            // }
            // $vehicule->setImage($newFilename);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('vehicule_index');
        }

        return $this->render('admin/vehicule/edit.html.twig', [
            'vehicule' => $vehicule,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/vehicule/{id}", name="vehicule_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Vehicule $vehicule): Response
    {
        if ($this->isCsrfTokenValid('delete' . $vehicule->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($vehicule);
            $entityManager->flush();
        }
        return $this->redirectToRoute('vehicule_index');
    }
    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }
}
