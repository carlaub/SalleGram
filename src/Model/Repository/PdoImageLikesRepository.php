<?php
/**
 * Created by PhpStorm.
 * User: Albertpv
 * Date: 14/04/17
 * Time: 00:33
 */

namespace pwgram\Model\Repository;


use Doctrine\DBAL\Connection;
use pwgram\lib\Database\Database;
use pwgram\Model\Entity\ImageLike;
use Silex\Application;

class PdoImageLikesRepository implements PdoRepository
{

    const TABLE_NAME    = "Image_likes";

    /**
     * @var Database class instance.
     */
    private $db;

    /**
     * PdoImageLikesRepository constructor.
     * @param Database|Connection $db
     */
    public function __construct($db)
    {
        $this->db = $db;
    }


    public function add($row)
    {
        $result = $this->db->insert(
            PdoImageLikesRepository::TABLE_NAME,
            array(
                'fk_user'   =>  $row->getFkUser(),
                'fk_image'  =>  $row->getFkImage()
            )
        );

        return !$result;
    }

    public function get($id)
    {
        $query  = "SELECT * FROM `Image_likes` WHERE id = ?";
        $comment = $this->db->fetchAssoc(
            $query,
            array(
                $id
            )
        );
        if (!$comment) return false;   // comment not found

        return new ImageLike(
            $comment['fk_user'],
            $comment['fk_image'],
            $comment['id']
        );
    }

    public function getTotalUserLikes($id) {

        $query  = "SELECT COUNT(*) as total FROM Image_likes WHERE fk_user = ?";
        $total  = $this->db->fetchAssoc(
            $query,
            array(
                $id
            )
        );

        if (!$total) return 0;

        return $total['total'];
    }

    public function update($row)
    {
        $query = "UPDATE `Image_likes` SET fk_user = ?, fk_image = ? WHERE id = ?";
        $result = $this->db->executeUpdate(
            $query,
            array(
                $row->getFkUser(),
                $row->getFkImage(),
                $row->getId()
            )
        );
    }

    public function remove($id)
    {
        $this->db->delete(PdoImageLikesRepository::TABLE_NAME,
            array(
                'id' => $id
            ));
    }
    public function removeLike($idImage, $idUser)
    {
        $this->db->delete(PdoImageLikesRepository::TABLE_NAME,
            array(
                'fk_image' => $idImage,
                'fk_user' => $idUser

            ));
    }

    public function removeImageLikes($idImage)
    {
        $this->db->delete(PdoImageLikesRepository::TABLE_NAME,
            array(
                'fk_image' => $idImage

            ));
    }


    public function length()
    {
        $result = $this->db->executeQuery("SELECT COUNT(*) AS total FROM Image_likes");

        if (!$result) return 0;

        $total = $result->fetch();

        return $total['total'];
    }

    public function likevalid($idImage, $idUser) {
        $query = "SELECT id FROM `Image_likes` WHERE fk_image = ? AND fk_user = ?";
        $result = $this->db->fetchAssoc(
            $query,
            array(
                $idImage,
                $idUser
            )
        );

        //TODO: check with the update
        if (!$result) return true;

        //if ($results == null) return true; // User hasn't put like

        return false; // User put like
    }
}