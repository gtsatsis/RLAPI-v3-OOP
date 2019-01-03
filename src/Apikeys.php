<?
namespace RRLME;
include_once '../vendor/autoload.php';
class Apikeys
{

  use Ramsey\Uuid\Uuid;
  use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
  use RLME\SentrySys;
  
  public $username;
  public $email;
  public $userid;
  public $token;
  public function __construct(string $username)
  {
    include '../inc/development_db_password.inc.php';
    $dbconn = pg_connect("host=localhost port=5432 dbname=rlapi_devel user=rlapi_devel password=" . $dbPass); //Note, $dbPass is defined in development_db_password.inc.php
    $this->sentry_instance = new SentryInstance();
  }

  public function createUserAPIKey(mixed $id, string $apikeyName)
  {
    $this->userid = $id;
    $this->apikeyName = htmlspecialchars($apikeyName);
    $unique = false;
    while ($unique == false)
    {
      $apikey = Uuid::uuid4();
      $apikey = $apikey->toString();
      $prepareStatement = pg_prepare($dbconn, "check_if_api_key_exists", "SELECT * FROM tokens WHERE token = $1");
      $executePreparedStatement = pg_execute($dbconn, "check_if_api_key_exists", array($apikey));
      $numberOfRows = pg_num_rows($executePreparedStatement);
      if($numberOfRows == 0)
      {
        $unique = true;
      }
    }
    $prepareStatement = pg_prepare($dbconn, "instert_api_key", "INSERT INTO tokens ('user_id', 'token', 'name') VALUES ($1, $2, $3)");
    $executePreparedStatement = pg_execute($dbconn, "insert_api_key", $this->userid, $apikey, $this->apikeyName);
    if($prepareStatement !== false && $executePreparedStatement !== false)
    {
      return
        [
          'success' => true,
          'apikey' => [
            'created' => true,
            'key' => $apikey
          ]
        ];
    }
    else
    {
      return
        [
          'success' => false,
          'error_code' => 302882
        ];
      $this->sentry_instance->log_error('There was an oopsie. Check logs (ln 140) Time: ' . gmdate("Y-m-d H:i:s", time()));
    }
  }

  public function deleteUserAPIKey(string $apikey, mixed $id, string $email)
  {
    $this->userid = htmlspecialchars($id);
    $this->email = htmlspecialchars($email);
    $this->token = htmlspecialchars($apikey);
    $prepareStatement = pg_prepare($dbconn, "delete_api_key", "DELETE FROM tokens WHERE user_id = $1 AND token = $2");
    $executePreparedStatement = pg_execute($dbconn, "delete_api_key", array($this->userid, $this->token));
    if($prepareStatement !== false && $executePreparedStatement !== false)
    {
      return
        [
           'success' => true,
           'apikey' => [
             'deleted' => true
           ]
        ];
    }
    else
    {
      return
        [
          'success' => false,
          'errorcode' => 302882
        ];
      $this->sentry_instance->log_error('There was an oopsie. Check logs (ln 159) Time: ' . gmdate("Y-m-d H:i:s", time()));
    }
  }

}
