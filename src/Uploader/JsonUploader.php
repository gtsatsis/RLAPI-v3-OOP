<?php

namespace App\Uploader;

use App\Models\User;
use App\Utils\Auth;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Dotenv\Dotenv;

class JsonUploader
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

    public function upload($api_key, $json)
    {
        if ($this->authentication->upload_authentication($api_key)) {
            $json_array = json_decode($json);

            if (JSON_ERROR_NONE == json_last_error() && strlen($json) < getenv('JSON_UPLOADER_MAXCHARS')) {
                $id = Uuid::uuid4();
                $id = $id->toString();
                $url = '~json.'.$id;

                pg_prepare($this->dbconn, 'get_user_by_api_key', 'SELECT user_id FROM tokens WHERE token = $1');
                $user_id = pg_fetch_array(pg_execute($this->dbconn, 'get_user_by_api_key'));

                pg_prepare($this->dbconn, 'upload_json', 'INSERT INTO json_uploads (user_id, api_key, id, url, json, timestamp) VALUES ($1, $2, $3, $4, $5, $6)');
                pg_execute($this->dbconn, 'upload_json', array($user_id['user_id'], $api_key, $id, $url, $json, time()));

                return [
                    'success' => true,
                    'upload' => [
                        'id' => $id,
                        'url' => $id,
                    ],
                ];
            } else {
                return [
                    'success' => false,
                    'error_message' => 'invalid_json',
                ];
            }
        } else {
            return [
                'success' => false,
                'error_message' => 'unauthorized',
            ];
        }
    }
}
