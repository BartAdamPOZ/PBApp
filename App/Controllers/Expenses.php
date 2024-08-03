<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Flash;
use \App\Models\Expense;

class Expenses extends Authenticated 
{

  public $user;
  public $expense;


  public function addAction() {

    $expense = new Expense($_POST);
    $today = date('Y-m-d');

    if ($expense -> save() && !empty($_POST['expense_category_assigned_to_user']) && !empty($_POST['payment_method_assigned_to_user_id'])) {

      Flash::addMessage('Expense added successfully.');

      $this -> redirect('/expenses/show');

    } else {

      Flash::addMessage("At least one of the following fields has not been completed:
      - category  
      - payment method
      Go to Settings and add an option or press 'Restore all categories'", Flash::WARNING);

      View::renderTemplate('/Expenses/show.html', [
        'amount' => $_POST['amount'],
        'date_of_expense' => $_POST['date_of_expense'],
        'expense_comment' => $_POST['expense_comment'],
        'today' => $_POST['today'],

      ]);

    }

  }

  public function showAction()
  {
    View::renderTemplate('Expenses/show.html');
  }
}