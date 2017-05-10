<?php
/**
 * Created by PhpStorm.
 * User: Albertpv
 * Date: 10/05/17
 * Time: 12:57
 */

namespace pwgram\Model\Services;


class ObjectsJsonParser
{

    function objectToJson($array) {
        $result = [];
        $current = null;

        foreach ($array as $object) {

            $current = $object->getVars();
            array_push($result, $current);
        }

        return json_encode($result);
    }
}