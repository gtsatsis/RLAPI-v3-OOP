<?php
namespace RLME;

include_once '../vendor/autoload.php';

use RLME\Utils\Sentry;
use RLME\Models\User;
use RLME\Models\Apikeys;

class Uploader
{
  public $bucket;
  public $filename;
  public $sentry_instance;

  public function __construct($bucket='owoapi')
  {
    include '../inc/development_db_password.inc.php';
    $this->sentry_instance = new SentryInstance();
  }

  public function upload($apikey, $files)
  {

    if(empty($apikey)){
      return [
        'success' => false,
        'error' => [
          'error_code' => 10000,
          'error_message' => 'You have not included an API key in your request.'
        ]
      ];
    }

    if(empty($files)){ // If there are no files uploaded, give a generic error.
      return [
        'success' => false,
        'error' => [
          'error_code' => 10001,
          'error_message' => 'Your POST request did not include a file in the files[] parameter'
        ]
      ];
    }

    
  }
}

?>
