<?php
/**
 * Created by PhpStorm.
 * User: carlaurrea
 * Date: 27/4/17
 * Time: 20:03
 */

namespace pwgram\Model\Entity;


class FormError
{
    private $stringUsernameError = "Nombre de usuario inválido.";

    private $stringEmailError = "Email inválido.";

    private $stringDateError = "Fecha incorrecta.";

    private $stringPasswordError = "Constraseña incorrecta.";

    private $stringImageError = "La imagen escogida no es válida";

    private $usernameError = false;

    private $emailError = false;

    private $dateError = false;

    private $passwordError = false;

    private $imageError = false;


    /**
     * @return mixed
     */
    public function getEmailError()
    {
        return $this->emailError;
    }

    /**
     * @param mixed $emailError
     */
    public function setEmailError($emailError)
    {
        $this->emailError = $emailError;
    }

    /**
     * @return mixed
     */
    public function getDateError()
    {
        return $this->dateError;
    }

    /**
     * @param mixed $dateError
     */
    public function setDateError($dateError)
    {
        $this->dateError = $dateError;
    }

    /**
     * @return mixed
     */
    public function getImageError()
    {
        return $this->imageError;
    }

    /**
     * @param mixed $imageError
     */
    public function setImageError($imageError)
    {
        $this->imageError = $imageError;
    }



    /**
     * @return mixed
     */
    public function getPasswordError()
    {
        return $this->passwordError;
    }

    /**
     * @param mixed $passwordError
     */
    public function setPasswordError($passwordError)
    {
        $this->passwordError = $passwordError;
    }


    /**
     * @return mixed
     */
    public function getUsernameError()
    {
        return $this->usernameError;
    }

    /**
     * @param mixed $usernameError
     */
    public function setUsernameError($usernameError)
    {
        $this->usernameError = $usernameError;
    }

    /**
     * @return string
     */
    public function getStringUsernameError(): string
    {
        return $this->stringUsernameError;
    }

    /**
     * @param string $stringUsernameError
     */
    public function setStringUsernameError(string $stringUsernameError)
    {
        $this->stringUsernameError = $stringUsernameError;
    }

    /**
     * @return string
     */
    public function getStringEmailError(): string
    {
        return $this->stringEmailError;
    }

    /**
     * @param string $stringEmailError
     */
    public function setStringEmailError(string $stringEmailError)
    {
        $this->stringEmailError = $stringEmailError;
    }

    /**
     * @return string
     */
    public function getStringDateError(): string
    {
        return $this->stringDateError;
    }

    /**
     * @param string $stringDateError
     */
    public function setStringDateError(string $stringDateError)
    {
        $this->stringDateError = $stringDateError;
    }

    /**
     * @return string
     */
    public function getStringPasswordError(): string
    {
        return $this->stringPasswordError;
    }

    /**
     * @param string $stringPassword
     */
    public function setStringPasswordError(string $stringPasswordError)
    {
        $this->stringPasswordError = $stringPasswordError;
    }

    /**
     * @return string
     */
    public function getStringImageError(): string
    {
        return $this->stringImageError;
    }

    /**
     * @param string $stringImageError
     */
    public function setStringImageError(string $stringImageError)
    {
        $this->stringImageError = $stringImageError;
    }

    public function haveErrors() {
        return ($this->dateError || $this->emailError || $this->usernameError
        || $this->imageError || $this->passwordError);
    }
}