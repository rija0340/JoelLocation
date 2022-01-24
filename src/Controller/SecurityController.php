<?php

namespace App\Controller;

use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private $flashy;
    private $session;

    public function __construct(SessionInterface $session, FlashyNotifier $flashy)
    {
        $this->flashy = $flashy;
        $this->session = $session;
    }

    /**
     * @Route("/connexion", name="app_login")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils, RouterInterface $router): Response
    {

        // get the referer, it can be empty!
        $referer = $request->headers->get('referer');
        if (!\is_string($referer) || !$referer) {
        }

        if ($referer != null) {
            $refererPathInfo = Request::create($referer)->getPathInfo();

            // try to match the path with the application routing
            $routeInfos = $router->match($refererPathInfo);

            // get the Symfony route name if it exists
            $refererRoute = $routeInfos['_route'] ?? '';
            switch ($refererRoute) {
                case "inscription":
                    $this->flashy->success('Compte créé avec succés, Veuillez vous connecter');
                    break;
            }
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/deconnexion", name="app_logout")
     */
    public function logout(Request $request)
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
