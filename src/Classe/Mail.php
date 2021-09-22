<?php

namespace App\Classe;

use Mailjet\Client;
use Mailjet\Resources;


class Mail
{

    private $api_key = '353d0fe96ebc139412ba9d5a065d0826';
    private $api_key_secret =  '466090d3e5f229fa7bb780e76cb2f503';

    public function send($to_email, $to_name, $subject, $content)
    {

        $mj = new Client($this->api_key, $this->api_key_secret, true, ['version' => 'v3.1']);

        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "rija0340@gmail.com",
                        'Name' => "Joel Location"
                    ],
                    'To' => [
                        [
                            'Email' => $to_email,
                            'Name' => $to_name
                        ]
                    ],
                    'TemplateID' => 3190943,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        'content' => $content,
                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success();
    }
}
