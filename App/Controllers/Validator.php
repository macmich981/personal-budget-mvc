<?php

namespace App\Controllers;

use \App\Models\ExpenseModel;

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
    
}