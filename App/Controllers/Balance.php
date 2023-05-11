<?php

namespace App\Controllers;

use \Core\View;
use App\Models\IncomeModel;
use App\Models\ExpenseModel;

class Balance extends Authenticated {

    public function indexAction() {
        $period = 'currentMonth';
        
        if (!empty($_GET['period'])) {
            $period = $_GET['period'];
        } else {
            if (!empty($_GET['start-date'])) {
                $start_date = $_GET['start-date'];
                $period = 'custom';
            }
            if (!empty($_GET['end-date'])) {
                $end_date = $_GET['end-date'];
            }
        }

        if (isset($start_date) && isset($end_date)) {
            $incomes = IncomeModel::getIncomesSumSortedByCategory($period, $start_date, $end_date);
            $expenses = ExpenseModel::getExpensesSumSortedByCategory($period, $start_date, $end_date);
        } else {
            $incomes = IncomeModel::getIncomesSumSortedByCategory($period);
            $expenses = ExpenseModel::getExpensesSumSortedByCategory($period);
        }

        if (!empty($start_date) && !empty($end_date)) {
            View::renderTemplate('Balance/index.html', [
                'period' => $period,
                'incomes' => $incomes,
                'expenses' => $expenses,
                'startdate' => $start_date,
                'enddate' => $end_date
            ]);
        } else {
            View::renderTemplate('Balance/index.html', [
                'period' => $period,
                'incomes' => $incomes,
                'expenses' => $expenses
            ]);
        }
    }
    
}