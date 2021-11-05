<?php

namespace App\Controller;

use App\Repository\MailRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EmailController extends AbstractController
{

    private $emailRepo;
    public function __construct(MailRepository $emailRepo)
    {
        $this->emailRepo = $emailRepo;
    }

    /**
     * @Route("/backoffice/emails", name="email_index")
     */
    public function index(): Response
    {

        $emails = $this->emailRepo->findAll();
        return $this->render('admin/email/index.html.twig', [
            'emails' => $emails
        ]);
    }



    /**
     * @Route("/email/nouveau", name="email_new", methods={"GET","POST"})
     */
    public function new(EmailType $email, Request $request): Response
    {


        if ($request->request->get('email') != null) {

            $nom = $request->request->get('nom');
            $email = $request->request->get('email');
            $telephone = $request->request->get('telephone');
            $adresse = $request->request->get('adresse');
            $objet = $request->request->get('objet');
            $message = $request->request->get('message');


            $this->flashy->success("Votre email a bien été envoyé");
            return $this->redirectToRoute('accueil');
        }
    }
}
