<?php

namespace App\Models;

require_once __DIR__.'/../../vendor/autoload.php';

use App\Utils\Auth;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Dotenv\Dotenv;

class Apikeys
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

    /* Begin API Key Creation Function */

    public function create_user_api_key(string $user_id, string $api_key_name, string $password)
    {
        if ($this->authentication->validate_password($user_id, $password)) {
            if ($this->authentication->user_api_key_allowance($user_id)) {
                $api_key = $this->generate_api_key();

                pg_prepare($this->dbconn, 'insert_api_key', 'INSERT INTO tokens (user_id, token, name) VALUES ($1, $2, $3)');
                $execute_prepared_statement = pg_execute($this->dbconn, 'insert_api_key', array($user_id, $api_key, $api_key_name));

                if ($execute_prepared_statement) {
                    return [
                        'success' => true,
                        'api_key' => [
                            'created' => true,
                            'key' => $api_key,
                        ],
                    ];
                } else {
                    throw new \Exception('Error Processing create_user_api_key Request');
                }
            } else {
                return [
                    'success' => false,
                    'error_code' => 101010,
                    'error_message' => 'maximum_allowed_keys_reached',
                ];
            }
        } else {
            return [
                'success' => false,
                'error_code' => 1002,
                'error_message' => 'invalid_user_id_or_password',
            ];
        }
    }

    /* End API Key Creation Function */

    /* Begin API Key Deletion Function */

    public function delete_user_api_key(string $user_id, string $api_key, string $password)
    {
        if ($this->authentication->validate_password($user_id, $password)) {
            pg_prepare($this->dbconn, 'delete_api_key', 'DELETE FROM tokens WHERE user_id = $1 AND token = $2');
            $execute_prepared_statement = pg_execute($this->dbconn, 'delete_api_key', array($user_id, $api_key));

            if ($execute_prepared_statement) {
                return [
                    'success' => true,
                ];
            } else {
                throw new \Exception('Error Processing delete_user_api_key Request');
            }
        } else {
            return [
                'success' => false,
                'error_code' => 1002,
                'error_message' => 'invalid_user_id_or_password',
            ];
        }
    }

    /* End API Key Deletion Function */

    /* Begin API Key Rename Function */

    public function rename_user_api_key(string $user_id, string $api_key, string $api_key_name, string $password)
    {
        if ($this->authentication->validate_password($user_id, $password)) {
            pg_prepare($this->dbconn, 'rename_api_key', 'UPDATE tokens SET name = $1 WHERE token = $2');
            $execute_prepared_statement = pg_execute($this->dbconn, 'rename_api_key', array($api_key_name, $api_key));

            if ($execute_prepared_statement) {
                return [
                    'success' => true,
                    'api_key' => [
                        'name' => $api_key_name,
                    ],
                ];
            } else {
                throw new \Exception('Error Processing rename_user_api_key Request');
            }
        } else {
            return [
                'success' => false,
                'error_code' => 1002,
                'error_message' => 'invalid_user_id_or_password',
            ];
        }
    }

    /* End API Key Rename Function */

    /* Begin API Key Regeneration Function */

    public function regenerate_user_api_key(string $user_id, string $api_key, string $password)
    {
        if ($this->authentication->validate_password($user_id, $password)) {
            $get_api_key_exists = $this->get_api_key_exists($api_key);

            if ($api_key == $get_api_key_exists) {
                $new_api_key = $this->generate_api_key();

                pg_prepare($this->dbconn, 'regen_api_key', 'UPDATE tokens SET token = $1 WHERE token = $2 AND user_id = $3');
                $execute_prepared_statement = pg_execute($this->dbconn, 'regen_api_key', array($new_api_key, $api_key, $user_id));

                if ($execute_prepared_statement) {
                    return [
                        'success' => true,
                        'api_key' => [
                            'updated' => true,
                            'api_key' => $new_api_key,
                        ],
                    ];
                }
            }
        } else {
            return [
                'success' => false,
                'error_code' => 1002,
                'error_message' => 'invalid_user_id_or_password',
            ];
        }
    }

    /* End API Key Regeneration Function */

    /* Begin API Key Generation Function */

    public function generate_api_key()
    {
        $unique = false;
        while (!$unique) {
            $api_key = Uuid::uuid4();
            $api_key = $api_key->toString();

            pg_prepare($this->dbconn, 'check_if_api_key_exists', 'SELECT * FROM tokens WHERE token = $1');
            $execute_prepared_statement = pg_execute($this->dbconn, 'check_if_api_key_exists', array($api_key));

            $number_of_rows = pg_num_rows($execute_prepared_statement);

            if (0 == $number_of_rows) {
                $unique = true;
            }
        }

        return $api_key;
    }

    /* End API Key Generation Function */

    /* Begin Get Api Key Exists Function */

    public function get_api_key_exists($api_key)
    {
        pg_prepare($this->dbconn, 'api_key_exists', 'SELECT token FROM tokens WHERE token = $1');
        $execute_prepared_statement = pg_execute($this->dbconn, 'api_key_exists', array($api_key));
        $api_key_exists = pg_fetch_array($execute_prepared_statement);

        return $api_key_exists[0];
    }

    /* Begin API Key Creation Function */

    public function create_user_api_key_email_auth(string $email, string $api_key_name, string $password)
    {
        pg_prepare($this->dbconn, 'get_user_id_api_key_create_email_auth', 'SELECT id FROM users WHERE email = $1');
        $execute_prepared_statement = pg_execute($this->dbconn, 'get_user_id_api_key_create_email_auth', array($email));

        $user_info = pg_fetch_array($execute_prepared_statement);
        $user_id = $user_info['id'];

        if ($this->authentication->validate_password($user_id, $password)) {
            if ($this->authentication->user_api_key_allowance($user_id)) {
                $api_key = $this->generate_api_key();

                pg_prepare($this->dbconn, 'insert_api_key', 'INSERT INTO tokens (user_id, token, name) VALUES ($1, $2, $3)');
                $execute_prepared_statement = pg_execute($this->dbconn, 'insert_api_key', array($user_id, $api_key, $api_key_name));

                if ($execute_prepared_statement) {
                    return [
                        'success' => true,
                        'api_key' => [
                            'created' => true,
                            'key' => $api_key,
                        ],
                    ];
                } else {
                    throw new \Exception('Error Processing create_user_api_key Request');
                }
            } else {
                return [
                    'success' => false,
                    'error_code' => 101010,
                    'error_message' => 'maximum_allowed_keys_reached',
                ];
            }
        } else {
            return [
                'success' => false,
                'error_code' => 1002,
                'error_message' => 'invalid_user_id_or_password',
            ];
        }
    }

    public function generate_sharex_config($api_key, $domain){
        return [
            'Version' => '12.4.1',
            'Name' => 'ratelimited.me - Free API: '.$api_key,
            'DestinationType' => 'ImageUploader, TextUploader, FileUploader',
            'RequestMethod' => 'POST',
            'RequestURL' => 'https://api.ratelimited.me/upload/pomf',
            'Parameters' => [
                'key' => $api_key
            ],
            'Body' => 'MultipartFormData',
            'FileFormName' => 'files[]',
            'URL' => $domain.'$json:files[0].url$'
        ];
    }
}
