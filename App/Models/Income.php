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
  
      // Zwróć obiekt klasy Income, jeśli rekord został znaleziony
      return $stmt->fetchObject(static::class);
    }

    public function editIncome($data) {

      $this->income_category_assigned_to_user_id = $data['income_category_assigned_to_user_id'];
      $this->amount = $data['amount'];
      $this->date_of_income = $data['date_of_income'];
      $this->income_comment = $data['income_comment'];


      // Sprawdź, czy nie ma błędów walidacji
      if (empty($this->errors)) {
          $sql = 'UPDATE incomes
                  SET income_category_assigned_to_user_id = :income_category_assigned_to_user_id, 
                      amount = :amount, 
                      date_of_income = :date_of_income, 
                      income_comment = :income_comment
                  WHERE id = :id'; 
  
          $db = static::getDB();
          $stmt = $db->prepare($sql);
  
          // Przypisanie wartości do parametrów SQL
          $stmt->bindValue(':income_category_assigned_to_user_id', $this->income_category_assigned_to_user_id, PDO::PARAM_INT);
          $stmt->bindValue(':amount', $this->amount, PDO::PARAM_STR);
          $stmt->bindValue(':date_of_income', $this->date_of_income, PDO::PARAM_STR);
          $stmt->bindValue(':income_comment', $this->income_comment, PDO::PARAM_STR);
          $stmt->bindValue(':id', $this->id, PDO::PARAM_INT);
  
          // Wykonanie zapytania SQL
          return $stmt->execute();
      } else {
          return false; // Zwróć false, jeśli wystąpiły błędy walidacji
      }
    }
}