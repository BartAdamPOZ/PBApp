<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Flash;
use \App\Models\Income;

class Incomes extends Authenticated
{

  public $user;
  public $income;


  public function addAction() {

    $income = new Income($_POST);
    $today = date('Y-m-d');

    if($income -> save() && !empty($_POST['income_category_assigned_to_user_id'])) {

      Flash::addMessage('Income added successfully.');

      $this -> redirect('/incomes/show');

    } else {

      Flash::addMessage("No category selected! Go to Settings and add one or press 'Restore all categories'.", Flash::WARNING);

      View::renderTemplate('/Incomes/show.html', [
          'amount' => $_POST['amount'],
          'date_of_income' => $_POST['date_of_income'],
          'income_comment' => $_POST['income_comment'],
          'today' => $today
      ]);

    }
  }

  public function editAction() {
    // Pobieramy ID dochodu z danych przesłanych przez formularz
    $incomeId = $_POST['id'];

    // Pobieramy istniejący rekord dochodu z bazy danych
    $income = Income::findById($incomeId);

    if ($income) {
        // Aktualizujemy właściwości obiektu Income na podstawie danych z formularza
        $income->income_category_assigned_to_user_id = $_POST['income_category_assigned_to_user_id'];
        $income->amount = $_POST['amount'];
        $income->date_of_income = $_POST['date_of_income'];
        $income->income_comment = $_POST['income_comment'];

        // Zapisujemy zmiany
        if ($income->editIncome($_POST)) {
            Flash::addMessage('Changes saved');
            $this->redirect('/incomes/show');
        } else {
            Flash::addMessage('Something went wrong. Please try again.', Flash::WARNING);
            $this->redirect('/incomes/show');
        }
    } else {
        // Obsługa sytuacji, gdy rekord dochodu nie został znaleziony
        Flash::addMessage('Income record not found.', Flash::WARNING);
        $this->redirect('/incomes/show');
    }
  }


  public function showAction()
  {

    $user = Auth::getUser();
    $incomeCategories = $user -> getCategories($user->id, 'income');
    $incomes = Income::getIncomesByUserId();
    $today = date('Y-m-d');


    View::renderTemplate('Incomes/show.html', [
      'user' => $this -> user,
      //'incomes' => $incomes,
      'incomeCategories' => $incomeCategories,
      'today' => $today
    ]);

  }

  public function getAction() {
    $user = Auth::getUser();
    $incomes = Income::getIncomesByUserId();
    echo $incomes;
  }


}