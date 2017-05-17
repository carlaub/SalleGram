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

    /**
     * Converts an array of objects to its equivalent in a JSON format
     * string.
     *
     * @param array $array an array of objects.
     * @return string objects to Json String
     */
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