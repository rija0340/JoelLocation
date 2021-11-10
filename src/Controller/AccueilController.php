<?php

namespace App\Controller;

use App\Classe\Mailjet;
use App\Entity\Faq;
use App\Entity\Mail;
use App\Entity\User;
use App\Form\LoginType;
use App\Entity\Paiement;
use App\Entity\Vehicule;
use App\Form\ClientType;
use App\Entity\Reservation;
use App\Entity\ModePaiement;
use App\Entity\EtatReservation;
use App\Entity\ModeReservation;
use App\Repository\UserRepository;
use App\Form\ReservationclientType;
use App\Repository\VehiculeRepository;
use App\Repository\ReservationRepository;
use App\Repository\EtatReservationRepository;
use App\Repository\ModeReservationRepository;
use App\Service\DateHelper;
use App\Service\SymfonyMailer;
use Doctrine\ORM\EntityManagerInterface;
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
        $vehicules = $this->getDoctrine()->getRepository(Vehicule::class)->findBy([], ["id" => "DESC"]);
        return $this->render('accueil/nosvehicule.html.twig', [
            'controller_name' => 'AccueilController',
            'vehicules' => $vehicules,
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
        if ($request->request->get('email') != null) {

            $nom = $request->request->get('nom');
            $email_user = $request->request->get('email');
            $telephone = $request->request->get('telephone');
            $adresse = $request->request->get('adresse');
            $objet = $request->request->get('objet');
            $message = "Adresse email du client :" . $email_user . "Message : " . $request->request->get('message');

            //to, client_nom, objet, message du client
            $this->mailjet->send("rakotoarinelinarija@gmail.com", $nom, $objet, $message);

            $this->flashy->success("Votre mail a bien été envoyé");
            return $this->redirectToRoute('accueil');
        }
        return $this->render('accueil/contact.html.twig');
    }


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

    /**
     * @Route("/teste", name="teste")
     */
    public function teste(): Response
    {
        return $this->render('accueil/teste.html.twig', [
            'controller_name' => 'TesteController',
        ]);
    }
}
