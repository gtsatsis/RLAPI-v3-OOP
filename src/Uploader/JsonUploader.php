<?php

namespace App\Uploader;

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
                $user_id = pg_fetch_array(pg_execute($this->dbconn, 'get_user_by_api_key', array($api_key)));

                pg_prepare($this->dbconn, 'upload_json', 'INSERT INTO json_uploads (user_id, api_key, id, url, json, timestamp) VALUES ($1, $2, $3, $4, $5, $6)');
                pg_execute($this->dbconn, 'upload_json', array($user_id['user_id'], $api_key, $id, $url, $json, time()));

                return [
                    'success' => true,
                    'upload' => [
                        'id' => $id,
                        'url' => $url,
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

    public function update($api_key, $json_id, $json)
    {
        if ($this->authentication->upload_authentication($api_key)) {
            $json_array = json_decode($json);

            if (JSON_ERROR_NONE == json_last_error() && strlen($json) < getenv('JSON_UPLOADER_MAXCHARS')) {
                pg_prepare($this->dbconn, 'get_user_by_api_key', 'SELECT user_id FROM tokens WHERE token = $1');
                $user_id = pg_fetch_array(pg_execute($this->dbconn, 'get_user_by_api_key', array($api_key)));

                if ($this->owns_json($user_id[0], $json_id)) {
                    pg_prepare($this->dbconn, 'update_json', 'UPDATE json_uploads SET json = $1 WHERE id = $2');
                    pg_execute($this->dbconn, 'update_json', array($json, $json_id));

                    $url = '~json.'.$json_id;

                    return [
                        'success' => true,
                        'json_object' => [
                            $json_id => [
                                'updated' => true,
                                'url' => $url,
                            ],
                        ],
                    ];
                } else {
                    return [
                        'success' => false,
                        'error_message' => 'unauthorized',
                    ];
                }
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

    public function delete($api_key, $json_id)
    {
        pg_prepare($this->dbconn, 'get_user_by_api_key', 'SELECT user_id FROM tokens WHERE token = $1');
        $user_id = pg_fetch_array(pg_execute($this->dbconn, 'get_user_by_api_key', array($api_key)));
        
        if($this->owns_json($user_id[0])){
            pg_prepare($this->dbconn, 'delete_json_object', 'DELETE FROM json_uploads WHERE id = $1');
            pg_execute($this->dbconn, 'delete_json_object', array($json_id));

            return [
                'success' => true,
            ];
        }else{
            return [
                'success' => false,
                'error_message' => 'unauthorized',
            ];
        }
    }

    public function owns_json($user_id, $json_id)
    {
        pg_prepare($this->dbconn, 'owns_json', 'SELECT COUNT(*) FROM json_uploads WHERE user_id = $1 AND id = $2');
        $owns_json = pg_fetch_array(pg_execute($this->dbconn, 'owns_json', array($user_id, $json_id)));

        if (1 == $owns_json[0]) {
            return true;
        } else {
            return false;
        }
    }
}
