<?php

namespace App\Controllers;

use \Core\View;

class Welcome extends Authenticated {

    public function indexAction() {
        View::renderTemplate('Welcome/index.html');
    }

}