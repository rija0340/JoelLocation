<?php

namespace App\Controller;

use App\Entity\Faq;
use App\Entity\Mail;
use App\Entity\User;
use App\Classe\Mailjet;
use App\Form\LoginType;
use App\Entity\Paiement;
use App\Entity\Vehicule;
use App\Form\ClientType;
use App\Entity\Reservation;
use App\Service\DateHelper;
use App\Entity\ModePaiement;
use App\Service\SymfonyMailer;
use App\Entity\EtatReservation;
use App\Entity\ModeReservation;
use App\Repository\UserRepository;
use App\Form\FormulaireContactType;
use App\Form\ReservationclientType;
use App\Repository\VehiculeRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use App\Repository\EtatReservationRepository;
use App\Repository\ModeReservationRepository;
use Doctrine\ORM\Mapping\Id;
use Symfony\Component\HttpFoundation\Request;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AccueilController extends AbstractController
{
    private $passwordEncoder;
    private $vehiculeRepo;
    private $flashy;
    private $em;
    private $dateHelper;
    private $mailjet;

    public function __construct(Mailjet $mailjet, DateHelper $dateHelper, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder, VehiculeRepository $vehiculeRepository, FlashyNotifier $flashy)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->vehiculeRepo = $vehiculeRepository;
        $this->flashy = $flashy;
        $this->em = $em;
        $this->dateHelper = $dateHelper;
        $this->mailjet = $mailjet;
    }

    /**
     * @Route("/", name="accueil")
     */
    public function index(): Response
    {
        $troisvehicules = [];
        $vehicules = $this->vehiculeRepo->findAll();
        $i = 0;
        foreach ($vehicules as $vehicule) {
            if ($i < 3) {
                // exit;
                $troisvehicules[$i] = $vehicule;
                $i++;
            }
        }

        // dd($vehicules[0]);
        return $this->render('accueil/index.html.twig', [
            'vehicules' => $troisvehicules
        ]);
    }


    /**
     * @Route("/quisommenous", name="quisommenous")
     */
    public function quiSommeNous(): Response
    {
        return $this->render('accueil/quisommenous.html.twig', [
            'controller_name' => 'QuiSommeNousController',
        ]);
    }


    /**
     * @Route("/capture", name="capture")
     */
    public function captur(): Response
    {
        return $this->render('accueil/capture.html.twig', [
            'controller_name' => 'CaptureController',
        ]);
    }


    /**
     * @Route("/clio", name="clio")
     */
    public function clio(): Response
    {
        return $this->render('accueil/clio.html.twig', [
            'controller_name' => 'ClioController',
        ]);
    }


    /**
     * @Route("/twingo", name="twingo")
     */
    public function twingo(): Response
    {
        return $this->render('accueil/twingo.html.twig', [
            'controller_name' => 'TwingoController',
        ]);
    }


    /**
     * @Route("/mentionlegale", name="mentionlegale")
     */
    public function mentionLegale(): Response
    {
        return $this->render('accueil/mentionlegale.html.twig', [
            'controller_name' => 'MentionLegaleController',
        ]);
    }



    /**
     * @Route("/nosvehicules", name="nosvehicules")
     */
    public function noVehicules(): Response
    {
        $vehicules = $this->vehiculeRepo->findAll();
        $i = 0;
        foreach ($vehicules as $vehicule) {
            if ($i < 3) {
                // exit;
                $troisvehicules[$i] = $vehicule;
                $i++;
            }
        }
        return $this->render('accueil/nosvehicule.html.twig', [
            'controller_name' => 'AccueilController',
            'vehicules' => $troisvehicules,
        ]);
    }


    /**
     * @Route("/notrevehicule{id}", name="notrevehicule")
     */
    public function notreVehicule(int $id): Response
    {
        $vehicule = $this->getDoctrine()->getRepository(Vehicule::class)->findOneBy(['id' => $id]);
        return $this->render('accueil/notrevehicule.html.twig', [
            'controller_name' => 'AccueilController',
            'vehicule' => $vehicule,
        ]);
    }


    /**
     * @Route("/carte", name="carte", methods={"GET","POST"})
     */
    public function carte(Request $request)
    {
        $id = $request->request->get("id");
        return $this->render('accueil/paiement.html.twig', [
            'controller_name' => 'carte bancaire',
            'id' => $id,
        ]);
    }


    /**
     * @Route("/foireauxquestion", name="foireauxquestion")
     */
    public function foireauxquestion(): Response
    {
        $faqs = $this->getDoctrine()->getRepository(Faq::class)->findAll();
        return $this->render('accueil/faq.html.twig', [
            'controller_name' => 'FoireAuxQuestions',
            'faqs' => $faqs,
        ]);
    }


    /**
     * @Route("/cgu", name="cgu")
     */
    public function cgu(): Response
    {
        return $this->render('accueil/cgu.html.twig');
    }


    /**
     * @Route("/formulaire-contact", name="formulaire-contact")
     */
    public function formcontact(Request $request, SymfonyMailer $mailer): Response
    {
        $form = $this->createForm(FormulaireContactType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $nom = $form->getData()['nom'];
            $email = $form->getData()['email'];
            $telephone = $form->getData()['telephone'];
            $adresse = $form->getData()['adresse'];
            $objet = $form->getData()['objet'];
            $message = $form->getData()['message'];

            //tester si presence de lien hypertexte dans le message envoyé par le client et blocké s'il y en a
            //tester aussi longueur message et envoyer si < 255
            //tester aussi adresse mail no-reply, be pas envoyer si présence 
            if (stristr($message, 'http://') || stristr($message, 'https://')) {
                $status = false;
                $this->flashy->error("Veuillez contacter le service client par téléphone ou WhatsApp si vous avez besoin d'envoyer plus d'éléments dans ce message");
            } else {
                //si inférieur envoyer le message
                if (strlen($message) < 255) {

                    if (substr($email, 0, 7) == "noreply" || substr($email, 0, 7) == "no-repl") {
                        $status = false;
                        $this->flashy->success("Votre email a bien été envoyé");
                    } else {
                        // envoyer le mail
                        $status = $this->mailjet->sendToMe($nom, $email, $telephone, $adresse, $objet, $message);
                    }
                } else {
                    $status = false;
                    $this->flashy->error("Ne pas dépasser 250 caractères pour le message");
                }
            }

            // if ($status) {
            //     $this->flashy->success("Votre email a bien été envoyé");
            // } else {
            //     $this->flashy->error("Votre email n'a pas été envoyé");
            // }
            return $this->redirectToRoute('accueil');
        }
        return $this->render('accueil/contact.html.twig', [
            'form' => $form->createView()
        ]);
    }

    //cette fonction est appelée dans securitycontroller
    /**
     * @Route("/redirection", name="redirection")
     */
    public function redirection()
    {

        $user = new User();
        $user = $this->getUser();
        if (in_array("ROLE_CLIENT", $user->getRoles())) {
            $this->flashy->success("Vous êtes bien connecté");
            return $this->redirectToRoute('espaceClient_index');
        }
        if (in_array("ROLE_PERSONNEL", $user->getRoles())) {
            $this->flashy->success("Vous êtes bien connecté");

            return $this->redirectToRoute('admin_index');
        }
        if (in_array("ROLE_ADMIN", $user->getRoles())) {
            $this->flashy->success("Vous êtes bien connecté");

            return $this->redirectToRoute('admin_index');
        }
        if (in_array("ROLE_SUPER_ADMIN", $user->getRoles())) {
            $this->flashy->success("Vous êtes bien connecté");

            return $this->redirectToRoute('admin_index');
        }
        return $this->redirectToRoute('app_logout');
        //return $this->render('accueil/contact.html.twig');
    }
}
