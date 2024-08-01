<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;

class Review extends Authenticated 
{
  public function showAction()
  {
      View::renderTemplate('Review/show.html');
  }

}