<?php


namespace pwgram\Controller;
use pwgram\lib\Database\Database;
use pwgram\Model\Entity\User;
use pwgram\Model\Repository\PdoUserRepository;
use \DateTime;

/**
 * Class Validator
 *
 * This class validates all the data that fluctuates
 * through the app (ex: registration fields).
 *
 * @version 1.0
 *
 * @package pwgram\Controller
 */
class Validator
{

    const MAX_USERNAME  = 20;
    const MIN_PASSWORD  = 6;
    const MAX_PASSWORD  = 12;
    const MAX_IMG_SIZE  = 5000000000;


    /**
     * Validates register/edit profile forms fields except email.
     * This method does not check if the username is unique.
     *
     * @param User $user
     * @param $passwd2
     * @return bool
     */
    public function validateUserEditableFields(User $user, $passwd2) {

        if (!filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) return false;

        $length = strlen($user->getUsername());
        if ($length == 0 || $length > Validator::MAX_USERNAME
            || !preg_match('/[a-zA-Z0-9]+$/', $user->getUsername())) return false;

        $passwdLength = strlen($user->getPassword());
        $uppercase  = preg_match('@[A-Z]@', $user->getPassword());
        $lowercase  = preg_match('@[a-z]@', $user->getPassword());
        $number     = preg_match('@[0-9]@', $user->getPassword());

        if ($passwdLength < Validator::MIN_PASSWORD || $passwdLength > Validator::MAX_PASSWORD
            || !$uppercase || !$lowercase || !$number)    return false;

        if ($user->getPassword() !== $passwd2)          return false;

        if (!$this->validateDate($user->getBirthday())) return false;

        return true;
    }

    /**
     * Verify that the user data is completely correct
     * @param User $user
     * @param $passwd2
     * @return bool
     */
    public function validateNewUser(User $user, $passwd2) {

        if (!$this->validateUserEditableFields($user, $passwd2)) return false;

        $db = Database::getInstance("pwgram");
        $pdoUser = new PdoUserRepository($db);


        if (!$pdoUser->validateUnique($user->getUsername(), $user->getEmail())) return false;

        return true;
    }

    /**
     * Validates all editable fields an also checks that, if the user has changed its username,
     * this new username already does nor exists.
     *
     * @param User $currentUserState    The current user data, without the update.
     * @param User $userUpdate          The current user with data modifications.
     * @param string $passwd2           The password confirmation.
     *
     * @return bool                     true if the new data is correct, false if not.
     */
    public function validateUserUpdate(User $currentUserState, User $userUpdate, $passwd2) {

        if (!$this->validateUserEditableFields($userUpdate, $passwd2)) return false;

        if ($currentUserState->getUsername() !== $userUpdate->getUsername()) {

            $db = Database::getInstance("pwgram");
            $pdoUser = new PdoUserRepository($db);

            if (!$pdoUser->validateUnique($userUpdate->getUsername())) return false;
        }

        return true;
    }

    // not checked
    public function validateNewUserExtra(User $user, $passwd2) {
        $response = array('STATUS' => 'OK', 'username' => 'OK', 'email' => 'OK', 'message' => '');

        if (filter_var(!$user->getUsername(), FILTER_VALIDATE_EMAIL)) {

            $response['STATUS'] = $response['email'] = 'KO';
            return json_encode($response);
        }

        $length = strlen($user->getUsername());
        if ($length == 0 || $length > Validator::MAX_USERNAME) {

            $response['STATUS']  = 'KO';
            $response['message'] = "User name must be between 1 and 20 characters.";
            return json_encode($response);
        }
        if ($user->getPassword() !== $passwd2) {

            $response['STATUS'] = 'KO';
            $response['message'] = "Passwords does not match";
            return json_encode($response);
        }

        $passwdLength = strlen($user->getPassword());
        if ($passwdLength < Validator::MIN_PASSWORD || $passwdLength > Validator::MAX_PASSWORD) {

            $response['message'] = "Password length must be between 6 and 12 characters";
            $response['STATUS'] = 'KO';
            return json_encode($response);
        }
        if (!$this->validateDate($user->getBirthday())) {

            $response['message'] = "Invalid date format, it must be YYYY-mm-dd.";
            $response['STATUS'] = 'KO';
        }

        $db = Database::getInstance("pwgram");
        $pdoUser = new PdoUserRepository($db);
        $response = $pdoUser->validateUniqueExtra($user->getUsername(), $user->getEmail());

        return $response;
    }

    // checked
    function validateDate($date){

        $today  = new DateTime();
        $format = 'Y-m-d';
        $today  = $today->format($format);

        $dateFormatted = DateTime::createFromFormat($format, $date);

        return $date && $dateFormatted->format($format) == $date && $date <= $today;
    }

    function validateProfileImage($size, $format) {
        //Size lees than 5M and forman png or jpg
        if ($size < Validator::MAX_IMG_SIZE && ($format == "jpg" || $format == "jpeg")) {
            return true;
        }
        return false;
    }

}