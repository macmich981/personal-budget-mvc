<?php

namespace App\Controllers;

use \Core\View;
use App\Date;

class Expense extends Authenticated {

    public function newAction() {
        View::renderTemplate('Expense/new.html', [
            'date' => Date::getCurrentDate()
        ]);
    }

}