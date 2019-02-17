<?php
namespace App\Models;

require_once __DIR__ . '/../../vendor/autoload.php';
 
use \Ramsey\Uuid\Uuid;
use \Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Symfony\Component\Dotenv\Dotenv;

class User
{

	private $dbconn;

	public function __construct()
	{
	/* Load the env file */
	$dotenv = new Dotenv();
	$dotenv->load(__DIR__.'/../../.env');

	/* Connect to database */
	$this->dbconn = pg_connect("host=" . getenv('DB_HOST') . " port=5432 dbname=" . getenv('DB_NAME') . " user=" . getenv('DB_USERNAME') . " password=" . getenv('DB_PASSWORD'));

	//$this->sentry_instance = new Sentry();
	}

	/* Functions Related to creating and deleting users */

	public function createUser(string $username, string $password, string $email)
	{
	// Encrypt Password
	$encPassword = password_hash($password, PASSWORD_BCRYPT);
	unset($password); // We dont want to store the password in the code

	// Create User ID
	$userid = Uuid::uuid4();
	$userid = $userid->toString();

	// Add the user to DB
	$preparedStatement = pg_prepare($this->dbconn, "create_user", "INSERT INTO users (id, username, password, email, tier, is_admin, is_blocked) VALUES ($1, $2, $3, $4, 'free', false, false)");
	$executePreparedStatement =	pg_execute($this->dbconn, "create_user", array($userid, $username, $encPassword, $email));

	if(pg_result_status($executePreparedStatement) == 1 || pg_result_status($executePreparedStatement) == 6)
	{
		return 
		[
			'success' => true,
			'status' => 'created',
			'account' => [
			'id' => $userid,
			'username' => $username,
			'email' => $email
			]
		];
	}
	else
	{
		return
		[
			'success' => false,
			'message' => 'Something went horribly wrong while inserting the user into the database! Check the logs!'
		];

		$this->log_error('Something went horribly wrong while inserting the user into the database! Check the logs! Time: ' . gmdate("Y-m-d H:i:s", time()));
	}
	}

	public function deleteUser(string $id, string $email, string $password)
	{

	$prepareStatement = pg_prepare($this->dbconn, "get_old_password", 'SELECT password FROM users WHERE id = $1');
	$executePreparedStatement = pg_execute($this->dbconn, "get_old_password", array($id));
	$passwordDB = pg_fetch_array($executePreparedStatement);

	if(password_verify($password, $passwordDB[0])){

		$preparedStatement = pg_prepare($this->dbconn, "delete_user", "DELETE FROM users WHERE id = $1 AND email = $2");
		$executePreparedStatement = pg_execute($this->dbconn, "delete_user", array($id, $email));

		$prepareStatementApiKeys = pg_prepare($this->dbconn, "delete_user_api_keys", "DELETE FROM tokens WHERE user_id = $1");
		$executePreparedStatementApiKeys = pg_execute($this->dbconn, "delete_user_api_keys", array($id));

		if(pg_result_status($executePreparedStatement) == 1 || pg_result_status($executePreparedStatement) == 6 && pg_result_status($executePreparedStatementApiKeys) == 1 || pg_result_status($executePreparedStatementApiKeys) == 6)
		{
				return
			[
					'success' => true,
					'account' => [
					'deleted' => true
					]
				];
			}
			else
			{
			return
			[
					'success' => false,
					'message' => 'Something went horribly wrong while deleting the user from the database! Check the logs!'
			];

		$this->log_error('Something went horribly wrong while deleting the user from the database! Check the logs! Time: ' . gmdate("Y-m-d H:i:s", time()));
		}
	}else{
		return [
			'success' => false,
			'error_code' => 4002112,
			'error_message' => 'Wrong Password'
		];
	}
}

	 /* Other user-related functions */

	public function setUserTier(string $id, string $tier)
	{
	$preparedStatement = pg_prepare($this->dbconn, "update_tier", "UPDATE users SET tier = $1 WHERE id = $2");
	$executePreparedStatement =	pg_execute($this->dbconn, "update_tier", array($tier, $id));

	if($prepareStatement !== false && $executePreparedStatement !== false)
	{
		return
		[
		'success' => true,
		'account' => [
			'tier' => [
			'updated' => true
			]
		]
		];
	}
	else
	{
		return
		[
		'success' => false,
		'account' => [
			'tier' => [
			'updated' => false
			]
		]
		];
		$this->log_error('Couldnt update the tier of user ' . $id .	' Time: ' . gmdate("Y-m-d H:i:s", time()));
	}
	}

	public function setUserEmail(string $id, string $newUserEmail, $password)
	{

		$prepareStatement = pg_prepare($this->dbconn, "get_old_password", 'SELECT password FROM users WHERE id = $1');
	$executePreparedStatement = pg_execute($this->dbconn, "get_old_password", array($id));
	$passwordDB = pg_fetch_array($executePreparedStatement);

	if(password_verify($password, $passwordDB[0])){

		$prepareStatement = pg_prepare($this->dbconn, "update_email", "UPDATE users SET email = $1 WHERE id = $2");
		$executePreparedStatement = pg_execute($this->dbconn, "update_email", array($newUserEmail, $id));

		if($prepareStatement !== false && $executePreparedStatement !== false)
			{
					return
					[
					'success' => true,
					'account' => [
							'email' => [
						'updated' => true
							]
					]
					];
			}
			else
			{
				return
						[
						'success' => false,
						'account' => [
								'email' => [
								'updated' => false
								]
						]
						];
					$this->log_error('Couldnt update the email of user w/ username of ' . $userName .	' Time: ' . gmdate("Y-m-d H:i:s", time()));
			}
		}
	}

	public function setNewPassword($id, $oldPassword="", $newRawPassword, $override=false)
	{
	if ($override == true)
	{
		unset($oldPassword); //we dont need this anymore
		$newPassword = password_hash($newRawPassword, PASSWORD_BCRYPT);
		unset($newRawPassword); // we dont keep this
		$prepareStatement = pg_prepare($this->dbconn, "update_password_ovr", "UPDATE users SET password = $1 WHERE id = $2");
		$executePreparedStatement = pg_execute($this->dbconn, "update_password_ovr", array($newPassword, $id));
		if($prepareStatement !== false && $executePreparedStatement !== false)
		{
		return
		[
			'success' => true,
			'account' => [
			'password' => [
				'updated' => true
			]
			]
		];
		}
		else
		{
		return
		[
			'success' => false,
			'account' => [
			'password' => [
				'updated' => false
			]
			]
		];
		$this->log_error('Couldnt update the password (OVERRIDE) of user w/ username of ' . $username .	' Time: ' . gmdate("Y-m-d H:i:s", time()));
		}
	}
	elseif($override == false)
	{
		$newPassword = password_hash($newRawPassword, PASSWORD_BCRYPT);
		unset($newRawPassword); // we dont keep this
		
		$prepareStatement = pg_prepare($this->dbconn, "get_old_password", 'SELECT password FROM users WHERE id = $1');
		$executePreparedStatement = pg_execute($this->dbconn, "get_old_password", array($id));
		$oldPasswordDB = pg_fetch_array($executePreparedStatement);

		if(password_verify($oldPassword, $oldPasswordDB[0])){ 

			$prepareStatement = pg_prepare($this->dbconn, "update_password_ovr", "UPDATE users SET password = $1 WHERE id = $2");
			$old_pass = password_hash($oldPassword, PASSWORD_BCRYPT);
			$executePreparedStatement = pg_execute($this->dbconn, "update_password_ovr", array($newPassword, $id));
			if($prepareStatement !== false && $executePreparedStatement !== false)
			{
			return
			[
				'success' => true,
				'account' => [
				'password' => [
					'updated' => true
				]
			]
			];
			}
			else
			{
				return
			[
				'success' => false,
				'account' => [
				'password' => [
					'updated' => false
				]
				]
			];
			$this->log_error('Couldnt update the password (OVERRIDE) of user w/ username of ' . $username .	' Time: ' . gmdate("Y-m-d H:i:s", time()));
			}
			unset($username, $oldPassword, $newRawPassword, $override);
		}else{
		return
			[
			'success' => false,
			'error_code' => 4002112,
			'error_message' => 'Wrong Password',
			'account' => [
				'password' => [
				'updated' => false
				]
			]
			];
		}
		}
}
	public function log_error($error){
	return $error;
	}
}
?>
