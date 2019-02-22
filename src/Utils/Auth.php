<?php
namespace App\Utils;
require_once __DIR__ . '/../../vendor/autoload.php';

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Symfony\Component\Dotenv\Dotenv;

class Auth {

	private $dbconn;

	public function __construct(){

		/* Load the env file */
		$dotenv = new Dotenv();
		$dotenv->load(__DIR__.'/../../.env');

		/* Connect to database */
		$this->dbconn = pg_connect("host=" . getenv('DB_HOST') . " port=5432 dbname=" . getenv('DB_NAME') . " user=" . getenv('DB_USERNAME') . " password=" . getenv('DB_PASSWORD'));

	}

	public function validate_password($user_id, $password){

		pg_prepare($this->dbconn, "get_password", 'SELECT password FROM users WHERE id = $1');
		$execute_prepared_statement = pg_execute($this->dbconn, "get_password", array($id));
		$password_DB = pg_fetch_array($execute_prepared_statement);

		if(password_verify($password, $password_DB[0])){

			return true;
		
		}else{

			return false;
		
		}

	}

}

?>