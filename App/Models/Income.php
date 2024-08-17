<?php

namespace App\Models;

use PDO;
use \App\Models;
use \Core\View;
use \App\Auth;


class Income extends \Core\Model {

  public $errors = [];
  public $id;
  public $user_id;
  public $income_category_assigned_to_user_id;
  public $amount;
  public $date_of_income;
  public $income_comment;

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

        $sql = 'INSERT INTO incomes (user_id, income_category_assigned_to_user_id, amount, date_of_income, income_comment)
                VALUES(:user_id, :income_category_assigned_to_user_id, :amount, :date_of_income, :income_comment)';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt -> bindValue(':user_id', $this -> user_id, PDO::PARAM_INT);
        $stmt -> bindValue(':income_category_assigned_to_user_id', $this -> income_category_assigned_to_user_id, PDO::PARAM_INT);
        $stmt -> bindValue(':amount', $this -> amount, PDO::PARAM_STR);
        $stmt -> bindValue(':date_of_income', $this -> date_of_income, PDO::PARAM_STR);
        $stmt -> bindValue(':income_comment', $this -> income_comment, PDO::PARAM_STR);

        $stmt -> execute();

        return true;
  
      } 

      return false;
  
    }

    public static function getIncomesByUserId() {

      $user = Auth::getUser();

      $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;

      $sql = 'SELECT i.id, i.income_category_assigned_to_user_id , i.user_id, i.amount, i.income_comment, i.date_of_income, c.name AS category_name
              FROM incomes i
              JOIN incomes_category_assigned_to_users c
              ON i.income_category_assigned_to_user_id = c.id
              WHERE i.user_id = :user_id
              ORDER BY i.date_of_income DESC';

      $db = static::getDB();
      $stmt = $db->prepare($sql);
      $stmt->bindValue(':user_id', $user->id, PDO::PARAM_INT);
      $stmt->execute();

      $result =  $stmt->fetchAll(PDO::FETCH_ASSOC);

      $data = [];

      foreach ($result as $row) {
        $data [] = [
          'id' => $row['id'],
          'income_category_assigned_to_user_id' => $row['income_category_assigned_to_user_id'],
          'amount' => $row['amount'], 2,
          'date_of_income' => $row['date_of_income'],
          'income_comment' => $row['income_comment'],
          'category_name' => $row['category_name']
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
              FROM incomes 
              WHERE id = :id';
  
      $db = static::getDB();
      $stmt = $db->prepare($sql);
      $stmt->bindValue(':id', $id, PDO::PARAM_INT);
      $stmt->execute();
  
      return $stmt->fetchObject(static::class);
    }

    public function editIncome($data) {

      $this->income_category_assigned_to_user_id = $data['income_category_assigned_to_user_id'];
      $this->amount = $data['amount'];
      $this->date_of_income = $data['date_of_income'];
      $this->income_comment = $data['income_comment'];

      if (empty($this->errors)) {
          $sql = 'UPDATE incomes
                  SET income_category_assigned_to_user_id = :income_category_assigned_to_user_id, 
                      amount = :amount, 
                      date_of_income = :date_of_income, 
                      income_comment = :income_comment
                  WHERE id = :id'; 
  
          $db = static::getDB();
          $stmt = $db->prepare($sql);
  
          $stmt->bindValue(':income_category_assigned_to_user_id', $this->income_category_assigned_to_user_id, PDO::PARAM_INT);
          $stmt->bindValue(':amount', $this->amount, PDO::PARAM_STR);
          $stmt->bindValue(':date_of_income', $this->date_of_income, PDO::PARAM_STR);
          $stmt->bindValue(':income_comment', $this->income_comment, PDO::PARAM_STR);
          $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
  
          return $stmt->execute();
          
      } else {

          return false; 
      }
    }

    public function deleteIncome($data) {

      $this -> id = $data['id'];

      if (empty($this->errors)) {

        $sql = 'DELETE FROM incomes
                WHERE id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);

        return $stmt->execute();
      }

      return false;

    }

    public static function getIncomesDataForPieChart($startDate = null, $endDate = null) {

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
  
      $sql = 'SELECT i.income_category_assigned_to_user_id, c.name AS category_name, 
              SUM(i.amount) AS Total
              FROM incomes i
              JOIN incomes_category_assigned_to_users c
              ON i.income_category_assigned_to_user_id = c.id
              WHERE i.user_id = :user_id';
  
      if ($startDate && $endDate) {
          $sql .= " AND i.date_of_income BETWEEN :start_date AND :end_date";
      }
  
      $sql .= " GROUP BY c.name";
  
      $db = static::getDB();
      $stmt = $db->prepare($sql);
  
      if ($startDate && $endDate) {
          $stmt->bindParam(':start_date', $startDate);
          $stmt->bindParam(':end_date', $endDate);
      }
  
      $stmt->bindValue(':user_id', $user->id, PDO::PARAM_INT);
      $stmt->execute();
  
      $result =  $stmt->fetchAll(PDO::FETCH_ASSOC);
  
      $data = [];
  
      foreach ($result as $row) {
          $data[] = [    
              'category_name' => $row['category_name'],
              'total' => $row['Total'],
              'color' => generatePastelColor() 
          ];
      }
      echo json_encode($data);
  }
  

  public static function getIncomesDataForBarChart($startDate = null, $endDate = null) {

    $user = Auth::getUser();

    $sql = 'SELECT date_of_income, SUM(amount) as Total
            FROM incomes
            WHERE user_id = :user_id';

    if ($startDate && $endDate) {
        $sql .= " AND date_of_income BETWEEN :start_date AND :end_date";
    }

    $sql .= " GROUP BY date_of_income";

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
            'date_of_income' => $row['date_of_income'],
            'total_bar_chart_income' => $row['Total'],
            'color' => '#3e76de'
        ];
    }
    echo json_encode($data);
  }

}