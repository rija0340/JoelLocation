<?php

namespace App\Classe;

use Mailjet\Client;
use Mailjet\Resources;
use Symfony\Bridge\Twig\TokenParser\DumpTokenParser;

class Mailjet
{
    //dans dashboard> preference du compte> gestion API key

    //key pour compte rija0340@gmail.com
    private $api_key = '353d0fe96ebc139412ba9d5a065d0826';
    private $api_key_secret =  '466090d3e5f229fa7bb780e76cb2f503';
    private $email_from = "rija0340@gmail.com";
    private $templateID  = 3190943;

    // key pour compte joel@joellocation.com
    // private $api_key = '9e0c14700d557919360cc8f742d1a15b';
    // private $api_key_secret =  '048654946039a06c8ea77ede2d5b2ec4';
    // private $email_from = "joel@joellocation.com";
    // private $templateID  = 3324755;

    public function send($to_email, $to_name, $subject, $content)
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
                        'content' => $content,
                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success();
        dd($response->getData());
    }
}
