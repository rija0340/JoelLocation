<?php

namespace App\Service;

use Twig\Environment;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SymfonyMailer
{

    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @Environment
     */
    private $twig;

    /**
     * MailerService constructor
     */
    public function __construct(MailerInterface $mailer, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    /**
     * @param string $subject
     * @param string $from
     * @param string $to
     * @param string $template
     * @param array $parameters
     * @throws |Symfony|Component|Mailer|Exception|TransportExceptionInterface
     * @throws |Twig|Error|LoadError
     * @throws |Twig|Error|RuntimeError
     * @throws |Twig|Error|SyntaxError
     */
    public function send(string $subjet, string $from, string $to, string $template, array $parameters): void
    {
        $email = (new Email())
            ->from($from)
            ->to($to)
            ->subject($subjet)
            ->html(
                '<h1>test</h1>'
                // $this->twig->render($template, $parameters),
                // 'text/html'
            );
        $this->mailer->send($email);
    }
}
