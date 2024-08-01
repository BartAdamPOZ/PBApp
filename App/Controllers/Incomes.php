<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Flash;
use \App\Models\Income;

class Incomes extends Authenticated
{

  public $user;


  public function addAction() {

    $income = new Income($_POST);

    if($income -> save()) {

      Flash::addMessage('Income added successfully.');

      $this -> redirect('/incomes/show');

    } else {

      Flash::addMessage('Something went wrong. Please try again.');

      View::renderTemplate('/Incomes/show.html', [
          'amount' => $_POST['amount'],
          'date_of_income' => $_POST['date_of_income'],
          'income_category_assigned_to_user_id' => $_POST['income_category_assigned_to_user_id'],
          'income_comment' => $_POST['income_comment']
      ]);

    }
  }

  public function showAction()
  {

    $user = Auth::getUser();
    $incomeCategories = $user -> getCategories($user->id, 'income');
    $today = date('Y-m-d');


    View::renderTemplate('Incomes/show.html', [
      'user' => $this -> user,
      'incomeCategories' => $incomeCategories,
      'today' => $today
    ]);

  }


}