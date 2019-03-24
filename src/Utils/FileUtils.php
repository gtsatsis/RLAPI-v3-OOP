<?php
namespace App\Utils;

use Symfony\Component\Dotenv\Dotenv;
use App\Models\User;

class FileUtils{

	private $dbconn;
	
	public function __construct(){
		/* Load the env file */
		$dotenv = new Dotenv();
		$dotenv->load(__DIR__.'/../../.env');
		/* Connect to database */
		$this->dbconn = pg_connect("host=" . getenv('DB_HOST') . " port=5432 dbname=" . getenv('DB_NAME') . " user=" . getenv('DB_USERNAME') . " password=" . getenv('DB_PASSWORD'));
	}

	public function generateFileName($extension){

    	// Generate a random name
    	$fileName = substr(str_shuffle(str_repeat(getenv('FILENAME_DICTIONARY'), getenv('FILENAME_LENGTH'))), 0, getenv('FILENAME_LENGTH'));
    	
    	// Add file extension
    	$fileName .= "." . $extension;
    	return $fileName;
	}

	public function isUnique($filename, $s3Endpoint){

		$headers = get_headers($s3Endpoint . $filename);
		
		if (substr($headers[0], 9, 3) == "404") {
		
			return true;
		
    	}
		
		return false;
	}

	public function log_object($api_key, $file_name, $file_original_name, $file_md5_hash, $file_sha1_hash){
		$users = new User();

		$user_id = $users->get_user_by_api_key($api_key);

		$user_id = $user_id['id'];
		if(!empty($user_id)){
			$prepareStatement = pg_prepare($this->dbconn, "log_object", "INSERT INTO files VALUES ($1, $2, $3, $4, $5, $6, $7)");
			$executePreparedStatement = pg_execute($this->dbconn, "log_object", array($file_name, $file_original_name, time(), $user_id, $api_key, $file_md5_hash, $file_sha1_hash));

			if($executePreparedStatement){
				return true;
			}else{
				throw new \Exception('Something went wrong while inserting a file into the database.');
			}
		}
	}
}

?>