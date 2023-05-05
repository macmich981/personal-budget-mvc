<?php

namespace App\Controllers;

use \Core\View;

class Balance extends Authenticated {

    public function indexAction() {
        View::renderTemplate('Balance/index.html');
    }
    
}