<?php

namespace App\Service;

use DateTime;
use DateTimeZone;
use App\Entity\Mail;
use App\Entity\Devis;
use App\Service\Site;
use App\Entity\Reservation;
use Psr\Log\LoggerInterface;
use App\Service\SymfonyMailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/backoffice/")
 */
class SymfonyMailerHelper
{
    private $symfonyMailer;
    private $router;
    private $site;
    private $em;
    private $emailLogger;
    private $flashy;
    private $dateHelper;

    public function __construct(
        SymfonyMailer $symfonyMailer,
        UrlGeneratorInterface $router,
        Site $site,
        EntityManagerInterface $em,
        LoggerInterface $emailLogger,
        FlashyNotifier $flashy,
        DateHelper $dateHelper
    ) {
        $this->symfonyMailer = $symfonyMailer;
        $this->router = $router;
        $this->site = $site;
        $this->em = $em;
        $this->emailLogger = $emailLogger;
        $this->flashy = $flashy;
        $this->dateHelper = $dateHelper;
    }

    public function sendDevis(Request $request, Devis $devis)
    {
        $this->sendDocument($request, $devis, 'devis');
    }
    public function sendContrat(Request $request, Reservation $resa)
    {
        $this->sendDocument($request, $resa, 'contrat');
    }
    public function sendFacture(Request $request, Reservation $resa)
    {
        $this->sendDocument($request, $resa, 'facture');
    }

    private function sendDocument($request, $entity, $type)
    {
        $baseUrl = $this->site->getBaseUrl($request);
        $route = $type . '_pdf';
        $documentLink = $baseUrl . $this->router->generate($route, ['hashedId' =>  sha1($entity->getId())]);

        $name = $entity->getClient()->getNom();
        $email = $entity->getClient()->getMail();
        //check the entity type 
        $reference = ($entity instanceof Devis) ? $entity->getNumero() : $entity->getReference();

        try {
            $method =  'send' . ucfirst($type);
            $this->symfonyMailer->$method($email, $name, ucfirst($type), $documentLink);

            $this->saveMail($entity, $type, "success");
            $this->flashy->success("L'url de téléchargement du $type N°$reference a été envoyé");
        } catch (\Exception $e) {
            $this->flashy->error("L'url de téléchargement du $type N°$reference n'a pas été envoyé");
            $this->saveMail($entity, $type, "failed");
            $this->emailLogger->error(sprintf(
                'Failed to send %s - Code: %s, Email: %s, Subject: %s, Error: %s',
                $type,
                $reference,
                $email,
                "Lien de $type",
                $e->getMessage()
            ));
        }
    }

    public function sendContact($data)
    {
        //transform the data en json string
        try {
            $this->symfonyMailer->sendContact($data);

            $this->saveMail($data, 'contact', "success");
            $this->flashy->success("Votre message a a été envoyé");
        } catch (\Exception $e) {
            $this->flashy->error("Votre message n'a pas été envoyé");
            $this->saveMail($data, 'contact', "failed");
            $this->emailLogger->error(sprintf(
                'Failed to send %s - Error: %s',
                'Contact',
                $e->getMessage()
            ));
        }
    }

    public function saveMail($entity, $type, $status)
    {
        $json = json_encode($entity);

        $email = new Mail();
        if (!is_array($entity)) {
            $ref  = ($entity instanceof Reservation) ? $entity->getReference() : $entity->getNumero();
            $email->setPrenom($entity->getClient()->getPrenom());
            $email->setNom($entity->getClient()->getNom());
            $email->setMail($entity->getClient()->getMail());
            $email->setObjet($type . " " . $ref);
            $email->setContenu($status);
        } else {
            $email->setPrenom("");
            $email->setNom($entity['nom']);
            $email->setMail($entity['emailClient']);
            $email->setObjet($type);
            $email->setContenu($json);
        }
        //set date of today 
        $date  = $this->dateHelper->dateNow();
        $email->setDateReception($date);
        $this->em->persist($email);
        $this->em->flush();
    }
}
