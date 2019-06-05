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
        $total_users = pg_query($this->dbconn, 'SELECT COUNT(*) FROM users');
        $upgraded_users = pg_query($this->dbconn, 'SELECT COUNT(*) FROM users WHERE tier NOT \'free\'');

        $total_files = pg_query($this->dbconn, 'SELECT COUNT(*) FROM files');

        $stats_array = [
            'users' => [
                'active' => $active_users,
                'total' => $total_users,
                'upgraded_users' => $upgraded_users,
            ],
            'files' => $total_files,
        ];

        return $stats_array;
    }
}
