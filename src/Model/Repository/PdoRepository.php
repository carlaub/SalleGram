<?php
/**
 * Created by PhpStorm.
 * User: Albertpv
 * Date: 11/04/17
 * Time: 16:29
 */

namespace pwgram\Model\Repository;


interface PdoRepository {

    public function add     ($row);

    public function get     ($id);

    public function update  ($row);

    public function remove  ($id);


}