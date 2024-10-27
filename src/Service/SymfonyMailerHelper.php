<?php

namespace App\Service;

use App\Entity\Mail;
use App\Entity\Devis;
use App\Entity\Reservation;
use App\Service\Site;
use App\Service\SymfonyMailer;
use Psr\Log\LoggerInterface;
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

    public function __construct(
        SymfonyMailer $symfonyMailer,
        UrlGeneratorInterface $router,
        Site $site,
        EntityManagerInterface $em,
        LoggerInterface $emailLogger,
        FlashyNotifier $flashy
    ) {
        $this->symfonyMailer = $symfonyMailer;
        $this->router = $router;
        $this->site = $site;
        $this->em = $em;
        $this->emailLogger = $emailLogger;
        $this->flashy = $flashy;
    }

    public function sendDevis(Request $request, Devis $devis)
    {
        $this->sendDocument($request, $devis, 'devis');
    }
    public function sendContrat(Request $request, Reservation $resa)
    {
        $this->sendDocument($request, $resa, 'contrat');
    }
    private function sendDocument($request, $entity, $type)
    {

        $baseUrl = $this->site->getBaseUrl($request);
        $route = ($type === 'devis') ? 'devis_pdf' : 'contrat_pdf';
        $documentLink = $baseUrl . $this->router->generate($route, ['id' => $entity->getId()]);

        $name = $entity->getClient()->getNom();
        $email = $entity->getClient()->getMail();
        $reference = ($type === 'devis') ? $entity->getNumero() : $entity->getReference();

        try {
            $method = ($type === 'devis') ? 'sendDevis' : 'sendContrat';
            $this->symfonyMailer->$method($email, $name, "Lien de $type", $documentLink);

            $this->saveMail($entity, "success");
            $this->flashy->success("L'url de téléchargement du $type N°$reference a été envoyé");
        } catch (\Exception $e) {
            $this->flashy->error("L'url de téléchargement du $type N°$reference n'a pas été envoyé");
            $this->saveMail($entity, "failed");
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

    public function saveMail($entity, $status)
    {
        $email = new Mail();
        $ref  = ($entity instanceof Reservation) ? $entity->getReference() : $entity->getNumero();
        $type = ($entity instanceof Reservation) ? ' la reservation' : 'du devis';
        $email->setPrenom($entity->getClient()->getPrenom());
        $email->setNom($entity->getClient()->getNom());
        $email->setMail($entity->getClient()->getMail());
        $email->setObjet('Lien' . $type . " " . $ref);
        $email->setContenu($status);
        //set date of today 
        $email->setDateReception(new \DateTime('now'));
        $this->em->persist($email);
        $this->em->flush();
    }
}
