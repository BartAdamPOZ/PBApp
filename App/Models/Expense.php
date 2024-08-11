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
        $stmt -> bindValue(':expense_comment', $this -> expense_comment, PDO::PARAM_STR);

        $stmt -> execute();

        return true;
  
      } 

      return false;
  
    }
    
    public static function getExpensesByUserId() {

      $user = Auth::getUser();

      $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;

      $sql = 'SELECT e.id, e.expense_category_assigned_to_user_id, e.payment_method_assigned_to_user_id, e.user_id, e.amount, e.expense_comment, e.date_of_expense, c.name AS category_name , d.name AS payment_method_name
              FROM expenses e
              JOIN expenses_category_assigned_to_users c
              ON e.expense_category_assigned_to_user_id = c.id
              JOIN payment_methods_assigned_to_users d
              ON e.payment_method_assigned_to_user_id = d.id
              WHERE e.user_id = :user_id
              ORDER BY e.date_of_expense DESC';

      $db = static::getDB();
      $stmt = $db->prepare($sql);
      $stmt->bindValue(':user_id', $user->id, PDO::PARAM_INT);
      $stmt->execute();

      $result =  $stmt->fetchAll(PDO::FETCH_ASSOC);

      $data = [];

      foreach ($result as $row) {
        $data [] = [
          'id' => $row['id'],
          'expense_category_assigned_to_user_id' => $row['expense_category_assigned_to_user_id'],
          'payment_method_assigned_to_user_id' => $row['payment_method_assigned_to_user_id'],
          'amount' => $row['amount'], 2,
          'date_of_expense' => $row['date_of_expense'],
          'expense_comment' => $row['expense_comment'],
          'category_name' => $row['category_name'],
          'payment_method_name' => $row['payment_method_name']
        ];
      }

      $response = [
        "draw" => $draw,
        "recordsTotal" => count($result),
        "recordsFiltered" => count($result),
        "data" => $data];

      return json_encode($response);

    }

    public static function findById($id) {

      $sql = 'SELECT * 
              FROM expenses 
              WHERE id = :id';
  
      $db = static::getDB();
      $stmt = $db->prepare($sql);
      $stmt->bindValue(':id', $id, PDO::PARAM_INT);
      $stmt->execute();
  
      return $stmt->fetchObject(static::class);
    }

    public function editExpense($data) {

      $this->expense_category_assigned_to_user_id = $data['expense_category_assigned_to_user_id'];
      $this->payment_method_assigned_to_user_id = $data['payment_method_assigned_to_user_id'];
      $this->amount = $data['amount'];
      $this->date_of_expense = $data['date_of_expense'];
      $this->expense_comment = $data['expense_comment'];

      if (empty($this->errors)) {

          $sql = 'UPDATE expenses
                  SET expense_category_assigned_to_user_id = :expense_category_assigned_to_user_id, 
                      payment_method_assigned_to_user_id = :payment_method_assigned_to_user_id, 
                      amount = :amount, 
                      date_of_expense = :date_of_expense, 
                      expense_comment = :expense_comment
                  WHERE id = :id'; 
  
          $db = static::getDB();
          $stmt = $db->prepare($sql);
  
          $stmt->bindValue(':expense_category_assigned_to_user_id', $this->expense_category_assigned_to_user_id, PDO::PARAM_INT);
          $stmt->bindValue(':payment_method_assigned_to_user_id', $this->payment_method_assigned_to_user_id, PDO::PARAM_INT);
          $stmt->bindValue(':amount', $this->amount, PDO::PARAM_STR);
          $stmt->bindValue(':date_of_expense', $this->date_of_expense, PDO::PARAM_STR);
          $stmt->bindValue(':expense_comment', $this->expense_comment, PDO::PARAM_STR);
          $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
  
          return $stmt->execute();
          
      } else {

          return false; 
      }

    }

    public function deleteExpense($data) {

      $this -> id = $data['id'];

      if (empty($this->errors)) {

        $sql = 'DELETE FROM expenses
                WHERE id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();
      }

      return false;

    }




}