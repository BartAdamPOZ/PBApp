<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;

/**
 * Settings controller
 *
 * PHP version 7.0
 */
class Settings extends Authenticated
{
    public function showAction()
    {
        View::renderTemplate('Settings/show.html');
    }
}