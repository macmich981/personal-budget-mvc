<?php

namespace App\Controllers;

use \Core\View;
use App\Date;
use App\Models\ExpenseModel;

class Expense extends Authenticated {

    public function newAction() {
        View::renderTemplate('Expense/new.html', [
            'date' => Date::getCurrentDate()
        ]);
    }

    public function createAction() {
        $expense = new ExpenseModel($_POST);
        
        $expense->save();
        View::renderTemplate('Expense/new.html', [
            'expense' => $expense,
            'date' => Date::getCurrentDate()
        ]);
    }

}