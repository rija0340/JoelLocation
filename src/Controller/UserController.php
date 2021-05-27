<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\UserClientType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $clients = array();
        $users = $userRepository->findBy([], ["date_inscription" => "DESC"]);
        $pagination = $paginator->paginate(
            $userRepository->findBy([], ["date_inscription" => "ASC"]), /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            20/*limit per page*/
        );
        foreach ($users  as $user) {

            if (in_array('ROLE_CLIENT', $user->getRoles())) {
                array_push($clients, $user);
            }
        }

        return $this->render('admin/user/index.html.twig', [
            'clients' => $clients,
        ]);
    }

    /**
     * @Route("/comptes_utilisateurs", name="comptes_utilisateurs", methods={"GET"})
     */
    public function comptes_utilisateurs(UserRepository $userRepository, Request $request): Response
    {
        $personnels = array();
        $users = $userRepository->findAll();
        foreach ($users as $user) {

            if (in_array('ROLE_PERSONNEL', $user->getRoles()) || in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
                array_push($personnels, $user);
            }
        }

        return $this->render('admin/agence/comptes_utilisateurs/index.html.twig', [
            'personnels' => $personnels
        ]);
    }

    /**
     * @Route("/new", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRoles(['ROLE_PERSONNEL']);
            $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                $user->getPassword()
            ));
            $user->setPresence(1);
            $user->setDateInscription(new \DateTime('now'));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('comptes_utilisateurs');
        }

        return $this->render('admin/user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/clientnew", name="user_client_new", methods={"GET","POST"})
     */
    public function new_client(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserClientType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRoles(['ROLE_CLIENT']);
            $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                $user->getPassword()
            ));
            $user->setPresence(1);
            $user->setDateInscription(new \DateTime('now'));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('admin/user/newclient.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render('admin/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/modifier/{id}", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('comptes_utilisateurs');
        }
        /* if($user->roles[1] == ['ROLE_PERSONNEL']){
            $user->roles = "Employé";
        }
        elseif($user->roles[1] == ['ROLE_ADMIN']){
            $user->roles = "Administrateur";
        }
        elseif($user->roles[1] == ['ROLE_SUPER_ADMIN']){
            $user->roles = "Administrateur";
        } 
        else {
            $user->roles = "Client";
        } */
        //$user->setRoles = "Client";

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }
}
