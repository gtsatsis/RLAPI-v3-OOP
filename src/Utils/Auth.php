<?php

namespace App\Utils;

require_once __DIR__.'/../../vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

class Auth
{
    private $dbconn;

    private $sqreen;

    public function __construct()
    {
        /* Load the env file */
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__.'/../../.env');

        /* Connect to database */
        $this->dbconn = pg_connect('host='.getenv('DB_HOST').' port=5432 dbname='.getenv('DB_NAME').' user='.getenv('DB_USERNAME').' password='.getenv('DB_PASSWORD'));

        $this->sqreen = new SqreenLib();
    }

    public function validate_password(string $user_id, string $password)
    {
        pg_prepare($this->dbconn, 'get_user', 'SELECT * FROM users WHERE id = $1');
        $execute_prepared_statement = pg_execute($this->dbconn, 'get_user', array($user_id));

        $user = pg_fetch_array($execute_prepared_statement);

        if (password_verify($password, $user['password']) && 't' == $user['verified']) {
            $this->sqreen->sqreen_auth_track(true, $user['email']);

            return true;
        } else {
            $this->sqreen->sqreen_auth_track(false, $user['email']);

            return false;
        }
    }

    public function maximum_filesize_assessment(string $api_key, string $file_size)
    {
        pg_prepare($this->dbconn, 'get_user_tier', 'SELECT tier FROM users WHERE id = (SELECT user_id FROM tokens WHERE token = $1)');
        $execute_prepared_statement = pg_execute($this->dbconn, 'get_user_tier', array($api_key));

        if ($execute_prepared_statement) {
            $user_tier = pg_fetch_array($execute_prepared_statement);
            $tier = $user_tier[0];

            pg_prepare($this->dbconn, 'get_tier_from_tiers', 'SELECT * FROM tiers WHERE tier = $1');
            $execute_prepared_statement = pg_execute($this->dbconn, 'get_tier_from_tiers', array($tier));

            if ($execute_prepared_statement) {
                $tier_info = pg_fetch_array($execute_prepared_statement);
                $max_allowed_file_size = $tier_info['maximum_filesize'];

                if ($file_size > $max_allowed_file_size) {
                    return false;
                } else {
                    return true;
                }
            } else {
                throw new \Exception('Failed to get tier from tiers');
            }
        } else {
            throw new \Exception('Uh.. no idea how this fucking shit happened... It should only be called if the upload auth passed... nanitf');
        }
    }

    public function bucket_allowance(string $api_key)
    {
        pg_prepare($this->dbconn, 'get_bucket_allowance', 'SELECT bucket_limit FROM tiers WHERE tier = (SELECT tier FROM users WHERE id = (SELECT user_id FROM tokens WHERE token = $1))');

        $execute_prepared_statement = pg_execute($this->dbconn, 'get_bucket_allowance', array($api_key));
        $bucket_allowance = pg_fetch_array($execute_prepared_statement);

        pg_prepare($this->dbconn, 'get_current_buckets', 'SELECT COUNT(*) FROM buckets WHERE user_id = (SELECT user_id FROM tokens WHERE token = $1)');
        $execute_prepared_statement = pg_execute($this->dbconn, 'get_current_buckets', array($api_key));

        $current_buckets = pg_fetch_array($execute_prepared_statement);

        if ($bucket_allowance[0] >= $current_buckets[0]) {
            return false;
        } else {
            return true;
        }
    }

    public function domain_allowance(string $user_id)
    {
        pg_prepare($this->dbconn, 'get_domain_allowance', 'SELECT private_domains FROM tiers WHERE tier = (SELECT tier FROM users WHERE id = $1)');

        $execute_prepared_statement = pg_execute($this->dbconn, 'get_domain_allowance', array($user_id));
        $domain_allowance = pg_fetch_array($execute_prepared_statement);

        pg_prepare($this->dbconn, 'get_current_domains', 'SELECT COUNT(*) FROM domains WHERE user_id = $1');
        $execute_prepared_statement = pg_execute($this->dbconn, 'get_current_domains', array($user_id));

        $current_domains = pg_fetch_array($execute_prepared_statement);

        if ($domain_allowance[0] >= $current_domains[0]) {
            return false;
        } else {
            return true;
        }
    }

    public function user_api_key_allowance(string $user_id)
    {
        pg_prepare($this->dbconn, 'get_api_key_allowance', 'SELECT api_keys FROM tiers WHERE tier = (SELECT tier FROM users WHERE id = $1)');
        $execute_prepared_statement = pg_execute($this->dbconn, 'get_api_key_allowance', array($user_id));
        $api_key_allownace = pg_fetch_array($execute_prepared_statement);

        pg_prepare($this->dbconn, 'get_current_api_keys', 'SELECT COUNT(*) FROM tokens WHERE user_id = $1');
        $execute_prepared_statement = pg_execute($this->dbconn, 'get_current_api_keys', array($user_id));

        $current_api_keys = pg_fetch_array($execute_prepared_statement);

        if ($current_api_keys[0] >= $api_key_allownace[0]) {
            return false;
        } else {
            return true;
        }
    }

    public function captcha_verify($recaptcha)
    {
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('secret' => getenv('RECAPTCHA_SECRET'), 'response' => $recaptcha));

        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response);

        return $data->success;
    }

    /* Begin Upload Authentication Function */

    public function upload_authentication(string $api_key)
    {
        pg_prepare($this->dbconn, 'get_user_by_api_key_2', 'SELECT * FROM users WHERE id = (SELECT user_id FROM tokens WHERE token = $1 LIMIT 1)');
        $execute_prepared_statement = pg_execute($this->dbconn, 'get_user_by_api_key_2', array($api_key));

        $user = pg_fetch_array($execute_prepared_statement);

        if (null != $user) {
            if ('t' == $user['verified']) {
                if ('f' == $user['is_blocked'] || empty($user['is_blocked'])) {
                    $this->sqreen->sqreen_auth_track(true, $user['email']);
                    $this->sqreen->sqreen_track_upload($user['id']);

                    return true;
                } else {
                    $this->sqreen->sqreen_auth_track(false, $user['email']);

                    return false;
                }
            } else {
                $this->sqreen->sqreen_auth_track(false, $user['email']);

                return false;
            }
        } else {
            return false;
        }
    }

    /* End Upload Authentication Function */

    public function shorten_authentication(string $api_key)
    {
        pg_prepare($this->dbconn, 'get_user_by_api_key_2', 'SELECT * FROM users WHERE id = (SELECT user_id FROM tokens WHERE token = $1 LIMIT 1)');
        $execute_prepared_statement = pg_execute($this->dbconn, 'get_user_by_api_key_2', array($api_key));

        $user = pg_fetch_array($execute_prepared_statement);

        if (null != $user) {
            if ('t' == $user['verified']) {
                if ('f' == $user['is_blocked'] || empty($user['is_blocked'])) {
                    $this->sqreen->sqreen_auth_track(true, $user['email']);
                    $this->sqreen->sqreen_track_shorten($user['id']);

                    return true;
                } else {
                    $this->sqreen->sqreen_auth_track(false, $user['email']);

                    return false;
                }
            } else {
                $this->sqreen->sqreen_auth_track(false, $user['email']);

                return false;
            }
        } else {
            return false;
        }
    }

    public function api_key_is_admin(string $api_key)
    {
        pg_prepare($this->dbconn, 'api_key_is_admin', 'SELECT is_admin FROM users WHERE id = (SELECT user_id FROM tokens WHERE token = $1)');
        $execute_prepared_statement = pg_execute($this->dbconn, 'api_key_is_admin', array($api_key));

        if ($execute_prepared_statement) {
            $is_admin = pg_fetch_array($execute_prepared_statement);

            if ('f' == $is_admin || empty($is_admin)) {
                return false;
            } else {
                return true;
            }
        }
    }

    public function owns_bucket(string $user_id, $bucket_id)
    {
        pg_prepare($this->dbconn, 'owns_bucket', 'SELECT COUNT(*) FROM buckets WHERE user_id = $1 AND id = $2');
        $execute_prepared_statement = pg_execute($this->dbconn, 'owns_bucket', array($user_id, $bucket_id));

        $count = pg_fetch_array($execute_prepared_statement);
        if (1 == $count[0]) {
            return true;
        } else {
            return false;
        }
    }

    public function owns_bucket_by_name_api_key(string $api_key, $bucket)
    {
        pg_prepare($this->dbconn, 'owns_bucket', 'SELECT COUNT(*) FROM buckets WHERE api_key = $1 AND bucket = $2');
        $execute_prepared_statement = pg_execute($this->dbconn, 'owns_bucket', array($api_key, $bucket));

        $count = pg_fetch_array($execute_prepared_statement);
        if (1 == $count[0]) {
            return true;
        } else {
            return false;
        }
    }

    public function isValidUUID($uuid)
    {
        if (!is_string($uuid) || (1 !== preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid))) {
            return false;
        }

        return true;
    }

    public function is_domain_owner($api_key, $domain)
    {
        pg_prepare($this->dbconn, 'get_domain_owner', 'SELECT user_id FROM domains WHERE domain_name = $1');
        $user_id = pg_fetch_array(pg_execute($this->dbconn, 'get_domain_owner', array($domain)));

        pg_prepare($this->dbconn, 'get_user_by_api_key', 'SELECT user_id FROM tokens WHERE token = $1');
        $api_key_owner = pg_fetch_array(pg_execute($this->dbconn, 'get_user_by_api_key', array($api_key)));

        if ($user_id[0] == $api_key_owner[0] && !empty($user_id[0]) && !empty($api_key_owner[0])) {
            return true;
        } else {
            return false;
        }
    }

    public function upload_to_cb_allowed($api_key, $bucket)
    {
        pg_prepare($this->dbconn, 'fetch_user_upload_to_cb', 'SELECT * FROM users WHERE is_blocked = false AND id = (SELECT user_id FROM tokens WHERE token = $1)');
        $user = pg_fetch_array(pg_execute($this->dbconn, 'fetch_user_upload_to_cb', array($api_key)));

        pg_prepare($this->dbconn, 'fetch_bucket_data', 'SELECT data FROM buckets WHERE bucket = $1');
        $bucket_data = pg_fetch_array(pg_execute($this->dbconn, 'fetch_bucket_data', array($bucket)));
        $bucket_data = json_decode($bucket_data[0]);

        if (array_key_exists($user['id'], $bucket_data['users'])) {
            if (true == $bucket_data['users'][$user['id']]['rlapi.custom.bucket.upload']) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
