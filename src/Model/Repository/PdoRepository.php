<?php


namespace pwgram\Model\Repository;


interface PdoRepository {

    public function add     ($row);

    public function get     ($id);

    public function update  ($row);

    public function remove  ($id);

    public function length  ();
}