<?php

namespace App\Controller;

use App\Entity\Faq;
use App\Entity\User;
use App\Form\UserType;
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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ClientController extends AbstractController
{
    private $passwordEncoder;
    private $vehiculeRepo;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, VehiculeRepository $vehiculeRepository)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->vehiculeRepo = $vehiculeRepository;
    }


    /**
     * @Route("/client", name="client")
     */
    public function client(Request $request): Response
    {
        $client = $this->getUser();
        $date = new \DateTime('now');
        $message_reservation = '';
        $reservation = new Reservation();
        // $client1 = new User();
        $mode_reservation = $this->getDoctrine()->getRepository(ModeReservation::class)->findOneBy(['id' => 3]);
        $etat_reservation = $this->getDoctrine()->getRepository(EtatReservation::class)->findOneBy(['id' => 1]);
        $form = $this->createForm(ReservationclientType::class, $reservation);
        $formClient = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $reservation->setDateReservation($date);
            $reservation->setCodeReservation("123");
            $reservation->setClient($client);
            $reservation->setUtilisateur($client);
            $reservation->setModeReservation($mode_reservation);
            $reservation->setEtatReservation($etat_reservation);
            $entityManager->persist($reservation);
            $entityManager->flush();
            $message_reservation = 'reservation enregister avec sussès';
            return $this->redirectToRoute('carte');
        }

        //récupération des réservations effectuée
        $reservationEffectuers = $this->getDoctrine()->getRepository(Reservation::class)->findReservationEffectuers($client, $date);

        //récupération des réservations en cours
        $reservationEncours = $this->getDoctrine()->getRepository(Reservation::class)->findReservationEncours($client, $date);

        //récupération des réservation en attente
        $reservationEnAttentes = $this->getDoctrine()->getRepository(Reservation::class)->findReservationEnAttente($client, $date);

        // page client.html auparavant
        return $this->render('client/client.html.twig', [
            'controller_name' => 'AccueilController',
            'client' => $client->getUsername(),
            'id' => $client->getId(),
            'reservation_effectuers' => $reservationEffectuers,
            'reservation_en_cours' => $reservationEncours,
            'reservation_en_attentes' => $reservationEnAttentes,
            'message' => $message_reservation,
            'form' => $form->createView(),
            'formClient' => $formClient->createView(),
        ]);
    }

    /**
     * @Route("client/modifier/{id}", name="client_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(ClientType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                $user->getPassword()
            ));
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('client');
        }

        return $this->render('client/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/inscription", name="inscription")
     */
    public function inscription(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(ClientType::class, $user);
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

            return $this->redirectToRoute('app_login');
        }
        return $this->render('accueil/inscription.html.twig', [
            'controller_name' => 'InscriptionController',
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/login_client", name="login")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        $user = new User();
        $form = $this->createForm(LoginType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //$entityManager = $this->getDoctrine()->getManager();
            //$entityManager->persist($user);
            //$entityManager->flush();
            $userreq = new User();
            $username = $user->getUsername();
            $userpass = $this->passwordEncoder->encodePassword($user, $user->getPassword());
            $userreq = $this->getDoctrine()->getRepository(User::class)->findOneBy(['username' => $username, 'password' => $userpass]);
            if ($userreq != null) {
                //if($user->setPassword($this->passwordEncoder->encodePassword($user,$user->getPassword())) == $userreq->getPassword()){ 
                return $this->redirectToRoute('accueil');
                //}
            }
        }
        return $this->render('accueil/login.html.twig', [
            'controller_name' => 'LoginController',
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/payement", name="payement", methods={"POST"})
     */
    public function payement(Request $request)
    {
        $client = $this->getUser();
        $reservation = $this->getDoctrine()->getRepository(Reservation::class)->findOneBy(["client" => $client], ["id" => "DESC"]);
        $modePaiement = $this->getDoctrine()->getRepository(ModePaiement::class)->findOneBy(["id" => 1]);
        $vehicule = new Vehicule();
        $vehicule = $reservation->getVehicule();
        $caution = $vehicule->getCaution() * 100;
        // Set your secret key. Remember to switch to your live secret key in production.
        // See your keys here: https://dashboard.stripe.com/account/apikeys
        \Stripe\Stripe::setApiKey('sk_test_51INCSpLWsPgEVX5UZKrH0YIs7H7PF8Boao1VcYHEks40it5a39h5KJzcwWxSWUIV6ODWkPS7txKsRyKeSfBknDFC00PAHEBwVP');

        // Token is created using Stripe Checkout or Elements!
        // Get the payment token ID submitted by the form:
        //$token = $_POST['stripeToken'];
        $token = $request->request->get('stripeToken');
        $charge = \Stripe\Charge::create([
            'amount' => $caution,
            'currency' => 'eur',
            'source' => $token,
            'description' => 'caution pour le véhicule ' . $vehicule->getMarque() . ' ' . $vehicule->getModele(),
        ]);

        $paiement = new Paiement();
        $paiement->setReservation($reservation);
        $paiement->setModePaiement($modePaiement);
        $paiement->setUtilisateur($client);
        $paiement->setClient($client);
        $paiement->setMontant($vehicule->getCaution());
        $paiement->setDatePaiement(new \DateTime('now'));
        $paiement->setMotif('caution pour le véhicule ' . $vehicule->getMarque() . ' ' . $vehicule->getModele());
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($paiement);
        $entityManager->flush();
        return $this->redirectToRoute('client');
    }
}
