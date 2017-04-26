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

    public static function timeFromNowMessage(DateTime $date) {
        $msg = "";

        $diff = $date->diff(new DateTime());

        if ($diff->d >= 1) {    // more than a day

            if ($diff->h == 0)
                $msg = "Publicado hace " . $diff->d . " días.";

            else if ($diff->h == 1)
                $msg = "Publicado hace " . $diff->d . " días y " . $diff->h . " hora.";

            else $msg = "Publicado hace " . $diff->d . " días y " . $diff->h . " horas.";
        }

        else if ($diff->h > 0)

            $msg = "Publicado hace ". $diff->h . " horas y " . $diff->m . " minutos.";

        else if ($diff->i > 0)

            if ($diff->s == 0)      $msg = "Publicado hace ". $diff->i . " minutos.";
            else if ($diff->s == 1) $msg = "Publicado hace ". $diff->i . " minutos y ". $diff->s . " segundo.";
            else                    $msg = "Publicado hace ". $diff->i . " minutos y ". $diff->s . " segundos.";

        else if ($diff->s > 0)

            if ($diff->s == 1) $msg = "Publicado hace tan solo ". $diff->s ." segundos.";
            else $msg = "Publicado hace tan solo ". $diff->s ." segundos.";

        else $msg = "Publicado ahora mismo";

        return $msg;
    }
}