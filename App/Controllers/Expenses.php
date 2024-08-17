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
    

    if (!empty($_POST['expense_category_assigned_to_user_id']) && !empty($_POST['payment_method_assigned_to_user_id'])) {

      $expense -> save();

      Flash::addMessage('Expense added successfully.');

      $this -> redirect('/expenses/show');

    } else {

      Flash::addMessage("At least one of the following fields has not been completed:
      - category  
      - payment method
      Go to Settings and add an option or press 'Restore all categories'", Flash::WARNING);

      $this -> redirect('/expenses/show');

    }

  }

  public function showAction() {

    $user = Auth::getUser();
    $expenseCategories = $user -> getCategories($user->id, 'expense');
    $paymentMethods = $user -> getCategories($user->id, 'payment');
    $today = date('Y-m-d');

    View::renderTemplate('Expenses/show.html', [
      'user' => $this -> user,
      'expenseCategories' => $expenseCategories,
      'paymentMethods' => $paymentMethods,
      'today' => $today
    ]);

  }

  public function getAction() {

    $expenses = Expense::getExpensesByUserId();
    echo $expenses;

  }

  public function editAction() {

    $expenseId = $_POST['id'];
    $expense = Expense::findById($expenseId);

    if ($expense) {

      $expense->expense_category_assigned_to_user_id = $_POST['expense_category_assigned_to_user_id'];
      $expense->payment_method_assigned_to_user_id = $_POST['payment_method_assigned_to_user_id'];
      $expense->amount = $_POST['amount'];
      $expense->date_of_expense = $_POST['date_of_expense'];
      $expense->expense_comment = $_POST['expense_comment'];

      if ($expense->editExpense($_POST)) {
        Flash::addMessage('Changes saved');
        $this->redirect('/expenses/show');
      } else {
        Flash::addMessage('Something went wrong. Please try again.', Flash::WARNING);
        $this->redirect('/expenses/show');
      }

    } else {

      Flash::addMessage('Expense record not found.', Flash::WARNING);
      $this->redirect('/expenses/show');

    }

  }

  public function deleteAction() {

    $ExpenseId = $_POST['id'];

    $expense = Expense::findById($ExpenseId);

    if ($expense) {

      if($expense->deleteExpense($_POST)) {

        Flash::addMessage('Expense deleted successfully.');
        $this->redirect('/expenses/show');

      } else {

        Flash::addMessage('Something went wrong. Please try again.', Flash::WARNING);
        $this->redirect('/expenses/show');

      }

    } else {

      Flash::addMessage('Expense record not found.', Flash::WARNING);
      $this->redirect('/expenses/show');

    }
  }

  public function fetchAction() {

    $expenses_chart_data = Expense::getExpensesDataForCharts();
    echo $expenses_chart_data;

  }


}