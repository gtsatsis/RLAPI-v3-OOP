<?php
namespace App\Models;
require_once __DIR__ . '/../../vendor/autoload.php';

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Symfony\Component\Dotenv\Dotenv;
use App\Utils\Auth;

class Users {

	private $dbconn;
	private $authentication;

	public function __construct(){

		/* Load the env file */
		$dotenv = new Dotenv();
		$dotenv->load(__DIR__.'/../../.env');

		/* Connect to database */
		$this->dbconn = pg_connect("host=" . getenv('DB_HOST') . " port=5432 dbname=" . getenv('DB_NAME') . " user=" . getenv('DB_USERNAME') . " password=" . getenv('DB_PASSWORD'));

		$this->authentication = new Auth();

	}

	/* Begin User Creation Function */

	public function create_user(string $username, string $password, string $email){
		$password = password_hash($password, PASSWORD_BCRYPT);

		$user_id = Uuid::uuid4();
		$user_id = $user_id->toString();

		pg_prepare($this->dbconn, "create_user", "INSERT INTO users (id, username, password, email, tier, is_admin, is_blocked) VALUES ($1, $2, $3, $4, 'free', false, false)");

		$execute_prepared_statement = pg_execute($this->dbconn, "create_user", array($user_id, $username, $password, $email));

		if($execute_prepared_statement){

			return [
				'success' => true,
				'status' => 'created',
				'account' => [
					'id' => $userid,
					'username' => $username,
					'email' => $email
				]
			];

		}else{

			throw new \Exception("Error Processing create_user Request");
		
		}
	}

	/* End User Creation Function */

	/* Begin User Deletion Function */

	public function delete_user(string $user_id, string $email, string $password){
		if($this->authentication->validate_password($user_id, $password)){

			/* User Deletion */
			pg_prepare($this->dbconn, "delete_user", "DELETE FROM users WHERE id = $1 AND email = $2");
			$execute_prepared_statement = pg_execute($this->dbconn, "delete_user", array($id, $email));
			if($execute_prepared_statement){
			
				/* Api key deletion */
				pg_prepare($this->dbconn, "delete_user_api_keys", "DELETE FROM tokens WHERE user_id = $1");
				$execute_prepared_statement = pg_execute($this->dbconn, "delete_user_api_keys", array($id));

				if($execute_prepared_statement){

					return [
						'success' => true
					];
				
				}else{

					throw new \Exception("Error Processing delete_user Request: Apikeys Deletion");
				
				}
			}else{

					throw new \Exception("Error Processing delete_user Request: Userdata Deletion");
			
			}

		}else{

			return [
				'success' => false,
				'error_code' => 1002,
				'error_message' => 'Invalid user ID or Password'
			];

		}
	}

	/* End User Deletion Function */

	/* Begin User Set Email Function */

	public function user_set_email(string $id, string $user_new_email, string $password){

		if($this->authentication->validate_password($user_id, $password)){

			pg_prepare($this->dbconn, "update_email", "UPDATE users SET email = $1 WHERE id = $2");
			$execute_prepared_statement = pg_execute($this->dbconn, "update_email", array($user_new_email, $id));

			if($execute_prepared_statement){

				return [
					'success' => true,
					'account' => [
						'updated' => [
							'email' => true
						]
					]
				];

			}else{

				throw new \Exception("Error Processing user_set_email Request");
			
			}

		}else{

			return [
				'success' => false,
				'error_code' => 1002,
				'error_message' => 'Invalid user ID or Password'
			];

		}

	}

	/* End User Set Email Function */

	/* Begin User Set Password Function */

	public function user_set_password($user_id, $old_password="", $new_password, $override=false){

		if($override=true){

			pg_prepare($this->dbconn, "update_password", "UPDATE users SET password = $1 WHERE id = $2");
			$execute_prepared_statement = pg_execute($this->dbconn, "update_password_ovr", array(password_hash($new_password, PASSWORD_BCRYPT), $id));

			if($execute_prepared_statement){

					return [
						'success' => true,
						'account' => [
							'updated' => [
								'password' => true
							]
						]
					];

			}else{
				throw new \Exception("Error Processing user_set_password Request: Override");
			}

		}else{
			if($this->authentication->validate_password($user_id, $old_password)){

				pg_prepare($this->dbconn, "update_password", "UPDATE users SET password = $1 WHERE id = $2");
				$execute_prepared_statement = pg_execute($this->dbconn, "update_password_ovr", array(password_hash($new_password, PASSWORD_BCRYPT), $id));

				if($execute_prepared_statement){

					return [
						'success' => true,
						'account' => [
							'updated' => [
								'password' => true
							]
						]
					];

				}else{
					throw new \Exception("Error Processing user_set_password Request");
				}

			}else{

				return [
					'success' => false,
					'error_code' => 1002,
					'error_message' => 'Invalid user ID or Password'
				];

			}
		}

	}

	/* End User Set Password Function */

	/* Begin Upload Authentication Function */

	public function upload_authentication($api_key){

		$prepareStatement = pg_prepare($this->dbconn, "get_user_by_api_key_2", "SELECT * FROM users WHERE id = (SELECT user_id FROM tokens WHERE token = $1 LIMIT 1)");
		$executePreparedStatement = pg_execute($this->dbconn, "get_user_by_api_key_2", array($api_key));

		$user = pg_fetch_array($executePreparedStatement);

		if($user != null){
			
			if($user['is_blocked'] == false || !empty($user['is_blocked'])){
				
				return true;
			
			}else{

				return [
					'success' => false,
					'error_message' => 'User banned.'
				];
			
			}
		
		}else{

			throw new \Exception('Userdata not found. Api key: ' . $api_key);
		
		}
	}

	/* End Upload Authentication Function */

	/* Begin Get User By API Key Function */

	public function get_user_by_api_key($api_key){

		$this->prepared = false;
		
		if($this->prepared == false){

			$prepareStatement = pg_prepare($this->dbconn, "get_user_by_api_key", "SELECT * FROM users WHERE id = (SELECT user_id FROM tokens WHERE token = $1 LIMIT 1)");
			$this->prepared = true;
		
		}

		$executePreparedStatement = pg_execute($this->dbconn, "get_user_by_api_key", array($api_key));

		if($executePreparedStatement){

			return pg_fetch_array($executePreparedStatement);
		
		}else{

			return [
				'success' => false,
				'error_message' => 'No data found'
			];
		
		}

		/* End Get User By API Key Function */
	}

}
?>
