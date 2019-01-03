<?php
namespace RLME;
include_once '../vendor/autoload.php';
class Getters
{

  use Ramsey\Uuid\Uuid;
  use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
  use RLME\SentrySys;

  public $username;
  public $email;
  public $password;
  public $userid;
  public $token;
  public $apikeys;

  public function __construct(string $username,string $password = null)

  {
    include '../inc/development_db_password.inc.php';
    $dbconn = pg_connect("host=localhost port=5432 dbname=rlapi_devel user=rlapi_devel password=" . $dbPass); //Note, $dbPass is defined in development_db_password.inc.php
    $this->sentry_instance = new SentryInstance();
  }

  public function getApiKeysFromUserId(mixed $userId)
  {
    $this->userId = $userId;
    $prepareStatement = pg_prepare($dbconn, "get_apikeys_by_user", "SELECT * FROM tokens WHERE user_id = $1");
    $executePreparedStatement = pg_execute($dbconn, "get_apikeys_by_user", $this->userid);
    if($prepareStatement !== false && $executePreparedStatement !== false)
    {
      $this->apikeys = pg_fetch_object($executePreparedStatement, 0);
    }
    else
    {
      return json_encode(array('success' => false, 'message' => 'Error! getApiKeyFromUserId failed, either prepareStatement or executePreparedStatement didnt work!'));
      $this->sentry_instance->log_error('getApiKeyFromUserId failed, either prepareStatement or executePreparedStatement didnt work! Time: ' . gmdate("Y-m-d H:i:s", time()));
    }
  }

  public function getUserIdByApiKey(string $apikey)
  {
    $this->apikey = $apikey;
    $prepareStatement = pg_prepare($dbconn, "get_user_by_apikey", "SELECT * FROM tokens WHERE  = $1");
    $executePreparedStatement = pg_execute($dbconn, "get_user_by_apikey", $this->apikey);

    if($prepareStatement !== false && $executePreparedStatement !== false)
    {

      $this->getUserById(pg_fetch_object($executePreparedStatement, 0)->user_id);

    }
    else
    {

      return json_encode(array('success' => false, 'message' => 'Error! getUserByApiKey failed, either prepareStatement or executePreparedStatement didnt work!'));

      $this->sentry_instance->log_error('getUserByApiKey failed, either prepareStatement or executePreparedStatement didnt work! Time: ' . gmdate("Y-m-d H:i:s", time()));

    }
  }
  
  public function getUserById(mixed $id)
  {
    $this->userid = $id;
    $prepareStatement = pg_prepare($dbconn, "get_user_by_id", "SELECT * FROM users WHERE id = $1");
    $executePreparedStatement = pg_execute($dbconn, "get_user_by_id", $this->userid);
    if($prepareStatement !== false && $executePreparedStatement !== false)
    {
      $this->userDetails = pg_fetch_object($executePreparedStatement);
    }
    else
    {

      return json_encode(array('success' => false, 'message' => 'Error! getUserById failed, either prepareStatement or executePreparedStatement didnt work!'));

      $this->sentry_instance->log_error('getUserById failed, either prepareStatement or executePreparedStatement didnt work! Time: ' . gmdate("Y-m-d H:i:s", time()));

    }
  }

  public function getUserByEmail(string $email)
  {
    $this->email = $email;
    $prepareStatement = pg_prepare($dbconn, "get_user_by_username", "SELECT * FROM users WHERE id = $1");
    $executePreparedStatement = pg_execute($dbconn, "get_user_by_username", $this->id);
    if($prepareStatement !== false && $executePreparedStatement !== false)
    {
      $this->userDetails = pg_fetch_object($executePreparedStatement);
    }
    else
    {
      return json_encode(array('success' => false, 'message' => 'Error! getUserByEmail failed, either prepareStatement or executePreparedStatement didnt work!'));
      $this->sentry_instance->log_error('getUserByEmail failed, either prepareStatement or executePreparedStatement didnt work! Time: ' . gmdate("Y-m-d H:i:s", time()));
    }
  }

  public function getUserByUsername(string $username)
  {
    $this->username = htmlspecialchars($username);
    $prepareStatement = pg_prepare($dbconn, "get_user_by_username", "SELECT * FROM users WHERE username = $1");
    $executePreparedStatement = pg_execute($dbconn, "get_user_by_username", $this->username);
    if($prepareStatement !== false && $executePreparedStatement !== false)
    {
      $this->userDetails = pg_fetch_object($executePreparedStatement);
    }
    else
    {
      return json_encode(array('success' => false, 'message' => 'Error! getUserByName failed, either prepareStatement or executePreparedStatement didnt work!'));
      $this->sentry_instance->log_error('getUserByName failed, either prepareStatement or executePreparedStatement didnt work! Time: ' . gmdate("Y-m-d H:i:s", time()));
    }
  }
}
