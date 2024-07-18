<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;

class Incomes extends Authenticated 
{
  public function showAction()
  {
    View::renderTemplate('Incomes/show.html');
  }
}