<?php

namespace App\Controllers;

use \Core\View;
use \App\Auth;
use \App\Flash;

/**
 * Settings controller
 *
 * PHP version 7.0
 */
class Settings extends Authenticated
{

    public $user;

    protected function before() {

        parent::before();

        $this -> user = Auth::getUser();

    }

    public function showAction() {

        $user = Auth::getUser();
        $incomeCategories = $user -> getCategories($user->id, 'income');
        $expenseCategories = $user -> getCategories($user->id, 'expense');
        $paymentMethods = $user -> getCategories($user->id, 'payment');


        View::renderTemplate('Settings/show.html', [
            'user' => $this -> user,
            'incomeCategories' => $incomeCategories,
            'expenseCategories' => $expenseCategories,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function updateAction() {

        if ($this -> user -> updateProfile($_POST)) {
    
          Flash::addMessage('Changes saved');
    
          $this -> redirect ('/settings/show');
    
        } else {

            Flash::addMessage('Something went wrong. Please try again.', Flash::WARNING);
    
        $this -> redirect ('/settings/show');
          
        }
    
      }

    public function restoreIncomeCategoriesAction() {

        $user = Auth::getUser();
        $user -> restoreOrCreateDefaultCategories($user->id, 'income');
        Flash::addMessage('Data has been restored to the default settings!');

        $this -> redirect('/settings/show');
    }

    public function restoreExpenseCategoriesAction() {

        $user = Auth::getUser();
        $user -> restoreOrCreateDefaultCategories($user->id, 'expense');
        Flash::addMessage('Data has been restored to the default settings!');

        $this -> redirect('/settings/show');
    }

    public function restorePaymentCategoriesAction() {

        $user = Auth::getUser();
        $user -> restoreOrCreateDefaultCategories($user->id, 'payment');
        Flash::addMessage('Data has been restored to the default settings!');

        $this -> redirect('/settings/show');
    }

    public function deleteIncomeCategoryAction() {

        if (!empty($_POST['incomeCategorySelected'])) {

            $user = Auth::getUser();
            $categoryName = $_POST['incomeCategorySelected'];

            $user -> deleteCategory($categoryName, $user->id, 'income');

            Flash::addMessage('Income category deleted successfully.');

        } else {
            Flash::addMessage("You don't have any categories to delete. Try to add a new category.", Flash::WARNING);
        }

        $this->redirect('/settings/show');

    }

    public function deleteExpenseCategoryAction() {

        if (!empty($_POST['expenseCategorySelected'])) {

            $user = Auth::getUser();
            $categoryName = $_POST['expenseCategorySelected'];

            $user -> deleteCategory($categoryName, $user->id, 'expense');

            Flash::addMessage('Expense category deleted successfully.');

        } else {
            Flash::addMessage("You don't have any categories to delete. Try to add a new category.", Flash::WARNING);
        }

        $this->redirect('/settings/show');

    }

    public function deletePaymentCategoryAction() {

        if (!empty($_POST['paymentCategorySelected'])) {

            $user = Auth::getUser();
            $categoryName = $_POST['paymentCategorySelected'];

            $user -> deleteCategory($categoryName, $user->id, 'payment');

            Flash::addMessage('Payment method deleted successfully.');

        } else {
            Flash::addMessage("You don't have any method to delete. Try to add a new category.", Flash::WARNING);
        }

        $this->redirect('/settings/show');

    }

    public function addIncomeCategoryAction() {

        if (isset($_POST['incomeCategoryName']) && !empty($_POST['incomeCategoryName'])) {
            $user = Auth::getUser();
            $categoryName = $_POST['incomeCategoryName'];

            if ($user->categoryExists($categoryName, $user->id, 'income')) {
                Flash::addMessage('Income category already exists.', Flash::WARNING);
            } else {
                if ($user->addCategory($categoryName, $user->id, 'income')) {
                    Flash::addMessage('Income category added successfully.');
                } else {
                    Flash::addMessage('Failed to add income category. Please try again.', Flash::WARNING);
                }
            }
        } else {
            Flash::addMessage('Income category name cannot be empty.', Flash::WARNING);
        }

        $this->redirect('/settings/show');
    }

    public function addExpenseCategoryAction() {

        if (isset($_POST['expenseCategoryName']) && !empty($_POST['expenseCategoryName'])) {
            $user = Auth::getUser();
            $categoryName = $_POST['expenseCategoryName'];

            if ($user->categoryExists($categoryName, $user->id, 'expense')) {
                Flash::addMessage('Expense category already exists.', Flash::WARNING);
            } else {
                if ($user->addCategory($categoryName, $user->id, 'expense')) {
                    Flash::addMessage('Expense category added successfully.');
                } else {
                    Flash::addMessage('Failed to add expense category. Please try again.', Flash::WARNING);
                }
            }
        } else {
            Flash::addMessage('Expense category name cannot be empty.', Flash::WARNING);
        }

        $this->redirect('/settings/show');
    }

    public function addPaymentCategoryAction() {

        if (isset($_POST['paymentCategoryName']) && !empty($_POST['paymentCategoryName'])) {
            $user = Auth::getUser();
            $categoryName = $_POST['paymentCategoryName'];

            if ($user->categoryExists($categoryName, $user->id, 'payment')) {
                Flash::addMessage('Payment method already exists.', Flash::WARNING);
            } else {
                if ($user->addCategory($categoryName, $user->id, 'payment')) {
                    Flash::addMessage('Payment method added successfully.');
                } else {
                    Flash::addMessage('Failed to add payment method. Please try again.', Flash::WARNING);
                }
            }
        } else {
            Flash::addMessage('Payment method name cannot be empty.', Flash::WARNING);
        }

        $this->redirect('/settings/show');
    }

}