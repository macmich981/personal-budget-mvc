<?php

namespace App\Controllers;

use \Core\View;
use App\Date;
use App\Flash;

class Income extends Authenticated {

    public function newAction() {
        View::renderTemplate('Income/new.html', [
            'date' => Date::getCurrentDate()
        ]);
    }

}