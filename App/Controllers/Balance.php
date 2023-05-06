<?php

namespace App\Controllers;

use \Core\View;

class Balance extends Authenticated {

    public function indexAction() {
        if (!empty($_GET['period'])) {
            $period = $_GET['period'];
        } else {
            $period = 'currentMonth';
        }
        
        View::renderTemplate('Balance/index.html', [
            'period' => $period
        ]);
    }
    
}