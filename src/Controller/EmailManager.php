<?php
namespace pwgram\Controller;
/**
 * Class EmailManager
 * The main function of this class is the mail management and sending
 * @package pwgram\Controller
 */
class EmailManager {
    const EMAIL_SENDER = "pwgramgrup17@gmail.com";

    private function emailBodyConstruct($idUser) {
        $activationPath = "grup17.com/dovalidation/" . $idUser;


        $body = '<html> 
                <head> 
                   <title>Prueba de correo</title> 
                </head> 
                <body> 
                <h1>Bienvenid@ a PWGram! </h1> 
                <p>Para comenzar a utilitzar tu cuenta <b>verifícala</b> accediendo al siguiente
                ';
        $body .= "<a href='" . $activationPath ."'>enlace</a></p>
            <p>Gracias por escogernos!</p>
            <p><i>El equipo de PWGram</i></p>
            </body> 
            </html> 
            ";

        return $body;
    }

    public function sendEmail($emailUser, $idUser) {
        $affair = "Bienvenid@ a PWGram";
        $headers = "From: " . EmailManager::EMAIL_SENDER . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        $result = mail($emailUser, $affair, $this->emailBodyConstruct($idUser), $headers);

        
        return $result;
    }
}