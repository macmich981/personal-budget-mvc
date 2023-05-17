<?php

namespace App\Controllers;

use \Core\View;
use App\Date;
use App\Flash;
use App\Models\IncomeModel;

class Income extends Authenticated {

    public function newAction() {
        View::renderTemplate('Income/new.html', [
            'date' => Date::getCurrentDate(),
            'categories' => IncomeModel::getIncomeCategories()
        ]);
    }

    public function createAction() {
        $income = new IncomeModel($_POST);
        
        if ($income->save()) {
            FLASH::addMessage('Przychód został pomyślnie zapisany');
            $this->redirect('/income/new');
        } else {
            View::renderTemplate('Income/new.html', [
                'income' => $income,
                'date' => Date::getCurrentDate(),
                'categories' => IncomeModel::getIncomeCategories()
            ]);
        }
    }

    public function updateAction() {
        $income = new IncomeModel($_POST);

        if ($income->update()) {
            FLASH::addMessage('Przychód został zaktualizowany.');
            $this->redirect('/balance/index');
        } else {
            FLASH::addMessage('Błąd zapisu do bazy danych.', FLASH::WARNING);
            $this->redirect('/balance/index');
        }
    }

    public function deleteAction() {
        if (IncomeModel::delete($_POST['incomeId'])) {
            FLASH::addMessage('Przychód został zaktualizowany.');
            $this->redirect('/balance/index');
        } else {
            FLASH::addMessage('Błąd: nie udało się usunąć przychodu.', FLASH::WARNING);
            $this->redirect('/balance/index');
        }
    }

}