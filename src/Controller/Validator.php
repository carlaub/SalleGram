<?php


namespace pwgram\Controller;
use pwgram\lib\Database\Database;
use pwgram\Model\AppFormatDate;
use pwgram\Model\Entity\Error;
use pwgram\Model\Entity\FormError;
use pwgram\Model\Entity\User;
use pwgram\Model\Repository\PdoUserRepository;
use \DateTime;
use Silex\Application;

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
    const MAX_IMG_SIZE  = 50000000000;


    /**
     * Validates register/edit profile forms fields except email.
     * This method does not check if the username is unique.
     *
     * @param User $user
     * @param $passwd2
     * @return bool
     */
    public function validateUserEditableFields(User $user, $passwd2, $errors) {


        if (!filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
            $errors->setEmailError(true);
        } else {
            $errors->setEmailError(false);
        }


        $length = strlen($user->getUsername());
        if ($length == 0 || $length > Validator::MAX_USERNAME
            || !preg_match('/^[a-zA-Z0-9]*$/', $user->getUsername())) {
            $errors->setUsernameError(true);
        }else {
            $errors->setUsernameError(false);
        }

        $passwdLength = strlen($user->getPassword());
        $uppercase  = preg_match('@[A-Z]@', $user->getPassword());
        $lowercase  = preg_match('@[a-z]@', $user->getPassword());
        $number     = preg_match('@[0-9]@', $user->getPassword());

        if ($passwdLength < Validator::MIN_PASSWORD || $passwdLength > Validator::MAX_PASSWORD
            || !$uppercase || !$lowercase || !$number) {
            $errors->setPasswordError(true);
        } else {
            $errors->setPasswordError(false);
        }

        if ($user->getPassword() !== $passwd2) {
            $errors->setConfirmPasswordError(true);
        } else {
            $errors->setConfirmPasswordError(false);
        }
        if (!$this->validateDate($user->getBirthday())) {
            $errors->setDateError(true);
        } else{
            $errors->setDateError(false);
        }
        return true;
    }

    /**
     * Verify that the user data is completely correct
     * @param User $user
     * @param $passwd2
     * @return bool
     */
    public function validateNewUser(Application $app, User $user, $passwd2) {

        $errors = new FormError();

        $this->validateUserEditableFields($user, $passwd2, $errors);

        $db = Database::getInstance("pwgram");
        $pdoUser = new PdoUserRepository($db);


        if (!$pdoUser->validateUnique($app, $user->getUsername(), $user->getEmail())){
            $errors->setUsernameRegisteredError(true);
        } else {
            $errors->setUsernameRegisteredError(false);
        }

        //return true;
        return $errors;
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
    public function validateUserUpdate(Application $app, User $currentUserState, User $userUpdate, $passwd2) {


        $errors = new FormError();

        $this->validateUserEditableFields($userUpdate, $passwd2, $errors);
        if ($currentUserState->getUsername() !== $userUpdate->getUsername()) {

            $db = Database::getInstance("pwgram");
            $pdoUser = new PdoUserRepository($db);

            if (!$pdoUser->validateUnique($app, $userUpdate->getUsername())) {
                $errors->setUsernameRegisteredError(true);
            } else {
                $errors->setUsernameRegisteredError(false);
            }
        }

        return $errors;
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

        $today  = AppFormatDate::today();

        $dateFormatted = DateTime::createFromFormat('Y-m-d', $date);

        return $date && $dateFormatted->format('Y-m-d') == $date && $date <= $today;
    }

    function validateImage($size, $format, $error) {
        //Size lees than 5M and forman png or jpg
//        if ($size < Validator::MAX_IMG_SIZE && ($format == "jpg" || $format == "jpeg" || $format == "png")) {
        if ((strcasecmp($format,"jpg") == 0 || $format == "jpeg" || $format == "png")) {

        $error->setImageError(false);
            return true;
        }
        $error->setImageError(true);
        return false;
    }

    /**
     * @param $title
     * @param $image
     */
    function validateUploadImage($title, $image, $errors) {
        if ($errors == null) $errors = new FormError();

        if($title == null ) {
            $errors->setTitleImageError(true);
        } else {
            $errors->setTitleImageError(true);
        }

        if ($title != null && $image != null
            && $this->validateImage($image->getClientSize(), $image->getClientOriginalExtension(), $errors)) {
            return true;
        } else {
            return false;
        }
    }

    function validateEditImage($title, $errors) {
        if ($errors == null) $errors = new FormError();

        if($title == null ) {
            $errors->setTitleImageError(true);
            return false;
        } else {
            $errors->setTitleImageError(true);
        }
        return true;
    }

    function haveErrors($errors) {

       return $errors->haveErrors();
    }
}