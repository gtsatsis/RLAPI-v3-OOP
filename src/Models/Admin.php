<?php

namespace App\Models;

require_once __DIR__.'/../../vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
use App\Utils\Auth;
use App\Utils\SqreenLib;

class Admin
{
    private $dbconn;

    private $authentication;

    private $sqreen;

    public function __construct()
    {
        /* Load the env file */
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__.'/../../.env');

        /* Connect to database */
        $this->dbconn = pg_connect('host='.getenv('DB_HOST').' port=5432 dbname='.getenv('DB_NAME').' user='.getenv('DB_USERNAME').' password='.getenv('DB_PASSWORD'));

        $this->authentication = new Auth();

        $this->sqreen = new SqreenLib();
    }

    public function delete_user($api_key, $password, $email, $user_id)
    {
        if ($this->authentication->api_key_is_admin($api_key)) {
            pg_prepare($this->dbconn, 'admin_delete_user_get_admin', 'SELECT user_id FROM tokens WHERE token = $1');
            $execute_prepared_statement = pg_execute($this->dbconn, 'admin_delete_user_get_admin', array($api_key));

            $user = pg_fetch_array($execute_prepared_statement);

            if ($this->authentication->validate_password($user['user_id'], $password)) {
                /* User Deletion */
                pg_prepare($this->dbconn, 'delete_user', 'DELETE FROM users WHERE id = $1 AND email = $2');
                $execute_prepared_statement = pg_execute($this->dbconn, 'delete_user', array($user_id, $email));
                if ($execute_prepared_statement) {
                    $this->sqreen->sqreen_track_user_deletion();

                    /* Api key deletion */
                    pg_prepare($this->dbconn, 'delete_user_api_keys', 'DELETE FROM tokens WHERE user_id = $1');
                    $execute_prepared_statement = pg_execute($this->dbconn, 'delete_user_api_keys', array($user_id));

                    if ($execute_prepared_statement) {
                        return [
                            'success' => true,
                        ];
                    } else {
                        throw new \Exception('Error Processing delete_user Request: Apikeys Deletion');
                    }
                } else {
                    throw new \Exception('Error Processing delete_user Request: Userdata Deletion');
                }
            } else {
                return [
                    'success' => false,
                    'error_message' => 'access_denied',
                ];
            }
        } else {
            return [
                    'success' => false,
                    'error_message' => 'access_denied',
            ];
        }
    }

    public function password_reset_all_migration($api_key, $password)
    {
        if ($this->authentication->api_key_is_admin($api_key)) {
            pg_prepare($this->dbconn, 'get_user_pass_reset_migration', 'SELECT user_id FROM tokens WHERE token = $1');
            $execute_prepared_statement = pg_execute($this->dbconn, 'get_user_pass_reset_migration', array($api_key));

            $user = pg_fetch_array($execute_prepared_statement);

            if ($this->authentication->validate_password($user['user_id'], $password)) {
                $execute_statement = pg_query($this->dbconn, 'SELECT email FROM users WHERE password IS NULL');

                $users = pg_fetch_all($execute_statement);

                $user = new User();

                foreach ($users as $users_array) {
                    $user->reset_password_send($users_array['email']);
                }

                return [
                    'success' => true,
                ];
            } else {
                return [
                    'success' => false,
                    'error_message' => 'access_denied',
                ];
            }
        } else {
            return [
                'success' => false,
                'error_message' => 'access_denied',
            ];
        }
    }

    public function verify_all_emails_migration($api_key, $password)
    {
        if ($this->authentication->api_key_is_admin($api_key)) {
            pg_prepare($this->dbconn, 'get_user_verify_all_emails_migration', 'SELECT user_id FROM tokens WHERE token = $1');
            $execute_prepared_statement = pg_execute($this->dbconn, 'get_user_verify_all_emails_migration', array($api_key));

            $user = pg_fetch_array($execute_prepared_statement);

            if ($this->authentication->validate_password($user['user_id'], $password)) {
                $execute_statement = pg_query($this->dbconn, 'SELECT * FROM users WHERE verified = false');

                $users = pg_fetch_all($execute_statement);

                $user = new User();

                foreach ($users as $users_array) {
                    $user->user_send_verify_email($users_array['email'], $users_array['id'], $users_array['username']);
                }

                return [
                    'success' => true,
                ];
            } else {
                return [
                    'success' => false,
                    'error_message' => 'access_denied',
                ];
            }
        } else {
            return [
                'success' => false,
                'error_message' => 'access_denied',
            ];
        }
    }

    public function verify_user_emails($api_key, $password, $email)
    {
        if ($this->authentication->api_key_is_admin($api_key)) {
            pg_prepare($this->dbconn, 'verify_user_emails_get_user', 'SELECT user_id FROM tokens WHERE token = $1');
            $execute_prepared_statement = pg_execute($this->dbconn, 'verify_user_emails_get_user', array($api_key));

            $user = pg_fetch_array($execute_prepared_statement);

            if ($this->authentication->validate_password($user['user_id'], $password)) {
                pg_prepare($this->dbconn, 'verify_user_force', 'SELECT * FROM users WHERE verified = false AND email = $1');
                $execute_prepared_statement = pg_execute($this->dbconn, 'verify_user_force', array($email));

                $users = pg_fetch_array($execute_prepared_statement);

                $user = new User();

                $user->user_send_verify_email($users['email'], $users['id'], $users['username']);

                return [
                    'success' => true,
                ];
            } else {
                return [
                    'success' => false,
                    'error_message' => 'access_denied',
                ];
            }
        } else {
            return [
                'success' => false,
                'error_message' => 'access_denied',
            ];
        }
    }

    public function get_all_active_promos($api_key, $password)
    {
        if ($this->authentication->api_key_is_admin($api_key)) {
            pg_prepare($this->dbconn, 'verify_user_emails_get_user', 'SELECT user_id FROM tokens WHERE token = $1');
            $execute_prepared_statement = pg_execute($this->dbconn, 'verify_user_emails_get_user', array($api_key));

            $user = pg_fetch_array($execute_prepared_statement);

            if ($this->authentication->validate_password($user['user_id'], $password)) {
                $execute_statement = pg_query($this->dbconn, 'SELECT * FROM promo_codes WHERE expired = false');

                return pg_fetch_all($execute_statement);
            }
        }
    }
}
