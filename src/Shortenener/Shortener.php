<?php

namespace App\Uploader;

use App\Models\User;
use App\Utils\Auth;
use App\Utils\FileUtils;
use Symfony\Component\Dotenv\Dotenv;
use Ramsey\Uuid\Uuid;

class Shortener
{
    private $dbconn;

    private $authentication;

    public function __construct()
    {
        /* Load the env file */
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__.'/../../.env');

        /* Connect to database */
        $this->dbconn = pg_connect('host='.getenv('DB_HOST').' port=5432 dbname='.getenv('DB_NAME').' user='.getenv('DB_USERNAME').' password='.getenv('DB_PASSWORD'));

        $this->authentication = new Auth();
    }

    public function shorten($api_key, $url){

        $authentication = $this->authentication->shorten_authentication($api_key);

        if ($authentication) {

            $parsed_url = parse_url($url);

            if(!$this->url_is_safe($parsed_url['host'])){
                $url = [
                    'url' => $url,
                    'safe' => false,
                ];
            }else{
                $url = [
                    'url' => $url,
                    'safe' => true,
                ];
            }


            $id = Uuid::uuid4();
            $id = $id->toString();

            /* Not done */

            
        }

    }
}

?>
