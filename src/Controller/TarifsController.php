<?php

namespace App\Controller;

use App\Entity\Garantie;
use App\Entity\Options;
use App\Entity\Tarifs;
use App\Entity\TarifsV2;
use App\Form\TarifsType;
use App\Form\TarifEditType;
use App\Form\TarifsV2Type;
use App\Repository\GarantieRepository;
use App\Repository\MarqueRepository;
use App\Repository\ModeleRepository;
use App\Repository\OptionsRepository;
use App\Repository\TarifsRepository;
use App\Repository\TarifsV2Repository;
use App\Repository\VehiculeRepository;
use App\Service\DateHelper;
use App\Service\TarifsHelper;
use App\Service\PricingModeService;
use App\Service\PricingStrategyProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;
/**
 * @Route("/backoffice")
 */
class TarifsController extends AbstractController
{

    private $marqueRepo;
    private $modeleRepo;
    private $tarifsHelper;
    private $dateHelper;
    private $optionsRepo;
    private $garantiesRepo;
    private $tarifsV2Repo;
    private $pricingModeService;
    private $strategyProvider;

    public function __construct(
        DateHelper $dateHelper, 
        TarifsHelper $tarifsHelper, 
        MarqueRepository $marqueRepo, 
        ModeleRepository $modeleRepo, 
        OptionsRepository $optionsRepo, 
        GarantieRepository $garantiesRepo,
        TarifsV2Repository $tarifsV2Repo,
        PricingModeService $pricingModeService,
        PricingStrategyProvider $strategyProvider
    )
    {
        $this->marqueRepo = $marqueRepo;
        $this->modeleRepo = $modeleRepo;
        $this->tarifsHelper = $tarifsHelper;
        $this->dateHelper = $dateHelper;
        $this->optionsRepo = $optionsRepo;
        $this->garantiesRepo = $garantiesRepo;
        $this->tarifsV2Repo = $tarifsV2Repo;
        $this->pricingModeService = $pricingModeService;
        $this->strategyProvider = $strategyProvider;
    }

    /**
     * @Route("/tarifs", name="tarifs_index")
     */
    public function index(TarifsRepository $tarifsRepo): Response
    {

        $listeTarifs = new Tarifs();
        $listeTarifs =  $tarifsRepo->findAll();
        $nbrTarifs = count($listeTarifs);
        $listeVehicule = [];
        $listeUniqueVehicules = [];

        $listeMois = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];


        $marques = $this->marqueRepo->findAll();
        $modeles = $this->modeleRepo->findAll();
        foreach ($marques as $marque) {
            foreach ($modeles as $modele) {

                if ($marque == $modele->getMarque()) {
                    array_push($listeUniqueVehicules, $marque . " " . $modele);
                }
            }
        }


        $tarifsParVehicule = [];
        //arrangement des donnée Janvier -> décembre
        foreach ($listeUniqueVehicules as $veh) {
            $ordered = [];
            foreach ($listeMois as $mois) {
                $i = 0;
                foreach ($listeTarifs as $tarif) {
                    if ($tarif->getMois() == $mois && $tarif->getMarque()->getLibelle() . " " . $tarif->getModele()->getLibelle() == $veh) {
                        $i = $i + 1;
                        array_push($ordered, $tarif);
                    }
                }
                if ($i == 0) {
                    array_push($ordered, null);
                }
            }
            array_push($tarifsParVehicule, $ordered);
        }

        return $this->render('admin/tarifs/index.html.twig', [
            'controller_name' => 'TarifsController',
            'tarifs' => $listeTarifs,
            'listeMois' => $listeMois,
            'listeVehicules' => $listeUniqueVehicules,
            'tarifsParVehicule' => $tarifsParVehicule
        ]);
    }

    /**
     * @Route("/tarif/new", name="tarif_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {

        $tarif = new Tarifs();

        $form = $this->createForm(TarifsType::class, $tarif);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $modeleId =  $request->request->get('selectModele');
            $mois =  $request->request->get('selectMois');
            $modele =  $this->modeleRepo->find($modeleId);
            $tarif->setModele($modele);
            $tarif->setMois($mois);

            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($tarif);
            $entityManager->flush();

            return $this->redirectToRoute('tarifs_index');
        }

        return $this->render('admin/tarifs/new.html.twig', [
            'tarif' => $tarif,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/tarif/newTarif", name="tarif_newTarif", methods={"GET","POST"})
     */
    public function newTarif(Request $request): Response
    {


        $tarif = new Tarifs();
        $marque = $request->query->get('marque');
        $modele = $request->query->get('modele');

        $marque = $this->marqueRepo->findOneBy(['libelle' => $marque]);
        $modele = $this->modeleRepo->findOneBy(['libelle' => $modele]);

        $troisJours = $request->query->get('troisJours');
        $septJours = $request->query->get('septJours');
        $quinzeJours = $request->query->get('quinzeJours');
        $trenteJours = $request->query->get('trenteJours');

        $mois = $request->query->get('mois');


        $tarif->setMarque($marque);
        $tarif->setModele($modele);
        $tarif->setTroisJours(floatval($troisJours));
        $tarif->setSeptJours(floatval($septJours));
        $tarif->setQuinzeJours(floatval($quinzeJours));
        $tarif->setTrenteJours(floatval($trenteJours));
        $tarif->setMois($mois);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($tarif);
        $entityManager->flush();


        return $this->redirectToRoute('tarifs_index');
    }


    /**
     * @Route("/tarif/{id}", name="tarif_show", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function show(Tarifs $tarif): Response
    {
        return $this->render('admin/tarifs/show.html.twig', [
            'tarif' => $tarif,
        ]);
    }

    /**
     * @Route("/tarif/{id}/edit", name="tarif_edit", methods={"GET","POST"},requirements={"id":"\d+"})
     */
    public function edit(Request $request, Tarifs $tarif): Response
    {
        $formEdit = $this->createForm(TarifEditType::class, $tarif);
        $formEdit->handleRequest($request);

        if ($formEdit->isSubmitted() && $formEdit->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('tarifs_index');
        }

        return $this->render('admin/tarifs/edit.html.twig', [
            'tarif' => $tarif,
            'formEdit' => $formEdit->createView(),
        ]);
    }

    /**
     * @Route("/tarif/{id}", name="tarif_delete", methods={"DELETE"}, requirements={"id":"\d+"})
     */
    public function delete(Request $request, Tarifs $tarif): Response
    {
        if ($this->isCsrfTokenValid('delete' . $tarif->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($tarif);
            $entityManager->flush();
        }
        return $this->redirectToRoute('tarifs_index');
    }

    /**
     * @Route("/tarifVenteComptoir", name="tarif_vente_comptoir", methods={"GET"})
     */
    public function tarifVenteComptoir(Request $request, VehiculeRepository $vehiculeRepo, TarifsRepository $tarifsRepo)
    {

        $vehicule_id = intVal($request->query->get('vehicule_id'));
        $dateDepart = $request->query->get('dateDepart');
        $dateRetour = $request->query->get('dateRetour');

        $dateDepart = $this->dateHelper->newDate($dateDepart);
        $dateRetour = $this->dateHelper->newDate($dateRetour);

        $vehicule = $vehiculeRepo->find($vehicule_id);
        $tarif = $this->tarifsHelper->calculTarifVehicule($dateDepart, $dateRetour, $vehicule);

        $data = array();

        if ($tarif != null) {

            $data['tarif'] = $tarif;
        } else {
            $data['tarif'] = 0;
        }

        return new JsonResponse($data);
    }


    /**
     * @Route("/listeMoisSansValeur", name="listeMoisSansValeur", methods={"GET"})
     * @return tableau contenant mois contenant tarifs
     */
    public function listeMoisSansValeur(Request $request, VehiculeRepository $vehiculeRepo, TarifsRepository $tarifsRepo)
    {
        $marqueID = intVal($request->query->get('marqueID'));
        $modeleID = intVal($request->query->get('modeleID'));

        $marque = $this->marqueRepo->find($marqueID);
        $modele = $this->modeleRepo->find($modeleID);

        $tarifs = $tarifsRepo->findBy(['marque' => $marque, 'modele' => $modele]);

        $listeMois = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

        $ordered = [];
        foreach ($listeMois as $mois) {
            $i = 0;
            foreach ($tarifs as $tarif) {
                if ($tarif->getMois() == $mois) {
                    $i = $i + 1;
                    array_push($ordered, $tarif->getMois());
                }
            }
            if ($i == 0) {
                array_push($ordered, "");
            }
        }

        $moisSansVal = [];
        for ($i = 0; $i < 12; $i++) {
            if ($ordered[$i] == "") {
                array_push($moisSansVal, $listeMois[$i]);
            }
        }
        return new JsonResponse($moisSansVal);
    }

    /**
     * @Route("/tarifs/simulateur", name="tarifs_simulateur")
     */
    public function simulateur(VehiculeRepository $vehiculeRepo): Response
    {
        $vehicules = $vehiculeRepo->findAllVehiculesWithoutVendu();
        $options = $this->optionsRepo->findAll();
        $garanties = $this->garantiesRepo->findAll();
        $activeMode = $this->pricingModeService->getActiveModel();

        return $this->render('admin/tarifs/simulateur.html.twig', [
            'vehicules' => $vehicules,
            'options' => $options,
            'garanties' => $garanties,
            'activeMode' => $activeMode,
            'isV2Active' => $this->pricingModeService->isV2Active(),
        ]);
    }

    /**
     * @Route("/tarifs/calculate", name="tarifs_calculate", methods={"POST"})
     */
    public function calculate(Request $request): JsonResponse
    {
        try {
            // Récupérer les données du formulaire
            $dateDepartStr = $request->request->get('dateDepart');
            $dateRetourStr = $request->request->get('dateRetour');
            $vehiculeId = $request->request->get('vehiculeId');
            $optionsIds = $request->request->get('options', []);
            $garantiesIds = $request->request->get('garanties', []);
            $hasConducteur = $request->request->get('hasConducteur', false);

            if (!$dateDepartStr || !$dateRetourStr || !$vehiculeId) {
                return new JsonResponse(['error' => 'Paramètres manquants : dateDepart, dateRetour, vehiculeId requis'], 400);
            }

            $dateDepart = $this->dateHelper->newDate($dateDepartStr);
            $dateRetour = $this->dateHelper->newDate($dateRetourStr);
            $vehicule = $this->getDoctrine()->getRepository(\App\Entity\Vehicule::class)->find($vehiculeId);

            if (!$vehicule) {
                return new JsonResponse(['error' => 'Véhicule non trouvé'], 400);
            }

            // Calcul du tarif véhicule via le PricingStrategyProvider
            $strategy = $this->strategyProvider->getStrategy();
            $tarifVehicule = $strategy->calculate($vehicule, $dateDepart, $dateRetour);
            $duree = $this->pricingModeService->isV2Active() ? $this->dateHelper->calculDureeInclusif($dateDepart, $dateRetour) : $this->dateHelper->calculDuree($dateDepart, $dateRetour);

            // Récupération des options sélectionnées
            $optionsData = [];
            $optionsTotal = 0;
            if (is_array($optionsIds)) {
                foreach ($optionsIds as $optId) {
                    $option = $this->optionsRepo->find($optId);
                    if ($option) {
                        $optionsTotal += $option->getPrix();
                        $optionsData[] = [
                            'id' => $option->getId(),
                            'nom' => $option->getAppelation(),
                            'prix' => $option->getPrix(),
                        ];
                    }
                }
            }

            // Ajout conducteur supplémentaire si coché
            if ($hasConducteur) {
                $optionsTotal += $this->tarifsHelper->getPrixConducteurSupplementaire();
                $optionsData[] = [
                    'nom' => 'Conducteur supplémentaire',
                    'prix' => $this->tarifsHelper->getPrixConducteurSupplementaire(),
                ];
            }

            // Récupération des garanties sélectionnées
            $garantiesData = [];
            $garantiesTotal = 0;
            if (is_array($garantiesIds)) {
                foreach ($garantiesIds as $garId) {
                    $garantie = $this->garantiesRepo->find($garId);
                    if ($garantie) {
                        $garantiesTotal += $garantie->getPrix();
                        $garantiesData[] = [
                            'id' => $garantie->getId(),
                            'nom' => $garantie->getAppelation(),
                            'prix' => $garantie->getPrix(),
                        ];
                    }
                }
            }

            // Calcul du total
            $total = $tarifVehicule + $optionsTotal + $garantiesTotal;

            // Calcul du détail par mois pour les réservations multi-mois
            $detailMois = [];
            if ($duree > 30) {
                $detailMois = $this->calculerDetailMois($dateDepart, $dateRetour, $vehicule);
            }

            return new JsonResponse([
                'duree' => $duree,
                'tarifVehicule' => $tarifVehicule,
                'optionsTotal' => $optionsTotal,
                'options' => $optionsData,
                'garantiesTotal' => $garantiesTotal,
                'garanties' => $garantiesData,
                'total' => $total,
                'detailMois' => $detailMois,
                'pricingModel' => $this->pricingModeService->getActiveModel(),
                'pricingStrategy' => $strategy->getName(),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors du calcul: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    // ==================== PRICING SETTINGS ====================

    /**
     * @Route("/tarifs/settings", name="tarifs_settings")
     */
    public function settings(): Response
    {
        return $this->render('admin/tarifs/settings.html.twig', [
            'currentMode' => $this->pricingModeService->getActiveModel(),
            'isV2Active' => $this->pricingModeService->isV2Active(),
        ]);
    }

    // ==================== V2 PRICING METHODS ====================

    /**
     * @Route("/tarifs-v2", name="tarifs_v2_index")
     */
    public function indexV2(TarifsV2Repository $tarifsV2Repo): Response
    {
        $listeTarifsV2 = $tarifsV2Repo->findAll();
        $listeMois = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

        $marques = $this->marqueRepo->findAll();
        $modeles = $this->modeleRepo->findAll();
        
        $listeUniqueVehicules = [];
        foreach ($marques as $marque) {
            foreach ($modeles as $modele) {
                if ($marque == $modele->getMarque()) {
                    array_push($listeUniqueVehicules, $marque . " " . $modele);
                }
            }
        }

        $tarifsParVehicule = [];
        foreach ($listeUniqueVehicules as $veh) {
            $ordered = [];
            foreach ($listeMois as $mois) {
                $found = null;
                foreach ($listeTarifsV2 as $tarif) {
                    if ($tarif->getMois() == $mois && 
                        $tarif->getMarque()->getLibelle() . " " . $tarif->getModele()->getLibelle() == $veh) {
                        $found = $tarif;
                        break;
                    }
                }
                array_push($ordered, $found);
            }
            array_push($tarifsParVehicule, $ordered);
        }

        return $this->render('admin/tarifs/index_v2.html.twig', [
            'controller_name' => 'TarifsV2Controller',
            'tarifs' => $listeTarifsV2,
            'listeMois' => $listeMois,
            'listeVehicules' => $listeUniqueVehicules,
            'tarifsParVehicule' => $tarifsParVehicule,
            'isV2Active' => $this->pricingModeService->isV2Active(),
        ]);
    }

    /**
     * @Route("/tarif-v2/new", name="tarif_v2_new", methods={"GET","POST"})
     */
    public function newV2(Request $request): Response
    {
        $tarif = new TarifsV2();
        $form = $this->createForm(TarifsV2Type::class, $tarif);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Validate no overlapping ranges
            if ($tarif->hasOverlappingRanges()) {
                $this->addFlash('error', 'Les plages de jours se chevauchent.');
                return $this->render('admin/tarifs/new_v2.html.twig', [
                    'tarif' => $tarif,
                    'form' => $form->createView(),
                ]);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($tarif);
            $entityManager->flush();

            $this->addFlash('success', 'Tarif V2 créé avec succès.');
            return $this->redirectToRoute('tarifs_v2_index');
        }

        return $this->render('admin/tarifs/new_v2.html.twig', [
            'tarif' => $tarif,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/tarif-v2/{id}/edit", name="tarif_v2_edit", methods={"GET","POST"}, requirements={"id":"\d+"})
     */
    public function editV2(Request $request, TarifsV2 $tarif): Response
    {
        $form = $this->createForm(TarifsV2Type::class, $tarif);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Validate no overlapping ranges
            if ($tarif->hasOverlappingRanges()) {
                $this->addFlash('error', 'Les plages de jours se chevauchent.');
                return $this->render('admin/tarifs/edit_v2.html.twig', [
                    'tarif' => $tarif,
                    'form' => $form->createView(),
                ]);
            }

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Tarif V2 modifié avec succès.');
            return $this->redirectToRoute('tarifs_v2_index');
        }

        return $this->render('admin/tarifs/edit_v2.html.twig', [
            'tarif' => $tarif,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/tarif-v2/{id}", name="tarif_v2_delete", methods={"DELETE"}, requirements={"id":"\d+"})
     */
    public function deleteV2(Request $request, TarifsV2 $tarif): Response
    {
        if ($this->isCsrfTokenValid('delete' . $tarif->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($tarif);
            $entityManager->flush();
            $this->addFlash('success', 'Tarif V2 supprimé avec succès.');
        }
        return $this->redirectToRoute('tarifs_v2_index');
    }

    /**
     * @Route("/tarifs/toggle-mode", name="tarifs_toggle_mode", methods={"POST"})
     */
    public function toggleMode(Request $request): JsonResponse
    {
        if (!$this->isCsrfTokenValid('toggle', $request->request->get('_token'))) {
            return new JsonResponse(['error' => 'Token invalide'], 400);
        }

        $currentMode = $this->pricingModeService->getActiveModel();
        
        if ($currentMode === 'v1') {
            $this->pricingModeService->activateV2();
            $newMode = 'v2';
        } else {
            $this->pricingModeService->activateV1();
            $newMode = 'v1';
        }

        return new JsonResponse([
            'success' => true,
            'new_mode' => $newMode,
            'message' => $newMode === 'v2' ? 'Mode V2 activé' : 'Mode V1 activé',
        ]);
    }

    /**
     * Calcule le détail du tarif par mois pour l'affichage
     */
    private function calculerDetailMois($dateDepart, $dateRetour, $vehicule)
    {
        $marque = $vehicule->getMarque();
        $modele = $vehicule->getModele();
        $details = [];
        $dateCourante = clone $dateDepart;

        while ($dateCourante < $dateRetour) {
            $month = $this->dateHelper->getMonthFullName($dateCourante);
            $finDuMois = new \DateTime($dateCourante->format('Y-m-t'));
            $dateFinPeriode = ($finDuMois < $dateRetour) ? $finDuMois : $dateRetour;
            
            if ($this->pricingModeService->isV2Active()) {
                $joursDansPeriode = $this->dateHelper->calculDureeInclusif($dateCourante, $dateFinPeriode);
            } else {
                $joursDansPeriode = $this->dateHelper->calculDuree($dateCourante, $dateFinPeriode);
            }

            $bracket = '-';
            $prix = 0;

            if ($this->pricingModeService->isV2Active()) {
                $intervalRepo = $this->getDoctrine()->getRepository(\App\Entity\PricingInterval::class);
                $cellRepo = $this->getDoctrine()->getRepository(\App\Entity\TarifsV2Cell::class);

                // Find smallest containing interval
                $intervals = $intervalRepo->createQueryBuilder('pi')
                    ->where('pi.minDays <= :days')
                    ->andWhere('pi.maxDays IS NULL OR pi.maxDays >= :days')
                    ->setParameter('days', $joursDansPeriode)
                    ->orderBy('pi.maxDays', 'ASC')
                    ->addOrderBy('pi.minDays', 'ASC')
                    ->getQuery()
                    ->getResult();
                
                $interval = $intervals[0] ?? null;

                if ($interval) {
                    $bracket = $interval->getLabel();
                    $cell = $cellRepo->findOneBy([
                        'marque' => $marque,
                        'modele' => $modele,
                        'month' => $month,
                        'pricingInterval' => $interval
                    ]);
                    if ($cell) {
                        $prix = (float) $cell->getPrice() * $joursDansPeriode;
                    }
                }
            } else {
                $tarif = $this->getDoctrine()
                    ->getRepository(\App\Entity\Tarifs::class)
                    ->findOneBy(['marque' => $marque, 'modele' => $modele, 'mois' => $month]);

                if ($tarif) {
                    if ($joursDansPeriode <= 3) {
                        $prix = $tarif->getTroisJours();
                        $bracket = '3 jours';
                    } elseif ($joursDansPeriode <= 7) {
                        $prix = $tarif->getSeptJours();
                        $bracket = '7 jours';
                    } elseif ($joursDansPeriode <= 15) {
                        $prix = $tarif->getQuinzeJours();
                        $bracket = '15 jours';
                    } else {
                        $prix = $tarif->getTrenteJours();
                        $bracket = '30 jours';
                    }
                }
            }

            $details[] = [
                'mois' => $month,
                'dateDebut' => $dateCourante->format('d/m/Y'),
                'dateFin' => $dateFinPeriode->format('d/m/Y'),
                'jours' => $joursDansPeriode,
                'bracket' => $bracket,
                'prix' => $prix,
            ];

            $dateCourante = new \DateTime($finDuMois->format('Y-m-d') . ' +1 day');
        }

        return $details;
    }

    // ==================== V2 MATRIX INTERFACE ====================

    /**
     * @Route("/tarifs-v2/matrix", name="tarifs_v2_matrix")
     */
    public function matrixView(): Response
    {
        // Get all vehicles for the selector
        $vehicles = [];
        $marques = $this->marqueRepo->findAll();
        
        foreach ($marques as $marque) {
            $modeles = $this->modeleRepo->findBy(['marque' => $marque]);
            foreach ($modeles as $modele) {
                $vehicles[] = [
                    'marque_id' => $marque->getId(),
                    'modele_id' => $modele->getId(),
                    'name' => $marque->getLibelle() . ' ' . $modele->getLibelle()
                ];
            }
        }
        
        // Get first vehicle as default
        $defaultVehicle = $vehicles[0] ?? null;
        
        return $this->render('admin/tarifs/matrix.html.twig', [
            'vehicles' => $vehicles,
            'default_vehicle' => $defaultVehicle,
            'isV2Active' => $this->pricingModeService->isV2Active()
        ]);
    }

    /**
     * @Route("/tarifs-v2/comparison", name="tarifs_v2_comparison")
     */
    public function comparisonView(): Response
    {
        $listeMois = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
        
        return $this->render('admin/tarifs/comparison.html.twig', [
            'months' => $listeMois,
            'isV2Active' => $this->pricingModeService->isV2Active()
        ]);
    }

    /**
     * @Route("/tarifs-v2/intervals", name="tarifs_v2_intervals")
     */
    public function intervalsView(): Response
    {
        return $this->render('admin/tarifs/intervals.html.twig', [
            'isV2Active' => $this->pricingModeService->isV2Active()
        ]);
    }

    /**
     * @Route("/tarifs-v2/history", name="tarifs_v2_history")
     */
    public function historyView(): Response
    {
        $listeMois = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
        
        return $this->render('admin/tarifs/history.html.twig', [
            'months' => $listeMois,
            'isV2Active' => $this->pricingModeService->isV2Active()
        ]);
    }
}
