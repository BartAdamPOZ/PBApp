<?php

namespace App\Models;

use PDO;
use \App\Models;
use \Core\View;
use \App\Auth;

class Expense extends \Core\Model {

  public $errors = [];
  public $id;
  public $user_id;
  public $expense_category_assigned_to_user_id;
  PUBLIC $payment_method_assigned_to_user_id;
  public $amount;
  public $date_of_expense;
  public $expense_comment;

  /**
     * Class constructor
     *
     * @param array $data  Initial property values (optional)
     *
     * @return void
     */
    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        };
    }

    public function save() {
  
      if (empty($this->errors)) {

        $user = Auth::getUser();

        $this -> user_id = $user-> id;

        $sql = 'INSERT INTO expenses (user_id, expense_category_assigned_to_user_id, payment_method_assigned_to_user_id, amount, date_of_expense, expense_comment)
                VALUES(:user_id, :expense_category_assigned_to_user_id, :payment_method_assigned_to_user_id, :amount, :date_of_expense, :expense_comment)';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt -> bindValue(':user_id', $this -> user_id, PDO::PARAM_INT);
        $stmt -> bindValue(':expense_category_assigned_to_user_id', $this -> expense_category_assigned_to_user_id, PDO::PARAM_INT);
        $stmt -> bindValue(':payment_method_assigned_to_user_id', $this -> payment_method_assigned_to_user_id, PDO::PARAM_INT);
        $stmt -> bindValue(':amount', $this -> amount, PDO::PARAM_STR);
        $stmt -> bindValue(':date_of_expense', $this -> date_of_expense, PDO::PARAM_STR);
        $stmt -> bindValue(':income_comment', $this -> expense_comment, PDO::PARAM_STR);

        $stmt -> execute();

        return true;
  
      } 

      return false;
  
    }
    
    public static function getExpensesByUserId($user_id) {

      $sql = 'SELECT *
              FROM expenses
              WHERE user_id = :user_id
              ORDER BY date_of_expense ASC';

      $db = static::getDB();
      $stmt = $db->prepare($sql);
      $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
      $stmt->execute();

      return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }



}