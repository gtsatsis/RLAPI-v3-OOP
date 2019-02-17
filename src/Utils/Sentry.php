<?php
namespace App\Utils;

/*
* @name SentrySys.php
* @desc Simple class that uses an API key to interact with sentry
* @author Sxribe
* @date 1/2/2019
*/

use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__ . '/../../vendor/autoload.php';

class Sentry
{
    public function __construct()
    {
        /* Load the env file */
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__.'/../../.env');
        
        $client = new Raven_Client(getenv('SENTRY_DSN'));
        $errorHandler = new Raven_ErrorHandler($client);
    }
    public function log_error(string $error)
    {
        // @name log_error
        // @param $error <string> error

        $client->captureMessage($error);
        return $client->getlastEventId();
    }
}

?>
