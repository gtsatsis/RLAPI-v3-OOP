<?php
namespace App\Utils;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Symfony\Component\Dotenv\Dotenv;

class Getters {

	private $dbconn;
	private $prepared;

	public function __construct(){

		/* Load the env file */
		$dotenv = new Dotenv();
		$dotenv->load(__DIR__.'/../../.env');

		/* Connect to database */
		$this->dbconn = pg_connect("host=" . getenv('DB_HOST') . " port=5432 dbname=" . getenv('DB_NAME') . " user=" . getenv('DB_USERNAME') . " password=" . getenv('DB_PASSWORD'));

	}

	public function check_if_user_exists(string $username, string $user_email){

		pg_prepare($this->dbconn, "check_if_user_exists", "SELECT COUNT(*) FROM users WHERE username = $1 OR email = $2");
		$execute_prepared_statement = pg_execute($this->dbconn, "check_if_user_exists", array($username, $user_email));

		$result = pg_fetch_array($execute_prepared_statement);

		if($result[0] == 0){

			return false;
		
		}else{
			
			return true;
		
		}

	}

	public function check_if_api_key_exists(string $api_key){

	}

	public function get_user_by_user_id(string $user_id){

	}

	public function get_user_by_api_key(string $api_key){

		$this->prepared = false;
		
		if($this->prepared == false){

			$prepareStatement = pg_prepare($this->dbconn, "get_user_by_api_key", "SELECT * FROM users WHERE id = (SELECT user_id FROM tokens WHERE token = $1 LIMIT 1)");
			$this->prepared = true;
		
		}

		$execute_prepared_statement = pg_execute($this->dbconn, "get_user_by_api_key", array($api_key));

		if($execute_prepared_statement){

			return pg_fetch_array($execute_prepared_statement);
		
		}else{

			return [
				'success' => false,
				'error_message' => 'No data found'
			];
		
		}

	}

	public function get_user_by_email(string $user_email){

	}

	public function get_api_keys_by_user_id(string $user_id){

	}

}
?>