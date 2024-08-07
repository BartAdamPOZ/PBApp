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

      $sql = 'SELECT *
              FROM incomes
              WHERE user_id = :user_id
              ORDER BY date_of_income ASC';

      $db = static::getDB();
      $stmt = $db->prepare($sql);
      $stmt->bindValue(':user_id', $user->id, PDO::PARAM_INT);
      $stmt->execute();

      $result =  $stmt->fetchAll(PDO::FETCH_ASSOC);

      $data = [];

      foreach ($result as $row) {
        $data [] = [
          'id' => $row['id'],
          'user_id' => $row['user_id'],
          'income_category_assigned_to_user_id' => $row['income_category_assigned_to_user_id'],
          'amount' => number_format($row['amount'], 2) . "PLN",
          'date_of_income' => $row['date_of_income'],
          'income_comment' => $row['income_comment']
        ];
      }


      $response = [
        "draw" => 1,
        "recordsTotal" => count($result),
        "recordsFiltered" => count($result),
        "data" => $data];

      
      return json_encode($response);

    }

  }
