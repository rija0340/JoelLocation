<?php

namespace App\Service;


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
}
