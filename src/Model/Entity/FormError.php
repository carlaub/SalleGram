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

    private $stringConfirmPasswordError = "Las contraseñas no coinciden.";

    private $stringImageError = "La imagen escogida no es válida";

    private $stringUsernameRegisteredError = "El nombre de usuario o email ya se enceuntra registrado en PWGram!";

    private $stringUserOrPasswordError = "El usuario o la contraseña no coinciden";

    private $stringTitleImageError = "Título no válido.";

    private $usernameError = false;

    private $emailError = false;

    private $dateError = false;

    private $passwordError = false;

    private $confirmPasswordError = false;

    private $imageError = false;

    private $usernameRegisteredError = false;

    private $userOrPasswordError = false;

    private $titleImageError = false;


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
     * @return string
     */
    public function getStringEmailError(): string
    {
        return $this->stringEmailError;
    }

    /**
     * @return string
     */
    public function getStringDateError(): string
    {
        return $this->stringDateError;
    }

    /**
     * @return string
     */
    public function getStringPasswordError(): string
    {
        return $this->stringPasswordError;
    }



    /**
     * @return string
     */
    public function getStringImageError(): string
    {
        return $this->stringImageError;
    }

    /**
     * @param bool $confirmPasswordError
     */
    public function setConfirmPasswordError(bool $confirmPasswordError)
    {
        $this->confirmPasswordError = $confirmPasswordError;
    }


    /**
     * @return bool
     */
    public function isConfirmPasswordError(): bool
    {
        return $this->confirmPasswordError;
    }

    /**
     * @return bool
     */
    public function isUsernameRegisteredError(): bool
    {
        return $this->usernameRegisteredError;
    }


    /**
     * @return string
     */
    public function getStringUsernameRegisteredError(): string
    {
        return $this->stringUsernameRegisteredError;
    }


    /**
     * @return bool
     */
    public function isUserOrPasswordError(): bool
    {
        return $this->userOrPasswordError;
    }

    /**
     * @param bool $userOrPasswordError
     */
    public function setUserOrPasswordError(bool $userOrPasswordError)
    {
        $this->userOrPasswordError = $userOrPasswordError;
    }

    /**
     * @return string
     */
    public function getStringUserOrPasswordError(): string
    {
        return $this->stringUserOrPasswordError;
    }

    /**
     * @return bool
     */
    public function isTitleImageError(): bool
    {
        return $this->titleImageError;
    }

    /**
     * @param bool $titleImageError
     */
    public function setTitleImageError(bool $titleImageError)
    {
        $this->titleImageError = $titleImageError;
    }

    /**
     * @return string
     */
    public function getStringTitleImageError(): string
    {
        return $this->stringTitleImageError;
    }

    public function setUsernameRegisteredError(bool $usernameRegisteredError) {
        $this->usernameRegisteredError = $usernameRegisteredError;

    }




    public function haveErrors() {
        return ($this->dateError || $this->emailError || $this->usernameError
            || $this->imageError || $this->passwordError || $this->confirmPasswordError
        || $this->usernameRegisteredError);
    }



}