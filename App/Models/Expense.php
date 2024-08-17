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

    public static function getExpensesDataForPieChart($startDate = null, $endDate = null) {

      function generatePastelColor() {
          $hue = rand(0, 360); 
          $saturation = rand(25, 50);
          $lightness = rand(75, 85);  
          return hslToHex($hue, $saturation, $lightness);
      }
  
      function hslToHex($h, $s, $l) {
          $s /= 100;
          $l /= 100;
          $c = (1 - abs(2 * $l - 1)) * $s;
          $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
          $m = $l - $c / 2;
          if ($h < 60) {
              $r = $c; $g = $x; $b = 0;
          } elseif ($h < 120) {
              $r = $x; $g = $c; $b = 0;
          } elseif ($h < 180) {
              $r = 0; $g = $c; $b = $x;
          } elseif ($h < 240) {
              $r = 0; $g = $x; $b = $c;
          } elseif ($h < 300) {
              $r = $x; $g = 0; $b = $c;
          } else {
              $r = $c; $g = 0; $b = $x;
          }
          $r = ($r + $m) * 255;
          $g = ($g + $m) * 255;
          $b = ($b + $m) * 255;
          return sprintf("#%02X%02X%02X", round($r), round($g), round($b));
      }
  
      $user = Auth::getUser();
  
      $sql = 'SELECT e.expense_category_assigned_to_user_id, d.name AS category_name_expenses, 
              SUM(e.amount) AS Total
              FROM expenses e
              JOIN expenses_category_assigned_to_users d
              ON e.expense_category_assigned_to_user_id = d.id
              WHERE e.user_id = :user_id';
  
      if ($startDate && $endDate) {
          $sql .= " AND e.date_of_expense BETWEEN :start_date AND :end_date";
      }
  
      $sql .= " GROUP BY d.name";
  
      $db = static::getDB();
      $stmt = $db->prepare($sql);
      $stmt->bindValue(':user_id', $user->id, PDO::PARAM_INT);
  
      if ($startDate && $endDate) {
          $stmt->bindParam(':start_date', $startDate);
          $stmt->bindParam(':end_date', $endDate);
      }
  
      $stmt->execute();
  
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
      $data = [];
  
      foreach ($result as $row) {
          $data[] = [    
              'category_name_expenses' => $row['category_name_expenses'],
              'total' => $row['Total'],
              'color' => generatePastelColor() 
          ];
      }
      echo json_encode($data);
    }

    public static function getExpensesDataForBarChart($startDate = null, $endDate = null) {

      $user = Auth::getUser();
  
      $sql = 'SELECT date_of_expense, SUM(amount) as Total
              FROM expenses
              WHERE user_id = :user_id';
  
      if ($startDate && $endDate) {
          $sql .= " AND date_of_expense BETWEEN :start_date AND :end_date";
      }
  
      $sql .= " GROUP BY date_of_expense";
  
      $db = static::getDB();
      $stmt = $db->prepare($sql);
  
      if ($startDate && $endDate) {
          $stmt->bindParam(':start_date', $startDate);
          $stmt->bindParam(':end_date', $endDate);
      }
  
      $stmt->bindValue(':user_id', $user->id, PDO::PARAM_INT);
      $stmt->execute();
  
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
  
      $data = [];
  
      foreach ($result as $row) {
          $data[] = [    
              'date_of_expense' => $row['date_of_expense'],
              'total_bar_chart_expense' => $row['Total'],
              'color' => '#e86471'
          ];
      }
      echo json_encode($data);
    }




}