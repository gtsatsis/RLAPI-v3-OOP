<?php

/**
 * Class
 */

class User
{
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
  
  public function __construct($username, $password = null) {} 
  public function setTier($userid, $tier) {
	
  }
  public function createUser($username, $password, $email){
    // First sanitize user input
    $this->username = htmlspecialchars($username);
    $this->email = htmlspecialchars($email);

    // Encrypt Password

    $this->password = password_hash(htmlentities($password), PASSWORD_BCRYPT);

    $password = rand(); // Set password to something random.
    unset($password); // Make sure we are NOT storing the password in the script. Just in case.

    // Create User ID
    $uuid4 = Uuid::uuid4();
    $this->userid = $uuid4->toString();

    
  }

}
