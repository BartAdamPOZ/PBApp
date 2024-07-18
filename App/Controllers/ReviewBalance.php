<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;

class ReviewBalance extends Authenticated 
{
  public function showAction()
  {
      View::renderTemplate('ReviewBalance/show.html');
  }

}