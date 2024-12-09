<?php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class Sha1Extension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('sha1', [$this, 'sha1Filter']),
        ];
    }

    public function sha1Filter($value)
    {
        return sha1($value);
    }
}
