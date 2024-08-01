<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Flash;

class Profile extends Authenticated {

  public $user;

  protected function before() {

    parent::before();

    $this -> user = Auth::getUser();

  }

  public function editAction() {

    View::renderTemplate('settings/show.html', [
      'user' => $this -> user
    ]);

  }

  public function updateAction() {

    if ($this -> user -> updateProfile($_POST)) {

      Flash::addMessage('Changes saved');

      $this -> redirect ('/settings/show');

    } else {

      View::renderTemplate('settings/show.html', [
        'user' => $this -> user
      ]);
    }

  }

}