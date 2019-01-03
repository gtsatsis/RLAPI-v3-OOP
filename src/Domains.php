<?php
namespace RLME;
include_once '../vendor/autoload.php';
class Domains
{

  use Ramsey\Uuid\Uuid;
  use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
  use RLME\SentrySys;

  public $userid;
  public $domainName;
  public $domainDetails;

  public function __construct()
  {
    include '../inc/development_db_password.inc.php';
    $dbconn = pg_connect("host=localhost port=5432 dbname=rlapi_devel user=rlapi_devel password=" . $dbPass); //Note, $dbPass is defined in development_db_password.inc.php
    $this->sentry_instance = new SentryInstance();
  }

  public function addDomain($id, $domainname){
    $domainname = htmlspecialchars($domainname);
    $validationHash = md5($domainname);
    
    $prepareStatement = pg_prepare($dbconn, "add_domain", "INSERT INTO domains ('id', 'user_id', 'domainname', 'validated', 'validationhash', 'type', 'bucket', 'expirydate') VALUES ($1, $2, $3, false, $4, 'public', 'owoapi', $5)");
    $executePreparedStatement = pg_execute($dbconn, "add_domain", array($domainid, $userid, $domainname, $validationHash, $expiryDate));


  }

}
