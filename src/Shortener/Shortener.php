<?php

namespace App\Shortener;

use App\Models\User;
use App\Utils\Auth;
use App\Utils\FileUtils;
use Symfony\Component\Dotenv\Dotenv;
use Ramsey\Uuid\Uuid;

class Shortener
{
    private $dbconn;

    private $authentication;

    private $util;

    public function __construct()
    {
        /* Load the env file */
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__.'/../../.env');

        /* Connect to database */
        $this->dbconn = pg_connect('host='.getenv('DB_HOST').' port=5432 dbname='.getenv('DB_NAME').' user='.getenv('DB_USERNAME').' password='.getenv('DB_PASSWORD'));

        $this->authentication = new Auth();

        $this->util = new FileUtils();
    }

    public function url_is_safe($domain)
    {
        $statement = pg_prepare($this->dbconn, 'check_banned_domain', 'SELECT COUNT(*) FROM banned_domains_short WHERE domain = $1');
        $executePreparedStatement = pg_execute($this->dbconn, 'check_banned_domain', array($domain));

        $result = pg_fetch_array($executePreparedStatement);

        if (0 == $result[0]) {
            return 'true';
        }

        return 'false';
    }

    public function shorten($api_key, $url, $custom_ending = null, $domain = 'https://ratelimited.me')
    {
        $authentication = $this->authentication->shorten_authentication($api_key);

        if ($authentication) {
            $parsed_url = parse_url($url);

            if ('false' == $this->url_is_safe($parsed_url['host'])) {
                $url = [
                    'url' => $url,
                    'safe' => false,
                ];
            } else {
                $url = [
                    'url' => $url,
                    'safe' => true,
                ];
            }

            $id = Uuid::uuid4();
            $id = $id->toString();

            $short_name = $this->util->generateShortName();
            $short_name_is_unique = false;

            while (!$short_name_is_unique) {
                if ($this->util->isShortUnique($short_name)) {
                    $short_name_is_unique = true;
                } else {
                    $short_name_is_unique = false;
                    $short_name = $this->util->generateShortName();
                }
            }
            if (!empty($custom_ending)) {
                $short_name = $short_name.$custom_ending;
            }

            $this->util->log_short($api_key, $id, $short_name, $url['url'], $url['safe']);

            return [
                'action' => 'shorten',
                'result' => $domain.'/'.$short_name,
            ];
        } else {
            return [
                    'success' => false,
                    'error_message' => 'Invalid Credentials',
            ];
        }
    }

    public function lookup($short_name)
    {
        $statement = pg_prepare($this->dbconn, 'look_up_short_url', 'SELECT url FROM shortened_urls WHERE short_name = $1');
        $executePreparedStatement = pg_execute($this->dbconn, 'look_up_short_url', array($short_name));
        $result = pg_fetch_array($executePreparedStatement);

        return [
            'action' => 'lookup',
            'result' => $result[0],
        ];
    }
}
