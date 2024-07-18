<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;

class Expenses extends Authenticated 
{
  public function showAction()
  {
    View::renderTemplate('Expenses/show.html');
  }
}