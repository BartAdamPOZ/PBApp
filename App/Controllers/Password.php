<?php

namespace App\Controllers;

use \Core\View;
use \App\Models\User;
use \App\Flash;

class Password extends \Core\Controller {

  public function forgotAction() {

    View::renderTemplate('Password/forgot.html');

  }

  public function requestResetAction() {

    User::sendPasswordReset($_POST['email']);

    $this -> redirect('/password/show-confirmation-message');

    //View::renderTemplate('Password/reset_requested.html');

  }

  public function resetAction() {

    $token = $this -> route_params['token'];

    $user = $this -> getUserOrExit($token);

    View::renderTemplate('Password/reset.html', [
      'token' => $token
    ]);
        
  }

  public function resetPasswordAction() {

    $token = $_POST['token'];

    $user = $this -> getUserOrExit($token);    

    if ($user -> resetPassword($_POST['password'])) {

      View::renderTemplate('Password/reset_success.html');

    } else {

      View::renderTemplate('Password/reset.html', [
        'token' => $token,
        'user' => $user
      ]);

    }

  }

  protected function getUserOrExit($token) {

    $user = User::findByPasswordReset($token);

    if ($user) {

      return $user;

    } else {
      
      View::renderTemplate("Password/token_expired.html");
      exit;
    }

  }

  public function showConfirmationMessageAction() {
    Flash::addMessage('Message has been sent');

    View::renderTemplate('Password/reset_requested.html');

  }


}