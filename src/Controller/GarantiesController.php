<?php

namespace App\Controller;

use App\Entity\Garantie;
use App\Form\GarantieType;
use App\Repository\GarantieRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GarantiesController extends AbstractController
{
    private $garantiesRepo;


    public function __construct(GarantieRepository $garantiesRepo)
    {

        $this->garantiesRepo = $garantiesRepo;
    }

    /**
     * @Route("/backoffice/garanties", name="garanties_index")
     */
    public function index(): Response
    {
        $garanties = $this->garantiesRepo->findAll();

        return $this->render('admin/garanties/index.html.twig', [
            'garanties' => $garanties
        ]);
    }

    /**
     * @Route("/backoffice/garanties/new", name="garanties_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $garantie = new Garantie();
        $form = $this->createForm(GarantieType::class, $garantie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($garantie);
            $entityManager->flush();

            return $this->redirectToRoute('garanties_index');
        }

        return $this->render('admin/garanties/new.html.twig', [
            'garantie' => $garantie,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/backoffice/garanties/{id}", name="garanties_show", methods={"GET"})
     */
    public function show(Garantie $garantie): Response
    {
        return $this->render('admin/garanties/show.html.twig', [
            'garantie' => $garantie,
        ]);
    }

    /**
     * @Route("/backoffice/garanties/{id}/edit", name="garanties_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Garantie $garantie): Response
    {
        $form = $this->createForm(GarantieType::class, $garantie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('garanties_index');
        }

        return $this->render('admin/garanties/edit.html.twig', [
            'garantie' => $garantie,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/backoffice/garanties/{id}", name="garanties_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Garantie $garantie): Response
    {
        if ($this->isCsrfTokenValid('delete' . $garantie->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($garantie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('garanties_index');
    }


    /**
     * @Route("/listeGaranties", name="listeGaranties", methods={"GET"})
     */
    public function listeGaranties(Request $request)
    {
        $data = array();
        $garanties = $this->garantiesRepo->findAll();

        foreach ($garanties as $key => $garantie) {

            $data[$key]['id'] = $garantie->getId();
            $data[$key]['appelation'] = $garantie->getAppelation();
            $data[$key]['prix'] = $garantie->getPrix();
        }

        return new JsonResponse($data);
    }
}
