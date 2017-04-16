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
 * @version 1.0.1
 *
 * @see PdoRepository
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

    /**
     * Adds a new user to the database.
     *
     * @param User $row     The new user to be added.
     */
    public function add($row)
    {
        $query  = "INSERT INTO `User`(`username`, `email`, `birthdate`, `password`, `active`, `profile_image`) VALUES(?, ?, ?, ?, ?, ?)";
        $result = $this->db->preparedQuery(
            $query,
            [
                $row->getUsername(),
                $row->getEmail(),
                $row->getBirthday(),
                $row->getPassword(),
                $row->getActive(),
                $row->getProfileImage()
            ]
        );

        if ($result) $row->setPassword(null);
    }

    /**
     * Finds a user by id. This method does not obtain the password of the user.
     *
     * @param int $id   The id of the user to find.
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
            $user["active"],
            $user["id"]
        );
    }


    // not working, yeah.
    public function getByField($field, $value) {

        $query = "SELECT ? FROM `User` WHERE ? = ?";
        $result = $this->db->preparedQuery(
            $query,
            [
                $field,
                $field,
                $value
            ]
        );

        if (!$result) return false;

        $res = $result->fetch();

        if (!$res) return false;

        return $res[$field];
    }


    /**
     * Checks if an username and/or email exists in the database.
     *
     * @param string $username The username to validate.
     * @param string $email    The email to validate.
     *
     * Note: this method adds extra logic not "natural" for
     *       the class. It will be practically moved to
     *       validator when the fucking method getByField
     *       starts to work.
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
     * @param string $username The username to validate.
     * @param string $email    The email to validate.
     *
     * Note: this method adds extra logic not "natural" for
     *       the class. It will be practically moved to
     *       validator when the fucking method getByField
     *       starts to work.
     *
     * @return bool true if there is no user with this username and email, false if not.
     */

    /**
     * Verify user isn't in DB
     * @param $username
     * @param $email
     * @return bool
     */
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

        //$res == false, user isn't in db
        return $res == false;


    }

    /**
     * Updates an existing user with new information.
     *
     * @param User $row The updated user.
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
     * @param int $id The id of the user.
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

    public function length()
    {
        $query = "SELECT COUNT(*) AS total FROM `User`";
        $result = $this->db->query($query);

        if (!$result) return 0;

        $total = $result->fetch();

        return $total['total'];
    }

    /**
     * Return user id
     * @param $userName
     */
    public function getId($userName) {
        $query  = "SELECT id FROM `User` WHERE username = ?";
        $result = $this->db->preparedQuery(
            $query,
            [
                $userName
            ]
        );
        if (!$result) return false; // an error happened during the execution

        $results = $result->fetch();

        return $results['id'];
    }

    /**
     * Retrieves the value of the active flag. Util for validate if the user account
     * is already validate
     * @param $id
     * @return bool|mixed
     */
    public function getActive($id) {
        $query = "SELECT active FROM `User` WHERE id = ?";
        $result = $this->db->preparedQuery(
            $query,
            [
                $id
            ]
        );
        if (!$result) return true; // an error happened during the execution

        $results = $result->fetch();

        return $results['active'];
    }

    /**
     * Update user's active state. Used when user click on validation link.
     * When an user it registers, by default his active value is 0 until he access
     * the link validation via email or via web
     * @param $id
     */
    public function updateActiveState($id) {
        $query = "UPDATE `User` SET active = ? WHERE id = ?";
        $result = $this->db->preparedQuery(
            $query,
            [
                1,
                $id
            ]
        );

        $results = $result->fetch();
        if (!$results) return false;
        return true;
    }

    public function validateUserLogin($userNameOrEmail, $password) {
        $query = "SELECT id FROM `User` WHERE (username = ? OR email = ?) AND password = ?";
        $result = $this->db->preparedQuery(
            $query,
            [
                $userNameOrEmail,
                $userNameOrEmail,
                $password
            ]
        );
        if (!$result) return false; // an error happened during the execution

        $results = $result->fetch();
        if (!$results) return false; // no user with these characteristics

        return true;
    }

    /**
     * This functions return user password from the user name or email.
     * In case that user don't exists in data base, the function will return "false"
     * instead of the password.
     *
     * @param $userNameOrEmail
     * @return bool|mixed
     */
    public function getPassword($userNameOrEmail) {
        $query = "SELECT password FROM `User` WHERE (username = ? OR email = ?)";
        $result = $this->db->preparedQuery(
            $query,
            [
                $userNameOrEmail,
                $userNameOrEmail,
            ]
        );
        if (!$result) return false;
        $results = $result->fetch();
        if(!$results) return false;

        return $results['password'];
    }
}