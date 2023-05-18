<?php

namespace App\Controllers;

use \Core\View;
use App\Date;
use App\Models\ExpenseModel;
use App\Flash;

class Expense extends Authenticated {

    public function newAction() {
        View::renderTemplate('Expense/new.html', [
            'date' => Date::getCurrentDate(),
            'paymentMethods' => ExpenseModel::getPaymentMethods(),
            'categories' => ExpenseModel::getExpenseCategories()
        ]);
    }

    public function createAction() {
        $expense = new ExpenseModel($_POST);
        
        if ($expense->save()) {
            FLASH::addMessage('Wydatek został pomyślnie zapisany');
            $this->redirect('/expense/new');
        } else {
            View::renderTemplate('Expense/new.html', [
                'expense' => $expense,
                'date' => Date::getCurrentDate(),
                'paymentMethods' => ExpenseModel::getPaymentMethods(),
                'categories' => ExpenseModel::getExpenseCategories()
            ]);
        }
    }

    public function updateAction() {
        $expense = new ExpenseModel($_POST);

        if ($expense->update()) {
            FLASH::addMessage('Wydatek został zaktualizowany.');
            $this->redirect('/balance/index');
        } else {
            FLASH::addMessage('Błąd zapisu do bazy danych.', FLASH::WARNING);
            $this->redirect('/balance/index');
        }
    }

    public function deleteAction() {
        if (ExpenseModel::delete($_POST['expenseId'])) {
            FLASH::addMessage('Wydatek został usunięty.');
            $this->redirect('/balance/index');
        } else {
            FLASH::addMessage('Błąd: nie udało się usunąć wydatku.', FLASH::WARNING);
            $this->redirect('/balance/index');
        }
    }

}