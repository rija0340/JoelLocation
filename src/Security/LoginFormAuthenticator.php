<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator implements PasswordAuthenticatedInterface
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private $entityManager;
    private $urlGenerator;
    private $csrfTokenManager;
    private $passwordEncoder;
    private $request;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function supports(Request $request)
    {
        return self::LOGIN_ROUTE === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        // Stocker la requête pour l'utiliser dans d'autres méthodes
        $this->request = $request;

        $credentials = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        );

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['mail' => $credentials['email']]);

        if (!$user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Votre email ou mot de passe est incorrecte.');
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // Vérifier d'abord si le mot de passe est valide
        $passwordValid = $this->passwordEncoder->isPasswordValid($user, $credentials['password']);

        // Si le mot de passe est valide, vérifier aussi si le compte est activé
        if ($passwordValid && $user instanceof User && !$user->getPresence()) {
            // Le mot de passe est correct mais le compte n'est pas activé
            $session = $this->request->getSession();
            $session->set('account_not_activated', true);

            $session->set('client_email', $user->getMail());
            throw new CustomUserMessageAuthenticationException('Votre compte n\'est pas encore activé.');
        }

        return $passwordValid;
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function getPassword($credentials): ?string
    {
        return $credentials['password'];
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        // For example : return new RedirectResponse($this->urlGenerator->generate('some_route'));
        //throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
        //return new RedirectResponse($this->urlGenerator->generate('accueil'));
        //la redirection se trouve dans accueilcontroller
        return new RedirectResponse($this->urlGenerator->generate('redirection'));
    }

    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    public function onAuthenticationFailure(Request $request, \Symfony\Component\Security\Core\Exception\AuthenticationException $exception)
    {
        if ($request->hasSession()) {
            $session = $request->getSession();

            // Check if it's a credentials failure and replace with user-friendly message
            if (
                $exception instanceof \Symfony\Component\Security\Core\Exception\BadCredentialsException ||
                strpos($exception->getMessage(), 'checkCredentials()') !== false
            ) {
                $customException = new \Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException(
                    'Identifiants incorrects. Veuillez vérifier votre email ou mot de passe.'
                );
                $session->set(Security::AUTHENTICATION_ERROR, $customException);
            } else {
                // For other types of authentication errors, keep the original
                $session->set(Security::AUTHENTICATION_ERROR, $exception);
            }
        }

        // Rediriger vers la page de login
        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }
}
