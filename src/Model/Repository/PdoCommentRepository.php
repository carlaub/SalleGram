<?php
/**
 * Created by PhpStorm.
 * User: Albertpv
 * Date: 11/04/17
 * Time: 21:20
 */

namespace pwgram\Model\Repository;

use pwgram\lib\Database\Database;

// not checked
class PdoCommentRepository implements PdoRepository
{

    private $db;

    public function __construct(Database $db)
    {

        $this->db = $db;
    }

    public function add($row)
    {
        $query  = "INSERT INTO Comment(`content`, `last_modified`, `fk_user`, `fk_image`) VALUES(?, ?, ?, ?)";
        $result = $this->db->preparedQuery(
            $query,
            [
                $row->getContent(),
                $row->getLastModified(),
                $row->getFkUser(),
                $row->getFkImage()
            ]
        );

        return !$result;
    }

    public function get($id)
    {
        // TODO: Implement get() method.
    }

    public function update($row)
    {
        // TODO: Implement update() method.
    }

    public function remove($id)
    {
        // TODO: Implement remove() method.
    }
}