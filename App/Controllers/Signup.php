<?php

namespace App\Controllers;

use \Core\View;
use App\Models\User;
use App\Flash;

class Signup extends \Core\Controller {

    public function newAction() {
        View::renderTemplate('Signup/new.html');
    }

    public function createAction() {
        $user = new User($_POST);
        
        if ($user->save()) {
            FLASH::addMessage('Rejestracja zakończona sukcesem. Proszę sprawdzić skrzynkę email, aby aktywować konto');
            $this->redirect('/login/new');
        } else {
            View::renderTemplate('Signup/new.html', [
                'user' => $user
            ]);
        }
    }

    public function successAction() {
        View::renderTemplate('Signup/success.html');
    }

    public function activateAction() {
        User::activate($this->route_params['token']);
        Flash::addMessage('Aktywacja konta zakończona.');
        $this->redirect('/login/new');
    }

}