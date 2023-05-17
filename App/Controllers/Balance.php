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
            $detailedIncomes = IncomeModel::getDetailedIncomes($period, $start_date, $end_date);
            $expenses = ExpenseModel::getExpensesSumSortedByCategory($period, $start_date, $end_date);
            $detailedExpenses = ExpenseModel::getDetailedExpenses($period, $start_date, $end_date);
        } else {
            $incomes = IncomeModel::getIncomesSumSortedByCategory($period);
            $detailedIncomes = IncomeModel::getDetailedIncomes($period);
            $expenses = ExpenseModel::getExpensesSumSortedByCategory($period);
            $detailedExpenses = ExpenseModel::getDetailedExpenses($period);
        }

        $incomeCategories = IncomeModel::getIncomeCategories();
        $expenseCategories = ExpenseModel::getExpenseCategories();
        $paymentMethods = ExpenseModel::getPaymentMethods();

        if (!empty($start_date) && !empty($end_date)) {
            View::renderTemplate('Balance/index.html', [
                'period' => $period,
                'incomes' => $incomes,
                'detailedIncomes' => $detailedIncomes,
                'expenses' => $expenses,
                'detailedExpenses' => $detailedExpenses,
                'incomeCategories' => $incomeCategories,
                'paymentMethods' => $paymentMethods,
                'startdate' => $start_date,
                'enddate' => $end_date
            ]);
        } else {
            View::renderTemplate('Balance/index.html', [
                'period' => $period,
                'incomes' => $incomes,
                'detailedIncomes' => $detailedIncomes,
                'expenses' => $expenses,
                'paymentMethods' => $paymentMethods,
                'detailedExpenses' => $detailedExpenses,
                'incomeCategories' => $incomeCategories,
                'expenseCategories' => $expenseCategories
            ]);
        }
    }
    
}