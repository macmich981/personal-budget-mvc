<?php

namespace App\Controllers;

use \Core\View;
use App\Models\ExpenseModel;

class Balance extends Authenticated {

    public function indexAction() {
        if (!empty($_GET['period'])) {
            $period = $_GET['period'];
        } else {
            $period = 'currentMonth';
        }
        
        $expenses = ExpenseModel::getExpensesSumSortedByCategory($period);

        View::renderTemplate('Balance/index.html', [
            'period' => $period,
            'expenses' => $expenses
        ]);
    }
    
}