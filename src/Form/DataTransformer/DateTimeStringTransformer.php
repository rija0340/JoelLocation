<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

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

        // Clean the input string
        $value = trim($value);

        // Try HTML5 datetime-local format first (YYYY-MM-DDTHH:MM or YYYY-MM-DDTHH:MM:SS)
        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}(:\d{2})?$/', $value)) {
            $date = \DateTime::createFromFormat('Y-m-d\TH:i', $value);
            if (!$date) {
                // Try with seconds
                $date = \DateTime::createFromFormat('Y-m-d\TH:i:s', $value);
            }
            if ($date) {
                return $date;
            }
        }

        // Try ISO 8601 format (YYYY-MM-DD HH:MM:SS)
        if (preg_match('/^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}$/', $value)) {
            $date = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
            if ($date) {
                return $date;
            }
        }

        // Try ISO 8601 format without seconds (YYYY-MM-DD HH:MM)
        if (preg_match('/^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}$/', $value)) {
            $date = \DateTime::createFromFormat('Y-m-d H:i', $value);
            if ($date) {
                return $date;
            }
        }

        // Try French format with seconds (dd/mm/YYYY HH:MM:SS)
        if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}\s\d{1,2}:\d{2}:\d{2}$/', $value)) {
            $date = \DateTime::createFromFormat('d/m/Y H:i:s', $value);
            if ($date) {
                return $date;
            }
        }

        // Try the configured format (default: d/m/Y H:i)
        $date = \DateTime::createFromFormat($this->format, $value);
        if ($date) {
            return $date;
        }

        // Try some common variations of the French format
        $commonFormats = [
            'd/m/Y H:i',      // 25/12/2023 14:30
            'd/m/Y G:i',      // 25/12/2023 2:30 (single digit hour)
            'j/n/Y H:i',      // 5/2/2023 14:30 (single digit day/month)
            'j/n/Y G:i',      // 5/2/2023 2:30 (all single digits)
            'd-m-Y H:i',      // 25-12-2023 14:30
            'd.m.Y H:i',      // 25.12.2023 14:30
        ];

        foreach ($commonFormats as $format) {
            $date = \DateTime::createFromFormat($format, $value);
            if ($date) {
                return $date;
            }
        }

        // Last resort: try native DateTime constructor
        try {
            return new \DateTime($value);
        } catch (\Exception $e) {
            // If all fails, throw a more informative exception
            throw new TransformationFailedException(sprintf(
                'Format de date invalide: "%s". Formats acceptés: %s, YYYY-MM-DDTHH:MM (mobile), YYYY-MM-DD HH:MM',
                $value,
                $this->format
            ));
        }
    }
}