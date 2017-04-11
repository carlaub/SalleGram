<?php
/**
 * Created by PhpStorm.
 * User: Albertpv
 * Date: 11/04/17
 * Time: 16:34
 */

namespace pwgram\Model\Repository;

use pwgram\lib\Database\Database;
use pwgram\Model\Entity\User;


/**
 * Class PdoUserRepository
 *
 * <p>This class manages the User table of <i>PWGRAM</i>. Any kind of change that is needed
 * to do to this table must be done using an instance from this class.</p>
 *
 * @author Carla Urrea
 * @author Jorge Melguizo
 * @author Albert Pernía
 *
 * @version 1.0
 *
 * @package pwgram\Model\Repository
 */
class PdoUserRepository implements PdoRepository {

    /**
     * @var Database class instance.
     */
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }


    public function add($row)
    {
        $query  = "INSERT INTO `User`(`username`, `email`, `birthdate`, `password`, `active`) VALUES(?, ?, ?, ?, ?)";
        $result = $this->db->preparedQuery(
            $query,
            [
                $row->getUsername(),
                $row->getEmail(),
                $row->getBirthday(),
                $row->getPassword(),
                $row->getActive()
            ]
        );

        if ($result) $row->setPassword(null);
    }

    /**
     * Finds a user by id. This method does not obtain the password of the user.
     *
     * @param $id   The id of the user to find.
     *
     * @return bool|User false if the user could not be found or the user in case it exists.
     */
    public function get($id)
    {
        $query  = "SELECT id, username, email, birthdate, active FROM `User` WHERE id = ?";
        $result = $this->db->preparedQuery(
            $query,
            [
                $id
            ]
        );
        if (!$result) return false; // an error happened during the execution

        $user = $result->fetch();

        if (!$user) return false;   // user not found

        return new User(
            $user["username"],
            $user["email"],
            $user["birthday"],
            $user["active"]
        );
    }


    /**
     * Checks if an username and/or email exists in the database.
     *
     * @param $username The username to validate.
     * @param $email    The email to validate.
     *
     * @return string Encoded JSON with the structure:
     *          {
     *              "RESULT"    :   OK/KO,
     *              "username"  :   OK/KO,
     *              "email"     :   OK/KO
     *          }
     */
    // Not checked
    public function validateUniqueExtra($username, $email) {
        $response = array(
            'STATUS' => 'OK',
            'username' => 'OK',
            'email'    => 'OK'
        );

        $query = "SELECT id FROM `User` WHERE username = ?";
        $userResult = $this->db->preparedQuery($query, [ $username ] );

        $res = $userResult->fetch();

        if (!$res) $response['STATUS'] = $response['username'] = 'KO';

        $query = "SELECT id FROM `User` WHERE email = ?";
        $emailResult = $this->db->preparedQuery($query, [ $email ] );

        $res = $emailResult->fetch();

        if (!$res) $response['STATUS'] = $response['email'] = 'KO';

        return json_encode($response);
    }


    /**
     * Checks if an username and/or email exists in the database. The method
     * @see validateUniqueExtra should be used instead of this if it is needed
     * to know exactly whether the username nor email exists to give a detailed
     * message.
     *
     * @param $username The username to validate.
     * @param $email    The email to validate.
     *
     * @return bool true if there is no user with this username and email, false if not.
     */
    // Not checked
    public function validateUnique($username, $email) {

        $query = "SELECT id FROM `User` WHERE username = ? OR email = ?";
        $result = $this->db->preparedQuery(
            $query,
            [
                $username,
                $email
            ]
        );

        $res = $result->fetch();

        return $res !== false;
    }

    /**
     * Updates an existing user with new information.
     *
     * @param $row The updated user.
     */
    public function update($row)
    {
        $query = "UPDATE `User` SET username = ?, password = ?, email = ?, birthdate = ?, active = ? WHERE id = ?";
        $result = $this->db->preparedQuery(
            $query,
            [
                $row->getUsername(),
                $row->getPassword(),
                $row->getEmail(),
                $row->getBirthday(),
                $row->getActive(),
                $row->getId()
            ]
        );

        if ($result) $row->setPassword(null);
    }

    /**
     * Removes an existing username from the database.
     *
     * @param $id The id of the user.
     */
    public function remove($id)
    {
        $query = "DELETE FROM `User` WHERE id = ?";
        $result = $this->db->preparedQuery(
            $query,
            [
                $id
            ]
        );
    }
}