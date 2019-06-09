<?php

namespace App\Models;

require_once __DIR__.'/../../vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
use App\Utils\Auth;
use App\Utils\SqreenLib;

class Stats
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

    public function getStats()
    {
        $active_users = pg_query($this->dbconn, 'SELECT COUNT(*) FROM users WHERE verified = true AND is_blocked IS NOT true');
        $active_users = pg_fetch_array($active_users);

        $upgraded_users = pg_query($this->dbconn, 'SELECT COUNT(*) FROM users WHERE tier NOT IN (\'free\')');
        $upgraded_users = pg_fetch_array($upgraded_users);

        $total_users = pg_query($this->dbconn, 'SELECT COUNT(*) FROM users');
        $total_users = pg_fetch_array($total_users);

        $total_files = pg_query($this->dbconn, 'SELECT COUNT(*) FROM files');
        $total_files = pg_fetch_array($total_files);

        $active_promos = pg_query($this->dbconn, 'SELECT COUNT(*) FROM promo_codes WHERE expired = false');
        $active_promos = pg_fetch_array($active_promos);

        $total_promos = pg_query($this->dbconn, 'SELECT COUNT(*) FROM promo_codes');
        $total_promos = pg_fetch_array($total_promos);

        $total_domains = pg_query($this->dbconn, 'SELECT COUNT(*) FROM domains');
        $total_domains = pg_fetch_array($total_domains);

        $stats_array = [
            'users' => [
                'active' => (int) $active_users[0],
                'upgraded' => (int) $upgraded_users[0],
                'total' => (int) $total_users[0],
            ],
            'files' => (int) $total_files[0],
            'promos' => [
                'active' => (int) $active_promos[0],
                'total' => (int) $total_promos[0],
            ],
            'domains' => (int) $total_domains[0],
        ];

        return $stats_array;
    }
}
