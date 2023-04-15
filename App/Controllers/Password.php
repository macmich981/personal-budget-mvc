<?php

namespace App\Controllers;

use Core\View;
use App\Models\User;
use App\Flash;

class Password extends \Core\Controller {

    public function forgotAction() {
        View::renderTemplate('Password/forgot.html');
    }

    public function requestResetAction() {
        if (User::sendPasswordReset($_POST['email'])) {
            $message = 'W przesłanej wiadomości kliknij w link, aby zmienić hasło';
        } else {
            $message = 'Nie można znaleźć konta z takim adresem email';
        }
        $this->redirect('/password/show-password-message?message=' . $message);
    }

    public function showPasswordMessage() {
        $message = $_GET['message'];
        Flash::addMessage($message, FLASH::INFO);
        View::renderTemplate('Password/forgot.html');
    }

}