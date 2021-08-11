<?php

namespace App\Service;

use DateTime;
use DateTimeZone;

class DateHelper
{

    /** 
     * @param $date datetime
     * @return string
     */
    function getMonthName($date): string
    {
        if (is_object($date)) {
            $month = $date->format('m');
        } else {
            $month = (new \DateTime($date))->format('m');
        }
        $monthFR = null;
        switch ($month) {
            case "01":
                $monthFR = 'JAN';
                break;
            case "02":
                $monthFR = 'FEV';
                break;
            case "03":
                $monthFR = 'MAR';
                break;
            case "04":
                $monthFR = 'AVR';
                break;
            case "05":
                $monthFR = 'MAI';
                break;
            case "06":
                $monthFR = 'JUI';
                break;
            case "07":
                $monthFR = 'JUIL';
                break;
            case "08":
                $monthFR = 'AOU';
                break;
            case "09":
                $monthFR = 'SEP';
                break;
            case "10":
                $monthFR = 'OCT';
                break;
            case "11":
                $monthFR = 'NOV';
                break;
            case "12":
                $monthFR = 'DEC';
                break;
        }

        return $monthFR;
    }
    /**
     * @param date1 et date2
     * @return int
     */

    function calculDuree($dateDepart, $dateRetour)
    {

        $duree = date_diff($dateDepart, $dateRetour);

        return $duree->days;
    }

    function dateNow()
    {
        return new DateTime('NOW', new DateTimeZone('+0300'));
    }

    //parametre objet date
    function newDate($date)
    {
        return new DateTime($date, new DateTimeZone('+0300'));
    }
}
