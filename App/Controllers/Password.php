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

    public function resetAction() {
        $token = $this->route_params['token'];
        $user = $this->getUserOrExit($token);
        
        View::renderTemplate('Password/reset.html', [
            'token' => $token
        ]);
    }

    public function resetPasswordAction() {
        $token = $_POST['token'];
        $user = $this->getUserOrExit($token);
        
        if ($user->resetPassword($_POST['password'], $_POST['password_confirmation'])) {
            Flash::addMessage('Zmiana hasła przeprowadzona pomyślnie');
            View::renderTemplate('Login/new.html');
        } else {
            View::renderTemplate('Password/reset.html', [
                'token' => $token,
                'user' => $user
            ]);
        }
    }

    protected function getUserOrExit($token) {
        $user = User::findByPasswordReset($token);

        if ($user) {
            return $user;
        } else {
            Flash::addMessage('Niepoprawny link lub termin ważności zmiany hasła wygasł. Spróbuj ponownie', FLASH::WARNING);
            View::renderTemplate('Password/forgot.html');
            exit;
        }
    }

}