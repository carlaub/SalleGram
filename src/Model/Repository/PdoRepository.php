<?php


namespace pwgram\Model\Repository;


interface PdoRepository {

    const MAX_RESULTS_LIMIT   = 18446744073709551;

    public function add     ($row);

    public function get     ($id);

    public function update  ($row);

    public function remove  ($id);

    public function length  ();
}