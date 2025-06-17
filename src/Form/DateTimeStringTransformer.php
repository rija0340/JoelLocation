<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class DateTimeStringTransformer implements DataTransformerInterface
{
    private $format;

    public function __construct(string $format = 'd/m/Y H:i')
    {
        $this->format = $format;
    }

    public function transform($value)
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format($this->format);
        }

        return '';
    }

    public function reverseTransform($value)
    {
        if (!$value) {
            return null;
        }

        $date = \DateTime::createFromFormat($this->format, $value);

        if (!$date) {
            throw new \Exception("Format de date invalide");
        }

        return $date;
    }
}
