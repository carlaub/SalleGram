<?php
/**
 * Created by PhpStorm.
 * User: Albertpv
 * Date: 15/05/17
 * Time: 02:31
 */

namespace pwgram\Model\Services;


use pwgram\Model\Repository\PdoFollowRepository;

class PdoMapper
{

    const PDO_FOLLOW    = 1;


    function getPdoFollowReference() {

        return new PdoFollowRepository();
    }
}