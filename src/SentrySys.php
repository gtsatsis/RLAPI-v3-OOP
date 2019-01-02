<?php
namespace RLME;

/*
* @name SentrySys.php
* @desc Simple class that uses an API key to interact with sentry
* @author Sxribe
* @date 1/2/2019
*/

require_once '../vendor/autoload.php';
class SentryInstance
{
    public function __construct()
    {
        include '../inc/development_sentry_dsn.inc.php';
        $client = new Raven_Client($dsn);
        $errorHandler = new Raven_ErrorHandler($client);
        $this->client = $client;
        $this->errorHandler = $errorHandler;
    }
    public function log_error(string $error)
    {
        // @name log_error
        // @param $error <string> error

        $this->client->captureMessage($error);
        return $this->client->getlastEventId();
    }
}

?>
