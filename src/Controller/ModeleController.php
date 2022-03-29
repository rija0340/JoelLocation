<?php

namespace App\Controller;

use App\Entity\Modele;
use App\Form\ModeleType;
use App\Repository\MarqueRepository;
use App\Repository\ModeleRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("backoffice/modele")
 */
class ModeleController extends AbstractController
{

    private $modeleRepo;
    private $marqueRepo;



    public function __construct(ModeleRepository $modeleRepo, MarqueRepository $marqueRepo)
    {

        $this->modeleRepo = $modeleRepo;
        $this->marqueRepo = $marqueRepo;
    }


    /**
     * @Route("/liste", name="modeles_marque", methods={"GET"} , requirements={"id":"\d+"})
     */

    public function modelesMarque(Request $request)
    {

        $marqueID = intval($request->query->get('marqueID'));

        $marque = $this->marqueRepo->find($marqueID);

        $modeles = $this->modeleRepo->findBy(['marque' => $marque]);



        $data = array();
        foreach ($modeles as $key => $modele) {

            $data[$key]['id'] = $modele->getId();
            $data[$key]['text'] = $modele->getLibelle();
        }
        return new JsonResponse($data);
    }

    /**
     * @Route("/", name="modele_index", methods={"GET"})
     */
    public function index(ModeleRepository $modeleRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $pagination = $paginator->paginate(
            $modeleRepository->findBy([], ["id" => "DESC"]), /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );
        return $this->render('admin/modele/index.html.twig', [
            'modeles' => $pagination,
        ]);
    }

    /**
     * @Route("/new", name="modele_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $modele = new Modele();
        $form = $this->createForm(ModeleType::class, $modele);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($modele);
            $entityManager->flush();

            return $this->redirectToRoute('modele_index');
        }

        return $this->render('admin/modele/new.html.twig', [
            'modele' => $modele,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="modele_show", methods={"GET"})
     */
    public function show(Modele $modele): Response
    {
        return $this->render('admin/modele/show.html.twig', [
            'modele' => $modele,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="modele_edit", methods={"GET","POST"} , requirements={"id":"\d+"})
     */
    public function edit(Request $request, Modele $modele): Response
    {
        $form = $this->createForm(ModeleType::class, $modele);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('modele_index');
        }

        return $this->render('admin/modele/edit.html.twig', [
            'modele' => $modele,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="modele_delete", methods={"DELETE"},  requirements={"id":"\d+"})
     */
    public function delete(Request $request, Modele $modele): Response
    {
        if ($this->isCsrfTokenValid('delete' . $modele->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($modele);
            $entityManager->flush();
        }

        return $this->redirectToRoute('modele_index');
    }
}
