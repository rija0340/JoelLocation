<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

class Site
{

    public function getBaseUrl(Request $request)
    {
        // Get the scheme (protocol)
        $scheme = $request->getScheme();

        // Get the host (domain)
        $host = $request->getHost();

        // Get the port
        $port = $request->getPort();
        $url  = $scheme . "://" . $host;
        $url = $port != null ?  $url . ":" . $port : $url;
        return $url;
    }
}
