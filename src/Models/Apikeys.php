<?
namespace RLME\Models;
include_once '../../vendor/autoload.php';
class Apikeys
{

  use Ramsey\Uuid\Uuid;
  use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
  use RLME\Utils\Sentry;
  
  public $username;
  public $email;
  public $userid;
  public $token;
  public $sentry_instance;

  public function __construct()
  {
    include '../../inc/development_db_password.inc.php';
    $this->sentry_instance = new SentryInstance();
  }

  public function createUserAPIKey(mixed $id, string $apikeyName)
  {
    $this->userid = $id;
    $apikeyName = $apikeyName;
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
    $executePreparedStatement = pg_execute($dbconn, "insert_api_key", $this->userid, $apikey, $apikeyName);
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
      $this->sentry_instance->log_error('There was an oopsie. Check logs (ln 59) Time: ' . gmdate("Y-m-d H:i:s", time()));
    }
  }

  public function deleteUserAPIKey(string $apikey, mixed $id, string $email)
  {
    $this->userid = $id;
    $this->email = $email;
    $this->token = $apikey;
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
      $this->sentry_instance->log_error('There was an oopsie. Check logs (ln 87) Time: ' . gmdate("Y-m-d H:i:s", time()));
    }
  }

  public function renameApiKey(string $user_id, string $apikey, string $newFriendlyName)
  {
    $prepareStatement = pg_prepare($dbconn, "rename_apikey", "UPDATE tokens SET name = $1 WHERE token = $2");
    $executePreparedStatement = pg_execute($dbconn, "rename_apikey", $newFriendlyName, $apikey);

    if($executePreparedStatement){
      return
        [
        'success' => true,
        'apikey' => [
          'apikey' => $apikey,
          'name' => $newFriendlyName
        ]
      ];
    }else{
      return
        [
          'success' => false,
          'errorcode' => 302882
        ];
    }
  }

  public function regenerateApiKey(string $user_id, string $apikey)
  {
    $this->userid = $user_id;
    $old_token = $apikey;

    $prepareStatement = pg_prepare($dbconn, "regen_apikey", "UPDATE tokens SET token = $1 WHERE token = $2 AND user_id = $3");

    $new_token = Uuid::uuid4();
    $new_token = $new_token->toString();

    $executePreparedStatement = pg_execute($dbconn, "regen_apikey", array($new_token, $old_token, $this->userid));

    if($prepareStatement !== false && $executePreparedStatement !== false)
    {
      return
        [
           'success' => true,
           'apikey' => [
             'updated' => true,
             'new_value' => $new_token
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
      $this->sentry_instance->log_error('There was an oopsie. Check logs (ln 116) Time: ' . gmdate("Y-m-d H:i:s", time()));
    }
  }
}
