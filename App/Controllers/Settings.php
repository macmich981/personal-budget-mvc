<?php

namespace App\Controllers;

use \Core\View;
use App\Auth;
use App\Flash;

class Settings extends Authenticated {

    public function indexAction() {
        View::renderTemplate('Settings/index.html');
    }

    public function changeEmailAction() {
        $user = Auth::getUser();
        $email = $_POST['email'];

        if ($user->changeEmail($email)) {
            FLASH::addMessage('Adres email został zmieniony.');
            $this->redirect('/settings/index');
        } else {
            FLASH::addMessage('Adres email już zajęty lub błąd zapisu do bazy danych. Proszę sprówbować ponownie.', FLASH::WARNING);
            $this->redirect('/settings/index');
        }
    }

}