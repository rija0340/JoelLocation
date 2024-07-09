<?php

namespace App\Service;

use Twig\Environment;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;


// form tuto fix tls error

use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Bridge\Google\Transport\GmailSmtpTransport;

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
            ->text('Sending emails is fun again!');
        // ->html(
        //     //specifier chemin template dans l'appel de cette fonction
        //     $this->twig->render($template, $parameters)
        // );
        $this->mailer->send($email);
    }

    // test another method for fixing tls error
    public function sendMail()
    {
        // For this transport, use the following command line
        // docker run -p 1080:80 -p 1025:25 djfarrelly/maildev
        $transport = new EsmtpTransport('localhost', 1025);

        // $transport = new GmailSmtpTransport('joel@joellocation.com', 'FIXME');

        $mailer = new Mailer($transport);

        $email = (new Email())
            ->from('joel@joellocation.com')
            ->to('rakotorinelinarija@gmail.com')
            ->subject('test - ' . microtime(true))
            ->text('test - ' . microtime(true));

        $mailer->send($email);
    }
}
