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

}