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

    // not checked
    public function validateNewUser(User $user, $passwd2) {

        if (!filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) return false;

        $length = strlen($user->getUsername());
        if ($length == 0 || $length > 20)               return false;

        $passwdLength = strlen($user->getPassword());
        if ($passwdLength < 6 || $passwdLength > 12)    return false;

        if ($user->getPassword() !== $passwd2)          return false;

        if (!$this->validateDate($user->getBirthday())) return false;

        $db = Database::getInstance("pwgram", "pwgrammer", "secret");
        $pdoUser = new PdoUserRepository($db);

        if (!$pdoUser->validateUnique($user->getUsername(), $user->getEmail())) return false;

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
        if ($length == 0 || $length > 20) {

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
        if ($passwdLength < 6 || $passwdLength > 12) {

            $response['message'] = "Password length must be between 6 and 12 characters";
            $response['STATUS'] = 'KO';
            return json_encode($response);
        }
        if (!$this->validateDate($user->getBirthday())) {

            $response['message'] = "Invalid date format, it must be YYYY-mm-dd.";
            $response['STATUS'] = 'KO';
        }

        $db = Database::getInstance("pwgram", "pwgrammer", "secret");
        $pdoUser = new PdoUserRepository($db);
        $response = $pdoUser->validateUniqueExtra($user->getUsername(), $user->getEmail());

        return $response;
    }

    // checked
    function validateDate($date, $format = 'Y-m-d')
    {
        $today  = new DateTime();
        $today  = $today->format($format);

        $dateFormatted = DateTime::createFromFormat($format, $date);

        return $date && $dateFormatted->format($format) == $date && $date <= $today;
    }

}