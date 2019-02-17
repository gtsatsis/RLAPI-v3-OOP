<?php
namespace App\Models;
require_once __DIR__ . '/../../vendor/autoload.php';

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Symfony\Component\Dotenv\Dotenv;

class Apikeys {

	private $dbconn;

	public function __construct(){
		/* Load the env file */
		$dotenv = new Dotenv();
		$dotenv->load(__DIR__.'/../../.env');

		/* Connect to database */
		$this->dbconn = pg_connect("host=" . getenv('DB_HOST') . " port=5432 dbname=" . getenv('DB_NAME') . " user=" . getenv('DB_USERNAME') . " password=" . getenv('DB_PASSWORD'));
	}

	public function createUserApiKey(string $id, string $api_key_name, string $password){
		$prepareStatement = pg_prepare($this->dbconn, "get_old_password", 'SELECT password FROM users WHERE id = $1');
		$executePreparedStatement = pg_execute($this->dbconn, "get_old_password", array($id));
		$passwordDB = pg_fetch_array($executePreparedStatement);

		if(password_verify($password, $passwordDB[0])){
			$unique = false;
			while ($unique == false){
		
				$api_key = Uuid::uuid4();
				$api_key = $api_key->toString();
				$prepareStatement = pg_prepare($this->dbconn, "check_if_api_key_exists", "SELECT * FROM tokens WHERE token = $1");
				$executePreparedStatement = pg_execute($this->dbconn, "check_if_api_key_exists", array($api_key));
				$numberOfRows = pg_num_rows($executePreparedStatement);
		
				if($numberOfRows == 0){
					$unique = true;
				}
			}

			$prepareStatement = pg_prepare($this->dbconn, "insert_api_key", "INSERT INTO tokens (user_id, token, name) VALUES ($1, $2, $3)");
			$executePreparedStatement = pg_execute($this->dbconn, "insert_api_key", array($id, $api_key, $api_key_name));
    
    		if($prepareStatement && $executePreparedStatement){
    			return [
					'success' => true,
					'api_key' => [
						'created' => true,
						'key' => $api_key
					]
				];
			}else{
				return [
					'success' => false,
					'error_code' => 302882
				];
			}
		}else{
			return [
				'success' => false,
				'error_code' => 4002112,
				'error_message' => 'Wrong Password'
			];
		}
	}


	public function deleteUserApiKey(string $id, string $api_key, string $password){
		$prepareStatement = pg_prepare($this->dbconn, "get_old_password", 'SELECT password FROM users WHERE id = $1');
		$executePreparedStatement = pg_execute($this->dbconn, "get_old_password", array($id));
		$passwordDB = pg_fetch_array($executePreparedStatement);

		if(password_verify($password, $passwordDB[0])){
			$prepareStatement = pg_prepare($this->dbconn, "delete_api_key", "DELETE FROM tokens WHERE user_id = $1 AND token = $2");
			$executePreparedStatement = pg_execute($this->dbconn, "delete_api_key", array($id, $api_key));

			if($prepareStatement && $executePreparedStatement){
				return [
					'success' => true,
					'api_key' => [
						'deleted' => true
					]
				];
			}else{
				return [
					'success' => false,
					'errorcode' => 302882
				];
			}
		}else{
			return [
				'success' => false,
				'error_code' => 4002112,
				'error_message' => 'Wrong Password'
			];
		}
	}

	public function renameApiKey(string $id, string $api_key, string $api_key_name, string $password){

		$prepareStatement = pg_prepare($this->dbconn, "get_old_password", 'SELECT password FROM users WHERE id = $1');
		$executePreparedStatement = pg_execute($this->dbconn, "get_old_password", array($id));
		$passwordDB = pg_fetch_array($executePreparedStatement);

		if(password_verify($password, $passwordDB[0])){
			$prepareStatement = pg_prepare($this->dbconn, "rename_api_key", "UPDATE tokens SET name = $1 WHERE token = $2");
			$executePreparedStatement = pg_execute($this->dbconn, "rename_api_key", array($api_key_name, $api_key));

			if($prepareStatement && $executePreparedStatement){
				return [
					'success' => true,
					'api_key' => [
						'name' => $newFriendlyName
					]
				];
			}else{
				return [
					'success' => false,
					'errorcode' => 302882
				];
			}
		}else{
			return [
				'success' => false,
				'error_code' => 4002112,
				'error_message' => 'Wrong Password'
			];
		}
	}

	public function regenerateApiKey(string $id, string $api_key, string $password){

		$prepareStatement = pg_prepare($this->dbconn, "get_old_password", 'SELECT password FROM users WHERE id = $1');
		$executePreparedStatement = pg_execute($this->dbconn, "get_old_password", array($id));
		$passwordDB = pg_fetch_array($executePreparedStatement);

		if(password_verify($password, $passwordDB[0])){
			$prepareStatement = pg_prepare($this->dbconn, "regen_api_key", "UPDATE tokens SET token = $1 WHERE token = $2 AND user_id = $3");

			$new_token = Uuid::uuid4();
			$new_token = $new_token->toString();

			$executePreparedStatement = pg_execute($this->dbconn, "regen_api_key", array($new_token, $api_key, $id));

			if($prepareStatement && $executePreparedStatement){
				return [
					'success' => true,
					'api_key' => [
						'updated' => true,
						'new_value' => $new_token
					]
				];
			}else{
				return [
					'success' => false,
					'errorcode' => 302882
				];
			}
		}else{
			return [
				'success' => false,
				'error_code' => 4002112,
				'error_message' => 'Wrong Password'
			];
		}
	}


}
?>