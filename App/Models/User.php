<?php

namespace App\Models;

use PDO;
use \App\Token;
use \App\Mail;
use \Core\View;


/**
 * User model
 *
 * PHP version 7.0
 */
class User extends \Core\Model
{

    /**
     * Error messages
     *
     * @var array
     */
    public $errors = [];
    public $id;
    public $name;
    public $email;
    public $password_hash;
    public $password;
    public $password_reset_hash;
    public $password_reset_expires_at;
    public $password_reset_token;
    public $activation_token;
    public $activation_hash;
    public $is_active;
    public $remember_token;
    public $expiry_timestamp;

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

    /**
     * Save the user model with the current property values
     *
     * @return boolean  True if the user was saved, false otherwise
     */
    public function save()
    {
        $this->validate();

        if (empty($this->errors)) {

            $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

            $token = new Token();
            $hashed_token = $token -> getHash();
            $this -> activation_token = $token -> getValue();

            $sql = 'INSERT INTO users (name, email, password_hash, activation_hash)
                    VALUES (:name, :email, :password_hash, :activation_hash)';

            $db = static::getDB();
            $stmt = $db->prepare($sql);

            $stmt->bindValue(':name', $this->name, PDO::PARAM_STR);
            $stmt->bindValue(':email', $this->email, PDO::PARAM_STR);
            $stmt->bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
            $stmt->bindValue(':activation_hash', $hashed_token, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $this -> id = $db -> lastInsertId();
                return true;
            }
        }

        return false;
    }

    public static function copyDatabaseRecords($user_id) {

        self::restoreOrCreateDefaultCategories($user_id, 'income');
        
        self::restoreOrCreateDefaultCategories($user_id, 'expense');

        self::restoreOrCreateDefaultCategories($user_id, 'payment');
    }

    public static function restoreOrCreateDefaultCategories($user_id, $type) {
        $tableMap = [
            'income' => ['assigned' => 'incomes_category_assigned_to_users', 'default' => 'incomes_category_default'],
            'expense' => ['assigned' => 'expenses_category_assigned_to_users', 'default' => 'expenses_category_default'],
            'payment' => ['assigned' => 'payment_methods_assigned_to_users', 'default' => 'payment_methods_default']
        ];
    
        $assignedTable = $tableMap[$type]['assigned'];
        $defaultTable = $tableMap[$type]['default'];
    
        $sql = "SELECT 1 FROM $assignedTable WHERE user_id = :user_id";
        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
    
        if ($stmt->fetch()) {

            $sql = "DELETE FROM $assignedTable WHERE user_id = :user_id";
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
        }
    
        $sql = "INSERT INTO $assignedTable(user_id, name)
                SELECT :user_id, name
                FROM $defaultTable";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
    }

    public static function getCategories($user_id, $type)  {

        $tableMap = [
            'income' => 'incomes_category_assigned_to_users',
            'expense' => 'expenses_category_assigned_to_users',
            'payment' => 'payment_methods_assigned_to_users'
        ];
    
        $tableName = $tableMap[$type];
    
        $sql = "SELECT name , id
                FROM $tableName WHERE user_id = :user_id
                ORDER BY name ASC";
    
        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
    
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }




    /**
     * Validate current property values, adding valiation error messages to the errors array property
     *
     * @return void
     */
    public function validate()
    {
        // Name
        if ($this->name == '') {
            $this->errors[] = 'Name is required';
        }

        // email address
        if (filter_var($this->email, FILTER_VALIDATE_EMAIL) === false) {
            $this->errors[] = 'Invalid email';
        }
        if (static::emailExists($this->email, $this -> id ?? null)) {
            $this->errors[] = 'email already taken';
        }

        // Password
        if (isset($this -> password)) {

            if (strlen($this->password) < 6) {
                $this->errors[] = 'Please enter at least 6 characters for the password';
            }

            if (preg_match('/.*[a-z]+.*/i', $this->password) == 0) {
                $this->errors[] = 'Password needs at least one letter';
            }

            if (preg_match('/.*\d+.*/i', $this->password) == 0) {
                $this->errors[] = 'Password needs at least one number';
            }
        }
    }

    /**
     * See if a user record already exists with the specified email
     *
     * @param string $email email address to search for
     *
     * @return boolean  True if a record already exists with the specified email, false otherwise
     */
    public static function emailExists($email, $ignore_id = null)
    {
        $user = static::findByEmail($email);

        if ($user) {
            if ($user -> id != $ignore_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find a user model by email address
     *
     * @param string $email email address to search for
     *
     * @return mixed User object if found, false otherwise
     */
    public static function findByEmail($email)
    {
        $sql = 'SELECT * FROM users WHERE email = :email';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    public static function authenticate($email, $password) {

      $user = static::findByEmail($email);

      if($user && $user -> is_active) {

        if (password_verify($password, $user -> password_hash)) {

          return $user;

        }

        return false;

      }

    }

    public static function findByID($id)
    {
        $sql = 'SELECT * FROM users WHERE id = :id';

        $db = static::getDB();
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        $stmt->setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt->execute();

        return $stmt->fetch();
    }

    public function rememberLogin() {

        $token = new Token();
        $hashed_token = $token -> getHash();
        $this -> remember_token = $token -> getValue();

        $this ->expiry_timestamp = time() + 60 * 60 * 24 * 30;

        $sql = 'INSERT INTO remembered_logins (token_hash, user_id, expires_at)
                VALUES (:token_hash, :user_id, :expires_at)';

        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt -> bindValue(':user_id', $this -> id, PDO::PARAM_INT);
        $stmt -> bindValue(':expires_at', date('Y-m-d H:i:s', $this ->expiry_timestamp), PDO::PARAM_STR);

        return $stmt -> execute();

    }

    public static function sendPasswordReset($email) {
        
        $user = static::findByEmail($email);

        if ($user) {

            if($user -> startPasswordReset()) {

                $user -> sendPasswordResetEmail();

            }

        }
    }

    protected function startPasswordReset() {

        $token = new Token();
        $hashed_token = $token ->getHash();
        $this -> password_reset_token = $token -> getValue();

        $expiry_timestamp = time() + 60 * 60 * 2;

        $sql = 'UPDATE users
                SET password_reset_hash = :token_hash,
                    password_reset_expires_at = :expires_at
                WHERE id = :id';

        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);
        $stmt -> bindValue(':expires_at', date('Y-m-d H:i:s', $expiry_timestamp), PDO::PARAM_STR);
        $stmt -> bindValue(':id', $this -> id, PDO::PARAM_INT);

        return $stmt -> execute();
    }

    protected function sendPasswordResetEmail() {

        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/password/reset/' . $this -> password_reset_token;

        $text = View::getTemplate('Password/reset_email.txt', ['url' => $url]);
        $html = View::getTemplate('Password/reset_email.html', ['url' => $url]);

        Mail::send($this -> email, 'Password reset', $text, $html, $this -> name);

    }

    public static function findByPasswordReset($token) {

        $token = new Token($token);
        $hashed_token = $token -> getHash();

        $sql = 'SELECT * FROM users
                WHERE password_reset_hash = :token_hash';

        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':token_hash', $hashed_token, PDO::PARAM_STR);

        $stmt -> setFetchMode(PDO::FETCH_CLASS, get_called_class());

        $stmt -> execute();

        $user = $stmt -> fetch();

        if ($user) {

            if (strtotime($user -> password_reset_expires_at) > time()) {
                return $user;
            }
        }

    }

    public function resetPassword($password) {

        $this -> password = $password;

        $this -> validate();

        if (empty($this -> errors)) {

            $password_hash = password_hash($this -> password, PASSWORD_DEFAULT);

            $sql = 'UPDATE users
                    SET password_hash = :password_hash,
                    password_reset_hash = NULL,
                    password_reset_expires_at = NULL
                    WHERE id = :id';

            $db = static::getDB();
            $stmt = $db -> prepare($sql);

            $stmt -> bindValue(':id', $this -> id, PDO::PARAM_INT);
            $stmt -> bindValue(':password_hash', $password_hash, PDO::PARAM_STR);

            return $stmt -> execute();
        }

        return false;
    }

    public function sendActivationEmail() {

        $url = 'http://' . $_SERVER['HTTP_HOST'] . '/signup/activate/' . $this -> activation_token;

        $text = View::getTemplate('Signup/activation_email.txt', ['url' => $url]);
        $html = View::getTemplate('Signup/activation_email.html', ['url' => $url]);

        Mail::send($this -> email, 'Account activation', $text, $html, $this -> name);

    }


    public static function activate($value) {

        $token = new Token($value);
        $hashed_token = $token -> getHash();

        $sql = 'UPDATE users
                SET is_active = 1,
                    activation_hash = null
                WHERE activation_hash = :hashed_token';

        $db = static::getDB();
        $stmt = $db -> prepare($sql);

        $stmt -> bindValue(':hashed_token', $hashed_token, PDO::PARAM_STR);

        $stmt -> execute();        

    }

    public function updateProfile($data) {

        $this -> name = $data['name'];
        $this -> email = $data['email'];
        
        //Only validate and update the password if a value provided
        if ($data['password'] != '') {
            $this -> password = $data['password'];
        }

        $this -> validate();

        if (empty($this -> errors)) {

            $sql = 'UPDATE users
                    SET name = :name,
                        email = :email';

            //Add password if it's set
            if (isset($this -> password)) {
                $sql .= ', password_hash = :password_hash';
            }

            $sql .= "\nWHERE id = :id";

            $db = static::getDB();
            $stmt = $db -> prepare($sql);

            $stmt -> bindValue(':name', $this -> name, PDO::PARAM_STR);
            $stmt -> bindValue(':email', $this -> email, PDO::PARAM_STR);
            $stmt -> bindValue(':id', $this -> id, PDO::PARAM_INT);

            //Add password if it's set
            if (isset($this -> password)) {

                $password_hash = password_hash($this -> password, PASSWORD_DEFAULT);
                $stmt -> bindValue(':password_hash', $password_hash, PDO::PARAM_STR);
                
            }
            
            return $stmt -> execute();
        }

        return false;
    }

    public function addCategory($categoryName, $userId, $type) {

        $tableMap = [
            'income' => 'incomes_category_assigned_to_users',
            'expense' => 'expenses_category_assigned_to_users',
            'payment' => 'payment_methods_assigned_to_users'
        ];

        $tableName = $tableMap[$type];

        $sql = "INSERT INTO $tableName (user_id, name) 
                VALUES (:user_id, :name)";

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':name', $categoryName, PDO::PARAM_STR);

        return $stmt->execute();
    }

    public function categoryExists($categoryName, $userId, $type) {

        $tableMap = [
            'income' => 'incomes_category_assigned_to_users',
            'expense' => 'expenses_category_assigned_to_users',
            'payment' => 'payment_methods_assigned_to_users'
        ];

        $tableName = $tableMap[$type];

        $sql = "SELECT * 
                FROM $tableName 
                WHERE user_id = :user_id 
                AND name = :name";

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':name', $categoryName, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch() !== false;
    }

    public function deleteCategory($categoryName, $userId, $type) {

        $tableMap = [
            'income' => 'incomes_category_assigned_to_users',
            'expense' => 'expenses_category_assigned_to_users',
            'payment' => 'payment_methods_assigned_to_users'
        ];

        $tableName = $tableMap[$type];

        $sql = "DELETE FROM $tableName 
                WHERE user_id = :user_id AND name = :name";

        $db = static::getDB();
        $stmt = $db->prepare($sql);

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':name', $categoryName, PDO::PARAM_STR);
        $stmt->execute();

    }

}
