<?php

/**
 * Class
 */

class User
{
  include 'vendor/autoload.php';
  use Ramsey\Uuid\Uuid;
  use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
  
  public $username;
  public $email;
  public $password;
  public $userid;
  public $token;
  public $email;
  public $tier;
  public $isLoggedIn;
  public $isAdmin;
  public $isBlocked;
  // TODO: add all variables
  
  public function __construct($username, $password = null) {
    include '../inc/development_db_password.inc.php';
    $dbconn = pg_connect("host=localhost port=5432 dbname=rlapi_devel user=rlapi_devel password=".$dbPass);
  } 

  public function createUser($username, $password, $email){
    // First sanitize user input
    $this->username = htmlspecialchars($username);
    $this->email = htmlspecialchars($email);

    // Encrypt Password

    $this->password = password_hash(htmlentities($password), PASSWORD_BCRYPT);

    unset($password); // Make sure we are NOT storing the password in the script. Just in case.

    // Create User ID
    $this->userid = Uuid::uuid4();
    $this->userid = $uuid4->toString();

    $preparedStatement = pg_prepare($dbconn, "create_user", "INSERT INTO users ('id', 'username', 'password', 'email', 'tier', 'is_admin', 'is_blocked') VALUES ($1, $2, $3, $4, 'free', false, false)");
    $executePreparedStatement =  pg_execute($dbconn, "create_user", array($this->userid, $this->username, $this->password, $this->email));
  }

  public function createUserAPIKey($id, $email) {

  }

  public function deleteUserAPIKey($apikey, $id, $email) {
    
  }

  public function setUserTier($email) {

  }

}