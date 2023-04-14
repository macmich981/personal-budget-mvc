<?php

namespace App\Controllers;

use \Core\View;
use App\Models\User;
use \App\Auth;

class Login extends \Core\Controller {

    public function newAction() {
        View::renderTemplate('Login/new.html');
    }

    public function createAction() {
        $user = User::authenticate($_POST['email'], $_POST['password']);

        if ($user) {
            Auth::login($user);
            $this->redirect(Auth::getReturnToPage());
        } else {
            View::renderTemplate('Login/new.html', [
                'email' => $_POST['email']
            ]);
        }
    }

    public function destroyAction() {
        Auth::logout();
        $this->redirect('/login');
    }

}