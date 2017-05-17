<?php
/**
 * Created by PhpStorm.
 * User: Albertpv
 * AppFormatDate: 11/04/17
 * Time: 16:34
 */

namespace pwgram\Model\Repository;

use Doctrine\DBAL\Connection;
use pwgram\lib\Database\Database;
use pwgram\Model\Entity\User;
use Silex\Application;


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

    const TABLE_NAME    = "User";

    /**
     * @var Database class instance.
     */
    private $db;


    /**
     * PdoUserRepository constructor.
     * @param Database|Connection $db
     */
    public function __construct( $db)
    {
        $this->db = $db;
    }

    /**
     * Adds a new user to the database.
     *
     * @param User $row         The new user to be added.
     */
    public function add($row)
    {
        $this->db ->insert(PdoUserRepository::TABLE_NAME,
            array(
                'username'      => $row->getUsername(),
                'email'         => $row->getEmail(),
                'birthdate'     => $row->getBirthday(),
                'password'      => $row->getPassword(),
                'active'        => $row->getActive(),
                'profile_image' => $row->getProfileImage()
            ));
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
        $query  = "SELECT id, username, email, birthdate, active, profile_image FROM `User` WHERE id = ?";
        $user = $this->db->fetchAssoc($query, array((int) $id));

        if (!$user) return false; // an error happened during the execution

        return new User(
            $user["username"],
            $user["email"],
            $user["birthdate"],
            $user["active"],
            $user["profile_image"],
            $user["id"]
        );
    }

    /**
     * Checks if an username and/or email exists in the database.
     *
     * @param string $username The username to validate.
     * @param string $email    The email to validate.
     *
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
     *
     * @return bool true if there is no user with this username and email, false if not.
     */
    public function validateUnique($username, $email = "") {


        $query = "SELECT id FROM `User` WHERE username = ? OR email = ?";
        $res = $this->db->fetchAssoc($query, array($username, $email));

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
        $query = "UPDATE `User` SET username = ?, password = ?, email = ?, birthdate = ?, active = ?, profile_image = ? WHERE id = ?";
        $res = $this->db->executeUpdate(
            $query,
            array(
                $row->getUsername(),
                $row->getPassword(),
                $row->getEmail(),
                $row->getBirthday(),
                $row->getActive(),
                $row->getProfileImage(),
                $row->getId())
        );

        if ($res) $row->setPassword(null);
    }

    /**
     * Removes an existing username from the database.
     *
     * @param int $id The id of the user.
     */
    public function remove($id)
    {
        $this->db ->delete(PdoUserRepository::TABLE_NAME,
                            array(
                                'id' => $id
                            ));
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
    public function getId($username) {
        $query  = "SELECT id FROM `User` WHERE username = ?";
        $result = $this->db->fetchAssoc($query, array($username));

        if (!$result) return false; // an error happened during the execution

        return $result['id'];
    }

    public function getName($id) {
        $query = "SELECT username FROM `User` WHERE id = ?";
        $result = $this->db->fetchAssoc($query, array($id));

        if (!$result) return false;

        return $result['username'];
    }

    /**
     * Retrieves the value of the active flag. Util for validate if the user account
     * is already validate
     * @param $id
     * @return bool|mixed
     */
    public function getActive($id) {
        $query = "SELECT active FROM `User` WHERE id = ?";
        $result = $this->db->fetchAssoc($query, array($id));

        if (!$result) return true; // an error happened during the execution

        return $result['active'];
    }

    /**
     * Update user's active state. Used when user click on validation link.
     * When an user it registers, by default his active value is 0 until he access
     * the link validation via email or via web
     *
     * @param int $id
     * @return true     if updated correctly, false if not.
     */
    public function updateActiveState($id) {
        $query = "UPDATE `User` SET active = ? WHERE id = ?";
        $result = $this->db->executeUpdate($query, array(1, $id));

        if (!$result) return false;
        return true;
    }

    /**
     * @param $userNameOrEmail
     * @param string $password         hashed password
     * @return bool
     */
    // we don't have to validate the passwd here because we validate it in FormsUserController:loginUser
    public function validateUserLogin($userNameOrEmail) {
        $query = "SELECT id FROM `User` WHERE (username = ? OR email = ?)  AND active = 1 ";
        $result = $this->db->fetchAssoc($query,
                        array($userNameOrEmail, $userNameOrEmail));

        if ($result == false) return false; // an error happened during the execution

        return true;
    }

    /**
     * @param $userName
     * @param $password HASHED!!!
     */
    public function validateUserSession($userId) {
        $query = "SELECT id FROM `User` WHERE id = ?";
        $result = $this->db->fetchAssoc(
                    $query,
                    [
                        $userId
                    ]
                );
        if (!$result) return false; // an error happened during the execution

        return $result['id'];
    }

    /**
     * @param $userNameOrEmail
     * @return bool|mixed
     */

    public function getUsername($userNameOrEmail) {
        $query = "SELECT username FROM `User` WHERE username = ? OR email = ?";
        $result = $this->db->fetchAssoc(
            $query,
            [
                $userNameOrEmail,
                $userNameOrEmail
            ]
        );
        if (!$result) return false;

        return $result['username'];
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
        $result = $this->db->fetchAssoc(
            $query,
            [
                $userNameOrEmail,
                $userNameOrEmail
            ]
        );
        if (!$result) return false;

        return $result['password'];
    }

    /**
     *
     * @param $id
     */
    public function getProfileImage($id) {
        $query = "SELECT profile_image FROM `User` WHERE id = ?";
        $result = $this->db->fetchAssoc(
            $query,
            [
                $id
            ]
        );
        if (!$result) return false;

        return $result['profile_image'];
    }

}