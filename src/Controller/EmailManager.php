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
        $activationPath = "pwgram.com/activation/" . $idUser;

        $body = ' 
                <html> 
                <head> 
                   <title>Prueba de correo</title> 
                </head> 
                <body> 
                <h1>Bienvenid@ a PWGram! </h1> 
                <p>Para comenzar a utilitzar tu cuenta <b>verifícala</b> accediendo al siguiente
                enlace: <a href="<?php echo $activationPath; ?>"></a></p> 
                <p>Gracias por escogernos!</p>
                <p><i>El equipo de PWGram</i></p>
                </body> 
                </html> 
                ';

        return $body;
    }

    public function sendEmail($emailUser, $idUser) {
        $affair = "Bienvenid@ a PWGram";
        $result = mail("carlaurreablazquez@gmail.com", $affair, $this->emailBodyConstruct($idUser));
        return $result;
    }
}