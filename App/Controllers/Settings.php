<?php

namespace App\Controllers;

use \Core\View;
use App\Auth;
use App\Flash;
use App\Models\ExpenseModel;

class Settings extends Authenticated {

    public function indexAction() {
        View::renderTemplate('Settings/index.html', [
            'paymentMethods' => ExpenseModel::getPaymentMethodsCreatedByUser()
        ]);
    }

    public function changeEmailAction() {
        $user = Auth::getUser();
        $email = $_POST['email'];

        if ($user->changeEmail($email)) {
            FLASH::addMessage('Adres email został zmieniony.');
        } else {
            FLASH::addMessage('Adres email już zajęty lub błąd zapisu do bazy danych. Proszę sprówbować ponownie.', FLASH::WARNING);
        }
        $this->redirect('/settings/index');
    }

    public function changePasswordAction() {
        $user = Auth::getUser();
        $oldPassword = $_POST['old-password'];
        $password = $_POST['password'];
        $password_confirmation = $_POST['password_confirmation'];


        if ($user->changePassword($oldPassword, $password, $password_confirmation)) {
            FLASH::addMessage('Hasło zostało zmienione.');
        } else {
            FLASH::addMessage('Niewłaście hasło lub błąd zapisu do bazy danych. Proszę sprówbować ponownie.', FLASH::WARNING);
        }
        $this->redirect('/settings/index');
    }

    public function addPaymentMethodAction() {
        $method = $_POST['payment'];

        if (!empty($method)) {
            if (!ExpenseModel::findPaymentMethod($method)) {
                ExpenseModel::savePaymentMethodAssignedToUser($method);
                FLASH::addMessage('Metoda płatności została dodana.');
            } else {
                FLASH::addMessage('Metoda płatności już istnieje.', FLASH::WARNING);
            }
        }
        $this->redirect('/settings/index');
    }
    
    public function removePaymentMethodAction() {
        $method = $_POST['removePayment'];

        if (!empty($method)) {
            if (ExpenseModel::deletePaymentAssignedToUser($method)) {
                FLASH::addMessage('Wybrana metoda płatności została usunięta.');
            } else {
                FLASH::addMessage('Wybrana metoda nie mogła zostać usunięta.');
            }
        }
        $this->redirect('/settings/index');
    }
}