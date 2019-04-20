<?php

namespace App\Models;

require_once __DIR__.'/../../vendor/autoload.php';

use App\Utils\Auth;
use App\Utils\SqreenLib;
use Symfony\Component\Dotenv\Dotenv;

class Domains
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

    public function list_domains()
    {
        return pg_fetch_all(pg_query($this->dbconn, 'SELECT * FROM domains'));
    }
}
