<?php
namespace RLME\Domains;
include_once '../../vendor/autoload.php';
class DomainController
{

  use Ramsey\Uuid\Uuid;
  use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
  use RLME\Utils\Sentry;
  use RLME\Domains\ExternalAPIFunctions;
  use RLME\Models\User;

  public $userid;
  public $username;
  public $password;
  public $domainName;
  public $domainDetails;
  public $is_admin;
  public $user_obj;

  public function __construct()
  {
    include '../../inc/development_db_password.inc.php';
    $this->sentry_instance = new SentryInstance();
  }

    public function addDomain($id, $domainname){
        $dr = new DomainRequest;

        $domainname = htmlspecialchars($domainname);
        $validationHash = md5($domainname);

        $expiryDate = $dr->getExpirationDate($domainname);
            
        $prepareStatement = pg_prepare($dbconn, "add_domain", "INSERT INTO domains ('id', 'user_id', 'domainname', 'validated', 'validationhash', 'official', 'type', 'bucket', 'expirydate') VALUES ($1, $2, $3, false, $4, false, 'public', 'owoapi', $5)");
        $executePreparedStatement = pg_execute($dbconn, "add_domain", array($domainid, $userid, $domainname, $validationHash, $expiryDate));
        if($prepareStatement !== false && $executePreparedStatement !== false)
        {
            return
            [
                'success' => true,
                'domain' => [
                'added' => true
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
            $this->sentry_instance->log_error('There was a domain addition oopsie. Check logs (ln 55) Time: ' . gmdate("Y-m-d H:i:s", time()));
        }
    }   
    public function removeDomain($domainname){
        $domainname = htmlspecialchars($domainname);
        $validationHash = md5($domainname);
            
        $prepareStatement = pg_prepare($dbconn, "remove_domain", "DELETE FROM domains WHERE domainname = $1 AND validationhash = $2");
        $executePreparedStatement = pg_execute($dbconn, "remove_domain", array($domainname, $validationHash));
        if($prepareStatement !== false && $executePreparedStatement !== false)
        {
            return
            [
                'success' => true,
                'domain' => [
                    'removed' => true
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
            $this->sentry_instance->log_error('There was a domain addition oopsie. Check logs (ln 81) Time: ' . gmdate("Y-m-d H:i:s", time()));
        }
    }

}
