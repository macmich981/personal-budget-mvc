<?php

namespace App;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Config;
use App\Flash;

class Mail {

    public static function send($to, $subject, $text, $html) {
        
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->isSMTP();                                            
            $mail->Host       = Config::SMTP_HOST;                     
            $mail->SMTPAuth   = true;                                  
            $mail->Username   = Config::SMTP_USER;                     
            $mail->Password   = Config::SMTP_PASS;                      
            $mail->SMTPSecure = 'ssl';                                  
            $mail->Port       = 465;                                    

            //Recipients
            $mail->setFrom(Config::SMTP_USER);
            $mail->addAddress($to);     

            //Content
            $mail->isHTML(true);                                  
            $mail->Subject = $subject;
            $mail->Body    = $html;
            $mail->AltBody = $text;

            $mail->send();
            Flash::addmessage('Wiadomość została wysłana', FLASH::INFO);
        } catch (Exception $e) {
            Flash::addmessage("Wiadomość nie mogła zostać wysłana. Błąd: {$mail->ErrorInfo}", FLASH::WARNING);
        }
    }
}