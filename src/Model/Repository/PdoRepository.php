<?php


namespace pwgram\Model\Repository;

use Silex\Application;

interface PdoRepository {

    const MAX_RESULTS_LIMIT   = 18446744073709551615;

    public function add     (Application $app, $row);

    public function get     (Application $app, $id);

    public function update  (Application $app, $row);

    public function remove  (Application $app, $id);

    public function length  (Application $app);
}