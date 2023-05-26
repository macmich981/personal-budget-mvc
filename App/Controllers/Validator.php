<?php

namespace App\Controllers;

use \App\Models\ExpenseModel;
use \App\Models\IncomeModel;
use \App\Models\User;

class Validator extends \Core\Controller {

    public function validatePaymentMethodAction() {
        $is_valid = ExpenseModel::paymentMethodExists($_GET['payment']);
        
        header('Content-Type: application/json');
        echo json_encode($is_valid);
    }
    
    public function validateExpenseCategoryAction() {
        $is_valid = ExpenseModel::categoryExists($_GET['category']);

        header('Content-Type: application/json');
        echo json_encode($is_valid);
    }

    public function validateIncomeCategoryAction() {
        $is_valid = IncomeModel::categoryExists($_GET['category']);

        header('Content-Type: application/json');
        echo json_encode($is_valid);
    }
    
    public function validateOldPasswordAction() {
        $is_valid = User::checkOldPassword($_GET['old-password']);

        header('Content-Type: application/json');
        echo json_encode($is_valid);
    }

    public function paymentMethodExistAction() {
        $is_exist = !ExpenseModel::paymentMethodExists($_GET['payment']);
        
        header('Content-Type: application/json');
        echo json_encode($is_exist);
    }

    public function incomeCategoryExistAction() {
        $is_exist = !IncomeModel::categoryExists($_GET['inputIncomeCategory']);
        
        header('Content-Type: application/json');
        echo json_encode($is_exist);
    }
}