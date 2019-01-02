<?php

/**
 * Class
 */

class User
{

  include '../vendor/autoload.php';

 

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

  /* Functions related to user detail fetching */

  public function getUserIdByApiKey($apikey){

  }

  public function getUserById($id){

  }

  public function getUserByEmail($email){

  }

  public function getUserByUsername($username){

  }

  /* Functions Related to creating and deleting users */

  public function createUser($username, $password, $email){
    // First sanitize user input
    $this->username = htmlspecialchars($username);
    $this->email = htmlspecialchars($email);

    // Encrypt Password

    $this->password = password_hash(htmlentities($password), PASSWORD_BCRYPT);

    unset($password); // Make sure we are NOT storing the password in the script. Just in case.

    // Create User ID
    $this->userid = Uuid::uuid4();
    $this->userid = $this->userid->toString();

    $preparedStatement = pg_prepare($dbconn, "create_user", "INSERT INTO users ('id', 'username', 'password', 'email', 'tier', 'is_admin', 'is_blocked') VALUES ($1, $2, $3, $4, 'free', false, false)");
    $executePreparedStatement =  pg_execute($dbconn, "create_user", array($this->userid, $this->username, $this->password, $this->email));

    if(pg_result_status($executePreparedStatement) == 1 || pg_result_status($executePreparedStatement) == 6) {

        return json_encode(array('success' => true, 'status' => 'created', 'account' => array('id' => $this->userid, 'username' => $this->username, 'email' => $this->email)));

    }else{

        return json_encode(array('success' => false, 'message' => 'Something went horribly wrong while inserting the user into the database! Check the logs!'));

    }
  }

  public function deleteUser($id, $email){
    $this->userid = $id;
    $this->email = $email;

    $preparedStatement = pg_prepare($dbconn, "delete_user", "DELETE FROM users WHERE id = $1 AND email = $2");
    $executePreparedStatement = pg_execute($dbconn, "delete_user", array($this->userid, $this->email));

    $prepareStatementApiKeys = pg_prepare($dbconn, "delete_user_api_keys", "DELETE FROM tokens WHERE user_id = $1");
    $executePreparedStatementApiKeys = pg_execute($dbconn, "delete_user_api_keys", array($this->userid));

    if(pg_result_status($executePreparedStatement) == 1 || pg_result_status($executePreparedStatement) == 6 && pg_result_status($executePreparedStatementApiKeys) == 1 || pg_result_status($executePreparedStatementApiKeys) == 6){

        return json_encode(array('success' => true, 'account' => array('deleted' => true)));

    }else{

        return json_encode(array('success' => false, 'message' => 'Something went horribly wrong while inserting the user into the database! Check the logs!'));

    }
  }

  /* Functions Regarding API Keys (Tokens) */

  public function createUserAPIKey($id) {
    $this->userid = $id;

    $unique = false;
    while($unique == false){

        $apikey = Uuid::uuid4();
        $apikey = $apikey->toString();

        $prepareStatement = pg_prepare($dbconn, "check_if_api_key_exists", "SELECT * FROM tokens WHERE token = $1");
        $executePreparedStatement = pg_execute($dbconn, "check_if_api_key_exists", array($apikey));

        $numberOfRows = pg_num_rows($executePreparedStatement);
        if($numberOfRows == 0){
            $unique = true;
        }
    }

    $prepareStatement = pg_prepare($dbconn, "instert_api_key", "INSERT INTO tokens ('user_id', 'token') VALUES ($1, $2)");
    $executePreparedStatement = pg_execute($dbconn, "insert_api_key", $this->userid, $apikey);

    //TODO: If both worked, send this:

    if(){

    }else{

    }

  }

  public function deleteUserAPIKey($apikey, $id, $email) {
     $this->userid = $id;
     $this->email = $email;
     $this->token = $apikey;

     $prepareStatement = pg_prepare($dbconn, "delete_api_key", "DELETE FROM tokens WHERE user_id = $1 AND token = $2");
     $executePreparedStatement = pg_execute($dbconn, "delete_api_key", array($this->userid, $this->token));

    if(){ //TODO: Again, properly check if statement worked.

    }else{

    }
  }

  /* Other user-related functions */

  public function setUserTier($id, $email, $tier) {
    
  }

}
