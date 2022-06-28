<?php

namespace App\Classe;

use Mailjet\Client;
use Mailjet\Resources;
use Symfony\Bridge\Twig\TokenParser\DumpTokenParser;

class Mailjet
{
    //dans dashboard> preference du compte> gestion API key

    //key pour compte rija0340@gmail.com
    // private $api_key = '353d0fe96ebc139412ba9d5a065d0826';
    // private $api_key_secret =  '466090d3e5f229fa7bb780e76cb2f503';
    // private $email_from = "rija0340@gmail.com";
    // private $templateID  = 3190943;


    // key pour compte contact.joellocation@gmail.com
    //new api key march 2022
    private $api_key = 'affab7bbb2f993c330acfdd8deac52b7';
    private $api_key_secret = '78d2b360a2025239389a1f9032c0e5d8';
    private $email_from = "contact.joellocation@gmail.com";

    private $templateID = 3331640;

    //    private $confirmationInscriptionTemplate = 3747205;
    //    private $confirmationReservationTemplate = 3747323;

    public function send($to_email, $to_name, $subject, $message)
    {

        $mj = new Client($this->api_key, $this->api_key_secret, true, ['version' => 'v3.1']);

        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => $this->email_from,
                        'Name' => "JOEL LOCATION"
                    ],
                    'To' => [
                        [
                            'Email' => $to_email,
                            'Name' => $to_name
                        ]
                    ],
                    'TemplateID' => $this->templateID,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        'objet' => $subject,
                        'message' => $message,
                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success();
    }

    public function sendToMe($nom, $email, $telephone, $adresse, $objet, $message)
    {

        $mj = new Client($this->api_key, $this->api_key_secret, true, ['version' => 'v3.1']);

        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "contact.joellocation@gmail.com",
                        'Name' => $nom
                    ],
                    'To' => [
                        [
                            'Email' => "contact.joellocation@gmail.com",
                            'Name' => "JOEL LOCATION"
                        ]
                    ],
                    //                    'TemplateID' => 3357170, //template codé en html dans mailjet
                    'TemplateID' => 3856453, //template codé en html dans mailjet
                    'TemplateLanguage' => true,
                    'Subject' => $objet,
                    'Variables' => [
                        'nom' => $nom,
                        'email' => $email,
                        'telephone' => $telephone,
                        'adresse' => $adresse,
                        'objet' => $objet,
                        'message' => $message,
                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body ' => $body]);
        return $response->success();
    }


    public function confirmationInscription($nom, $email, $objet, $password)
    {
        $mj = new Client($this->api_key, $this->api_key_secret, true, ['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "contact.joellocation@gmail.com",
                        'Name' => "JOEL LOCATION"
                    ],
                    'To' => [
                        [
                            'Email' => $email,
                            'Name' => $nom
                        ]
                    ],
                    'TemplateID' => 3747205, //template codé en html dans mailjet
                    'TemplateLanguage' => true,
                    'Subject' => $objet,
                    'Variables' => [
                        'email' => $email,
                        'password' => $password,

                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        return $response->success();
    }

    public function confirmationReservation(
        $nom,
        $email,
        $objet,
        $dateResa,
        $refResa,
        $vehicule,
        $dateHeureDepart,
        $dateHeureRetour,
        $montantResa,
        $vingtcinqPourcent,
        $cinquantePourcent,
        $soixantequizePourcent,
        $linkDevis

    ) {
        $mj = new Client($this->api_key, $this->api_key_secret, true, ['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "contact.joellocation@gmail.com",
                        'Name' => "JOEL LOCATION"
                    ],
                    'To' => [
                        [
                            'Email' => $email,
                            'Name' => $nom
                        ]
                    ],
                    'TemplateID' => 3747323, //template codé en html dans mailjet
                    'TemplateLanguage' => true,
                    'Subject' => $objet,
                    'Variables' => [
                        'nom' => $nom,
                        'dateResa' => $dateResa,
                        'refResa' => $refResa,
                        'vehicule' => $vehicule,
                        'dateHeureDepart' => $dateHeureDepart,
                        'dateHeureRetour' => $dateHeureRetour,
                        'montantResa' => $montantResa,
                        'vingtcinqPourcent' => $vingtcinqPourcent,
                        'cinquantePourcent' => $cinquantePourcent,
                        'soixantequizePourcent' => $soixantequizePourcent,
                        'linkDevis' =>  $linkDevis
                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        return $response->success();
    }

    public function confirmationDevis($nom, $email, $objet, $dateResa, $refResa, $vehicule, $dateHeureDepart, $dateHeureRetour, $linkDevis, $linkReservation)
    {
        $mj = new Client($this->api_key, $this->api_key_secret, true, ['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "contact.joellocation@gmail.com",
                        'Name' => "JOEL LOCATION"
                    ],
                    'To' => [
                        [
                            'Email' => $email,
                            'Name' => $nom
                        ]
                    ],
                    'TemplateID' => 3760365, //template codé en html dans mailjet
                    'TemplateLanguage' => true,
                    'Subject' => $objet,
                    'Variables' => [
                        'nom' => $nom,
                        'dateResa' => $dateResa,
                        'refResa' => $refResa,
                        'vehicule' => $vehicule,
                        'dateHeureDepart' => $dateHeureDepart,
                        'dateHeureRetour' => $dateHeureRetour,
                        'linkDevis' => $linkDevis,
                        'linkReservation' => $linkReservation,
                        //                        'dateValiditeDevis'=> $dateValiditeDevis
                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        return $response->success();
    }


    public function confirmationPaiement($nom, $email, $objet, $dateResa, $refResa, $vehicule, $dateHeureDepart, $dateHeureRetour, $prixResa, $sommePaiement)
    {
        $mj = new Client($this->api_key, $this->api_key_secret, true, ['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "contact.joellocation@gmail.com",
                        'Name' => "JOEL LOCATION"
                    ],
                    'To' => [
                        [
                            'Email' => $email,
                            'Name' => $nom
                        ]
                    ],
                    //  A CHANGER
                    'TemplateID' => 3895677, //template codé en html dans mailjet
                    'TemplateLanguage' => true,
                    'Subject' => $objet,
                    'Variables' => [
                        'nom' => $nom,
                        'dateResa' => $dateResa,
                        'refResa' => $refResa,
                        'vehicule' => $vehicule,
                        'dateHeureDepart' => $dateHeureDepart,
                        'dateHeureRetour' => $dateHeureRetour,
                        'prixResa' => $prixResa,
                        'sommePaiement' => $sommePaiement,
                        //                        'dateValiditeDevis'=> $dateValiditeDevis
                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        return $response->success();
    }

    public function appelPaimentSolde(
        $nom,
        $email,
        $objet,
        $dateResa,
        $refResa,
        $vehicule,
        $dateHeureDepart,
        $dateHeureRetour,
        $prixResa,
        $sommePaiement,
        $soldePaiement
    ) {
        $mj = new Client($this->api_key, $this->api_key_secret, true, ['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "contact.joellocation@gmail.com",
                        'Name' => "JOEL LOCATION"
                    ],
                    'To' => [
                        [
                            'Email' => $email,
                            'Name' => $nom
                        ]
                    ],
                    //  A CHANGER
                    'TemplateID' => 3953714, //template codé en html dans mailjet
                    'TemplateLanguage' => true,
                    'Subject' => $objet,
                    'Variables' => [
                        'nom' => $nom,
                        'dateResa' => $dateResa,
                        'refResa' => $refResa,
                        'vehicule' => $vehicule,
                        'dateHeureDepart' => $dateHeureDepart,
                        'dateHeureRetour' => $dateHeureRetour,
                        'prixResa' => $prixResa,
                        'sommePaiement' => $sommePaiement,
                        'soldePaiement' => $soldePaiement,
                        //                        'dateValiditeDevis'=> $dateValiditeDevis
                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        return $response->success();
    }

    public function confirmationPaiementSolde($nom, $email, $objet, $dateResa, $refResa, $vehicule, $dateHeureDepart, $dateHeureRetour, $prixResa, $sommePaiement)
    {
        $mj = new Client($this->api_key, $this->api_key_secret, true, ['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "contact.joellocation@gmail.com",
                        'Name' => "JOEL LOCATION"
                    ],
                    'To' => [
                        [
                            'Email' => $email,
                            'Name' => $nom
                        ]
                    ],
                    //  A CHANGER
                    'TemplateID' => 3906925, //template codé en html dans mailjet
                    'TemplateLanguage' => true,
                    'Subject' => $objet,
                    'Variables' => [
                        'nom' => $nom,
                        'dateResa' => $dateResa,
                        'refResa' => $refResa,
                        'vehicule' => $vehicule,
                        'dateHeureDepart' => $dateHeureDepart,
                        'dateHeureRetour' => $dateHeureRetour,
                        'prixResa' => $prixResa,
                        'sommePaiement' => $sommePaiement,
                        //                        'dateValiditeDevis'=> $dateValiditeDevis
                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        return $response->success();
    }

    public function envoiDevis($nom, $email, $objet, $dateResa, $refResa, $vehicule, $dateHeureDepart, $dateHeureRetour, $linkDevis)
    {
        $mj = new Client($this->api_key, $this->api_key_secret, true, ['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "contact.joellocation@gmail.com",
                        'Name' => "JOEL LOCATION"
                    ],
                    'To' => [
                        [
                            'Email' => $email,
                            'Name' => $nom
                        ]
                    ],
                    'TemplateID' => 3771104, //template codé en html dans mailjet
                    'TemplateLanguage' => true,
                    'Subject' => $objet,
                    'Variables' => [
                        'nom' => $nom,
                        'dateResa' => $dateResa,
                        'refResa' => $refResa,
                        'vehicule' => $vehicule,
                        'dateHeureDepart' => $dateHeureDepart,
                        'dateHeureRetour' => $dateHeureRetour,
                        'linkDevis' => $linkDevis
                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        return $response->success();
    }


    public function sendToContacJoelLocation($nom, $email, $telephone, $adresse, $objet, $message, $btnRepondre)
    {
        $mj = new Client($this->api_key, $this->api_key_secret, true, ['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "contact.joellocation@gmail.com",
                        'Name' => "JOEL LOCATION"
                    ],
                    'To' => [
                        [
                            'Email' => "contact.joellocation@gmail.com",
                            'Name' => $nom
                        ]
                    ],
                    'TemplateID' => 3856453, //template codé en html dans mailjet
                    'TemplateLanguage' => true,
                    'Subject' => $objet,
                    'Variables' => [
                        'nom' => $nom,
                        'email' => $email,
                        'telephone' => $telephone,
                        'adresse' => $adresse,
                        'objet' => $objet,
                        'message' => $message,
                        'btnRepondre' => $btnRepondre,

                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        return $response->success();
    }




    //Vous : {{var:nom:""}}
    //Réservation du : {{var:dateResa:""}}
    //Code de référence : {{var:refResa:""}}
    //Votre location :
    //Catégorie du véhicule : ou similaire
    //Départ : {{var:dateHeureDepart:""}}
    //Retour : {{var:dateHeureRetour:""}}
}
