<?php
namespace App\Models;
require_once __DIR__ . '/../../vendor/autoload.php';

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Symfony\Component\Dotenv\Dotenv;

use App\Utils\Auth;
use App\Utils\Mailer;
use App\Utils\SqreenLib;
use App\Utils\Getters;

class User {

	private $dbconn;
	private $authentication;
	private $sqreen;

	public function __construct(){

		/* Load the env file */
		$dotenv = new Dotenv();
		$dotenv->load(__DIR__.'/../../.env');

		/* Connect to database */
		$this->dbconn = pg_connect("host=" . getenv('DB_HOST') . " port=5432 dbname=" . getenv('DB_NAME') . " user=" . getenv('DB_USERNAME') . " password=" . getenv('DB_PASSWORD'));

		$this->authentication = new Auth();

		$this->sqreen = new SqreenLib();

	}

	/* Begin User Creation Function */

	public function create_user(string $username, string $password, string $email){
		$getter = new Getters();

		if($getter->check_if_user_exists($username, $email) == false){

			if(strlen($password) >=8){

				$password = password_hash($password, PASSWORD_BCRYPT);

				$user_id = Uuid::uuid4();
				$user_id = $user_id->toString();

				pg_prepare($this->dbconn, "create_user", "INSERT INTO users (id, username, password, email, tier, is_admin, is_blocked, verified) VALUES ($1, $2, $3, $4, 'free', false, false, false)");

				$execute_prepared_statement = pg_execute($this->dbconn, "create_user", array($user_id, $username, $password, $email));

				if($execute_prepared_statement){
				
					$send_verification_email = $this->user_send_verify_email($email, $user_id, $username);

					$this->sqreen->sqreen_signup_track($email);

					if($send_verification_email){
				
						return [
							'success' => true,
							'status' => 'created',
							'account' => [
								'id' => $user_id,
								'username' => $username,
								'email' => $email
							]
						];

				}	

				}else{

					throw new \Exception("Error Processing create_user Request");
		
				}
			}else{
				return [
					'success' => false,
					'error_code' => 1013,
					'error_message' => 'Insufficient password length'
				];
			}
			}else{
				return [
					'success' => false,
					'error_code' => 1012,
					'error_message' => 'User Email/Name Exists'
				];
			}
	}

	/* End User Creation Function */

	/* Begin User Deletion Function */

	public function delete_user(string $user_id, string $email, string $password){
		if($this->authentication->validate_password($user_id, $password)){

			/* User Deletion */
			pg_prepare($this->dbconn, "delete_user", "DELETE FROM users WHERE id = $1 AND email = $2");
			$execute_prepared_statement = pg_execute($this->dbconn, "delete_user", array($user_id, $email));
			if($execute_prepared_statement){
			
				/* Api key deletion */
				pg_prepare($this->dbconn, "delete_user_api_keys", "DELETE FROM tokens WHERE user_id = $1");
				$execute_prepared_statement = pg_execute($this->dbconn, "delete_user_api_keys", array($user_id));

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

	public function user_set_email(string $user_id, string $user_new_email, string $password){

		if($this->authentication->validate_password($user_id, $password)){

			pg_prepare($this->dbconn, "update_email", "UPDATE users SET email = $1 WHERE id = $2");
			$execute_prepared_statement = pg_execute($this->dbconn, "update_email", array($user_new_email, $user_id));

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

	public function user_set_password(string $user_id, string $old_password="", string $new_password, bool $override=false){

		if($override=true){

			pg_prepare($this->dbconn, "update_password_ovr", "UPDATE users SET password = $1 WHERE id = $2");
			$execute_prepared_statement = pg_execute($this->dbconn, "update_password_ovr", array(password_hash($new_password, PASSWORD_BCRYPT), $user_id));

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
				$execute_prepared_statement = pg_execute($this->dbconn, "update_password_ovr", array(password_hash($new_password, PASSWORD_BCRYPT), $user_id));

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

	/* Begin User Set Tier Function */

	public function user_set_tier(string $user_id, string $user_tier, string $api_key){

		if($this->authentication->api_key_is_admin($api_key)){

			pg_prepare($this->dbconn, "set_user_tier", "UPDATE users SET tier = $1 WHERE id = $2");
			$execute_prepared_statement = pg_execute($this->dbconn, "set_user_tier", array($user_tier, $user_id));

			if($execute_prepared_statement){
				return [
					'success' => true,
					'account' => [
						'id' => $user_id,
						'tier' => $user_tier
					]
				];
			}

		}else{
			return [
				'success' => false,
				'error_code' => 1009,
				'error_message' => 'Insufficient Permissions'
			];
		}

	}

	/* End User Set Tier Function */

	/* Begin User Send Email Verify Function */

	public function user_send_verify_email(string $user_email, string $user_id, string $username){

		$Mailer = new Mailer();

		$verification_id = Uuid::uuid4();
		$verification_id = $verification_id->toString();

		pg_prepare($this->dbconn, "verification_created", "INSERT INTO verification_emails (user_id, verification_id, email, used) VALUES ($1, $2, $3, false)");

		$execute_prepared_statement = pg_execute($this->dbconn, "verification_created", array($user_id, $verification_id, $user_email));

		if($execute_prepared_statement){

			$Mailer->send_verification_email($user_email, $user_id, $username, $verification_id);

			return true;

		}else{

			throw new \Exception("Error Processing user_send_verify_email Request");
			
		}

	}

	/* End User Send Email Verify Function */

	/* Begin User Email Verify Function */

	public function user_verify_email($user_id, $verification_id){

		pg_prepare($this->dbconn, "verify_email_fetch", "SELECT * FROM verification_emails WHERE verification_id = $1 AND user_id = $2 AND used IS NOT true");
		$execute_prepared_statement = pg_execute($this->dbconn, "verify_email_fetch", array($verification_id, $user_id));

		if($execute_prepared_statement){
			$verification_fetch = pg_fetch_array($execute_prepared_statement);

			if(!empty($verification_fetch)){

				pg_prepare($this->dbconn, "verify_email", "UPDATE users SET verified = true WHERE id = $1 AND email = $2");
				$execute_prepared_statement = pg_execute($this->dbconn, "verify_email", array($verification_fetch['user_id'], $verification_fetch['email']));

				pg_prepare($this->dbconn, "verify_email_2", "UPDATE verification_emails SET used = $1 WHERE verification_id = $2");
				$execute_prepared_statement_2 = pg_execute($this->dbconn, "verify_email_2", array(true, $verification_id));

				if($execute_prepared_statement && $execute_prepared_statement_2){

					return [
						'success' => true,
						'email' => [
							'verified' => true
						]
					];

				}else{
					throw new \Exception("Error Processing user_verify_email Request: verify_email/exec");
					
				}

			}else{

				return [
					'success' => false,
					'error_message' => 'already_verified_or_nonexistant'
				];
				
			}
		}else{
			throw new \Exception("Error Processing user_verify_email Request: execute_prepared_statement");
			
		}

	}

	/* End User Email Verify Function */

	/* Begin Get User By API Key Function */

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

		/* End Get User By API Key Function */
	}

	public function reset_password_send(string $user_email){

		$Mailer = new Mailer();

		$reset_id = Uuid::uuid4();
		$reset_id = $reset_id->toString();

		pg_prepare($this->dbconn, "fetch_user_on_reset", "SELECT * FROM users WHERE email = $1");
		$execute_prepared_statement = pg_execute($this->dbconn, "fetch_user_on_reset", array($user_email));
		
		$user_fetch = pg_fetch_array($execute_prepared_statement);

		if(!is_null($user_fetch['id'])){

			pg_prepare($this->dbconn, "reset_created", "INSERT INTO password_resets (id, email, used) VALUES ($1, $2, false)");

			$execute_prepared_statement = pg_execute($this->dbconn, "reset_created", array($reset_id, $user_email));

			if($execute_prepared_statement){

				$Mailer->send_password_reset_email($user_email, $reset_id);

				return [
					'message' => 'if_user_exists_then_email_sent_successfully'
				];

			}else{

				throw new \Exception("Error Processing reset_password_send Request");
			
			}
		
		}else{

			return [
					'message' => 'if_user_exists_then_email_sent_successfully'
				];
		}

	}


	public function user_password_reset($reset_id, $password){

		pg_prepare($this->dbconn, "reset_password_fetch", "SELECT * FROM password_resets WHERE id = $1 AND used IS NOT true");
		$execute_prepared_statement = pg_execute($this->dbconn, "reset_password_fetch", array($reset_id));

		if($execute_prepared_statement){
			$password_reset_fetch = pg_fetch_array($execute_prepared_statement);

			if(!empty($password_reset_fetch)){

				pg_prepare($this->dbconn, "get_user_id_by_email", "SELECT id FROM users WHERE email = $1");
				$execute_prepared_statement = pg_execute($this->dbconn, "get_user_id_by_email", array($password_reset_fetch['email']));

				$fetch_user = pg_fetch_array($execute_prepared_statement);

				if(!is_null($fetch_user)){

					$reset_password = $this->user_set_password($fetch_user['id'], "", $password, true);

					if($reset_password['success'] == true){

						pg_prepare($this->dbconn, "reset_password_set_used", "UPDATE password_resets SET used = true WHERE id = $1");
						$execute_prepared_statement = pg_execute($this->dbconn, "reset_password_set_used", array($reset_id));

						return [
							'success' => true,
							'password' => [
								'reset' => true
							]
						];

					}else{

						return [
							'success' => false,
							'message' => 'user_not_found'
						];

					}

				}

			}else{

				return [
					'success' => false,
					'error_message' => 'already_reset_or_nonexistant'
				];
				
			}
		}
	}

}
?>