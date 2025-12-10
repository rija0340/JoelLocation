<?php

namespace App\Controller\Testing;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Home controller for all testing interfaces
 * @Route("/testing")
 * @IsGranted("ROLE_SUPER_ADMIN")
 */
class TestingHomeController extends AbstractController
{
    /**
     * @Route("/", name="testing_home")
     */
    public function index(): Response
    {
        $testingModules = [
            'email' => [
                'id' => 'email',
                'name' => 'Tests Email',
                'description' => 'Testez l\'envoi de tous les types d\'emails (devis, contrat, facture, validation, etc.)',
                'icon' => 'fa-envelope',
                'color' => 'bg-info',
                'route' => 'email_testing_index',
                'features' => [
                    'Envoi de devis',
                    'Envoi de contrat',
                    'Envoi de facture et avoir',
                    'Validation d\'inscription',
                    'Confirmation de paiement',
                    'Formulaire de contact'
                ]
            ],
            'pdf' => [
                'id' => 'pdf',
                'name' => 'Tests PDF',
                'description' => 'Prévisualisez et testez la génération de tous les templates PDF.',
                'icon' => 'fa-file-pdf-o',
                'color' => 'bg-danger',
                'route' => 'testing_pdf_index',
                'features' => [
                    'Devis PDF',
                    'Contrat PDF',
                    'Facture PDF',
                    'Avoir PDF',
                    'Facture Devis PDF'
                ]
            ],
            'availability' => [
                'id' => 'availability',
                'name' => 'Tests Disponibilité',
                'description' => 'Testez la logique de recherche des véhicules disponibles.',
                'icon' => 'fa-car',
                'color' => 'bg-success',
                'route' => 'availability_testing_index',
                'features' => [
                    'Vérification par période',
                    'Détail par véhicule',
                    'Réservations bloquantes',
                    'Stop Sales actifs',
                    'API de vérification'
                ]
            ]
        ];

        return $this->render('admin/testing/index.html.twig', [
            'modules' => $testingModules
        ]);
    }
}
