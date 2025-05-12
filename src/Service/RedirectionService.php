<?php

namespace App\Service;

use App\Entity\User;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

class RedirectionService
{
    private $urlGenerator;
    private $security;
    private $flashy;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        Security $security,
        FlashyNotifier $flashy
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->security = $security;
        $this->flashy = $flashy;
    }

    public function redirectAfterLogin(): RedirectResponse
    {
        $user = $this->security->getUser();

        if (!$user) {
            return new RedirectResponse($this->urlGenerator->generate('app_login'));
        }

        $this->flashy->success("Vous êtes bien connecté");

        if (in_array("ROLE_CLIENT", $user->getRoles())) {
            return new RedirectResponse($this->urlGenerator->generate('espaceClient_index'));
        }

        if (
            in_array("ROLE_PERSONNEL", $user->getRoles()) ||
            in_array("ROLE_ADMIN", $user->getRoles()) ||
            in_array("ROLE_SUPER_ADMIN", $user->getRoles())
        ) {
            return new RedirectResponse($this->urlGenerator->generate('admin_index'));
        }

        return new RedirectResponse($this->urlGenerator->generate('accueil'));
    }
}
