<?php
namespace App\Models;
require_once __DIR__ . '/../../vendor/autoload.php';

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use \Aws\S3\S3Client;
use \Aws\S3\AwsException;
use Symfony\Component\Dotenv\Dotenv;
use App\Utils\Auth;

class Buckets {

	private $dbconn;
	private $s3;

	public function __construct(){

		/* Load the env file */
		$dotenv = new Dotenv();
		$dotenv->load(__DIR__.'/../../.env');

		/* Connect to database */
		$this->dbconn = pg_connect("host=" . getenv('DB_HOST') . " port=5432 dbname=" . getenv('DB_NAME') . " user=" . getenv('DB_USERNAME') . " password=" . getenv('DB_PASSWORD'));

		$this->authentication = new Auth();

		$this->s3 = new \Aws\S3\S3Client(
			[
				'version' => 'latest', // Latest S3 version
				'region'  => 'us-east-1', // The service's region
				'endpoint' => getenv('S3_ENDPOINT'), // API to point to
				'credentials' => new \Aws\Credentials\Credentials(getenv('S3_API_KEY'), getenv('S3_API_SECRET')), // Credentials
				'use_path_style_endpoint' => true // Minio Compatible (https://minio.io)
        	]
    	);

	}


	/* Begin Create Bucket Function */

	public function create_new_user_bucket(string $user_id, string $bucket_name, string $password, string $allocated_domain=null){

		if($this->authentication->validate_password($user_id, $password)){

			if($this->authentication->bucket_allowance($user_id)){

					$create_bucket = $this->s3->createBucket(['ACL' => 'public-read','Bucket' => $bucket_name,'CreateBucketConfiguration' => ['LocationConstraint' => 'us-east-1']]);

					if($create_bucket != null){

						pg_prepare($this->dbconn, "insert_bucket", "INSERT INTO buckets (user_id, bucket_id, bucket_name, allocated_domain) VALUES ($1, $2, $3, $4)");
						$execute_prepared_statement = pg_execute($this->dbconn, "insert_bucket", array($user_id, $bucket_id, $bucket_name, $allocated_domain));
						
						if($execute_prepared_statement){

							return [
								'success' => true,
								'bucket' => [
									'location' => $create_bucket->toString()['Location']
								]
							];

						}else{

							throw new Exception("Error Processing create_new_user_bucket Request: SQL");
							
						}

					}else{

						throw new Exception("Error Processing create_new_user_bucket Request");
						
					}

			}else{

				return [
					'success' => false,
					'error_code' => 1122,
					'error_message' => 'You have reached your allocated private bucket allowance.'
				];

			}
		}else{

			return [
				'success' => false,
				'error_code' => 1002,
				'error_message' => 'Invalid user ID or Password'
			];

		}

	}

	/* End Create Bucket Function */

	/* Begin Delete Bucket Function */

	public function delete_user_bucket(string $user_id, string $bucket_name, string $password, string $allocated_domain=null){

		if($this->authentication->validate_password($user_id, $password)){

			$this->s3->deleteBucket(['Bucket' => $bucket_name]);
			
		}else{

			return [
				'success' => false,
				'error_code' => 1002,
				'error_message' => 'Invalid user ID or Password'
			];

		}

	}



}
?>