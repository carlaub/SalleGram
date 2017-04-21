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

    const DATE_FORMAT   = 'Y-m-d H:i:s';
    const DAY_SECONDS   = 86400;

    public static function today() {

        $today  = new DateTime();
        $format = AppFormatDate::DATE_FORMAT;

        return $today->format($format);
    }

    public static function toAppFormat(DateTime $date) {

        $format = AppFormatDate::DATE_FORMAT;

        return $date->format($format);
    }

    public static function diff(DateTime $date1, DateTime $date2) {

        return $date2->getTimestamp() - $date1->getTimestamp();
    }

}