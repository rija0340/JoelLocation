<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Reservation;
use App\Entity\ModeReservation;
use App\Entity\EtatReservation;
use App\Entity\Vehicule;
use App\Entity\ModePaiement;
use App\Entity\Paiement;
use App\Entity\Faq;
use App\Form\ClientType;
use App\Form\LoginType;
use App\Form\ReservationclientType;
use App\Repository\UserRepository;
use App\Repository\ReservationRepository;
use App\Repository\ModeReservationRepository;
use App\Repository\EtatReservationRepository;
use App\Repository\VehiculeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AccueilController extends AbstractController
{
    private $passwordEncoder;
    private $vehiculeRepo;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, VehiculeRepository $vehiculeRepository)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->vehiculeRepo = $vehiculeRepository;
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
     * @Route("/carte", name="carte")
     */
    public function carte()
    {
        return $this->render('accueil/paiement.html.twig', [
            'controller_name' => 'carte bancaire'
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
    public function formcontact(): Response
    {
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
            return $this->redirectToRoute('client');
        }
        if (in_array("ROLE_PERSONNEL", $user->getRoles())) {
            return $this->redirectToRoute('admin_index');
        }
        if (in_array("ROLE_ADMIN", $user->getRoles())) {
            return $this->redirectToRoute('admin_index');
        }
        if (in_array("ROLE_SUPER_ADMIN", $user->getRoles())) {
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
