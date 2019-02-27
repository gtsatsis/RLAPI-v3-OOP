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

	public function validate_password(string $user_id, string $password){

		pg_prepare($this->dbconn, "get_password", 'SELECT password FROM users WHERE id = $1');
		$execute_prepared_statement = pg_execute($this->dbconn, "get_password", array($user_id));
		$password_DB = pg_fetch_array($execute_prepared_statement);

		if(password_verify($password, $password_DB[0])){

			return true;
		
		}else{

			return false;
		
		}

	}

	public function bucket_allowance(string $user_id){

		pg_prepare($this->dbconn, "get_bucket_allowance", "SELECT private_domains FROM tiers WHERE tier = (SELECT tier FROM users WHERE id = $1)");
		$execute_prepared_statement = pg_execute($this->dbconn, "get_bucket_allowance", array($user_id));
		$bucket_allowance = pg_fetch_array($execute_prepared_statement);

		pg_prepare($this->dbconn, "get_current_buckets", "SELECT COUNT(*) FROM buckets WHERE user_id = $1");
		$execute_prepared_statement = pg_execute($this->dbconn, "get_current_buckets", array($user_id));
		$current_buckets = pg_fetch_array($execute_prepared_statement)[0];


		if($bucket_allowance >= $current_buckets){
			return false;
		}else{
			return true;
		}

	}

	public function captcha_verify($recaptcha){
			$url = "https://www.google.com/recaptcha/api/siteverify";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, array("secret"=>getenv('RECAPTCHA_SECRET'),"response"=>$recaptcha));
			$response = curl_exec($ch);
			curl_close($ch);
			$data = json_decode($response);     
        
			return $data->success;        
    }


    /* Begin Upload Authentication Function */

	public function upload_authentication(string $api_key){

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

}

?>