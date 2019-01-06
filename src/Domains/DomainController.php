<?php
namespace RLME\Domains;
include_once '../vendor/autoload.php';
use RLME\Models\User;
uee RLME\Utils\Sentry;
class DomainController
{

  use Ramsey\Uuid\Uuid;
  use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
  use RLME\SentrySys;

  public $userid;
  public $username;
  public $password;
  public $domainName;
  public $domainDetails;
  public $is_admin;
  public $user_obj;

  public function __construct(string $username, string $password)
  {
    include '../inc/development_db_password.inc.php';
    $dbconn = pg_connect("host=localhost port=5432 dbname=rlapi_devel user=rlapi_devel password=" . $dbPass); //Note, $dbPass is defined in development_db_password.inc.php
    $this->sentry_instance = new SentryInstance();

    $enc_password = password_hash(htmlentities($password), PASSWORD_BCRYPT);
    unset($password);
    $this->username = htmlentities($username);
    $this->password = $enc_password;

    $this->user_obj = new User($this->username, $this->password);
    $this->userdata = $this->user->login(); // TODO: add this function;
    if($this->userdata->is_admin == true)
    {
      $this->is_admin = true;
    }
    else
    {
      $this->is_admin = false;
    }
  }

  public function addDomain($id, $domainname){
    if($this->is_admin)
    {
      $domainname = htmlspecialchars($domainname);
      $validationHash = md5($domainname);

          
      $prepareStatement = pg_prepare($dbconn, "add_domain", "INSERT INTO domains ('id', 'user_id', 'domainname', 'validated', 'validationhash', 'official', 'type', 'bucket', 'expirydate') VALUES ($1, $2, $3, false, $4, false, 'public', 'owoapi', $5)");
      $executePreparedStatement = pg_execute($dbconn, "add_domain", array($domainid, $userid, $domainname, $validationHash, $expiryDate));
    }
  }

}
