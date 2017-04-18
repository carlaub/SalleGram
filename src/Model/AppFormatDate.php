<?php
/**
 * Created by PhpStorm.
 * User: Albertpv
 * AppFormatDate: 18/04/17
 * Time: 00:37
 */

namespace pwgram\Model;

use \DateTime;


/**
 * Class AppFormatDate
 *
 * This class groups the methods corresponding to the management of dates
 * to hold the same format whatever a date is needed.
 *
 * @package pwgram\Model\Entity
 */
class AppFormatDate
{

    const DATE_FORMAT   = 'Y-m-d';


    public static function today() {

        $today  = new DateTime();
        $format = AppFormatDate::DATE_FORMAT;

        return $today->format($format);
    }

    public static function toAppFormat(DateTime $date) {

        $format = AppFormatDate::DATE_FORMAT;

        return $date->format($format);
    }

}