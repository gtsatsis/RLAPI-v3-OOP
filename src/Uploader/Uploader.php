<?php
namespace App\Uploader;

use App\Models\User;
use \Ramsey\Uuid\Uuid;
use \Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Symfony\Component\Dotenv\Dotenv;

use \Aws\S3\S3Client;

class Uploader {

	private $dbconn;
	private $s3;
	private $bucket;

	public function __construct($bucket='owoapi'){
		/* Load the env file */
		$dotenv = new Dotenv();
		$dotenv->load(__DIR__.'/../../.env');

		/* Connect to database */
		$this->dbconn = pg_connect("host=" . getenv('DB_HOST') . " port=5432 dbname=" . getenv('DB_NAME') . " user=" . getenv('DB_USERNAME') . " password=" . getenv('DB_PASSWORD'));

		$this->s3 = new \Aws\S3\S3Client(
			[
				'version' => 'latest', // Latest S3 version
				'region'  => 'us-east-1', // The service's region
				'endpoint' => getenv('S3_ENDPOINT'), // API to point to
				'credentials' => new \Aws\Credentials\Credentials(getenv('S3_API_KEY'), getenv('S3_API_SECRET')), // Credentials (s3Credentials.inc.php)
				'use_path_style_endpoint' => true // Minio Compatible (https://minio.io)
        	]
    	);

    	$this->bucket = $bucket;

	}

	public function Upload($api_key, $file){


		$authenticate = true;
		if($authenticate){

			//$util->logObject(); // TODO: Create the object logging util
			$upload = $this->uploadToS3('oof.lol', $file);
			if($upload){
				$response = [
					'success' => true,
					'files' => [
						'url' => implode('', $file['name']),
						'name' => implode('', $file['name']),
						'hash_md5' => md5_file(implode('', $file['tmp_name'])),
						'hash_sha1' => sha1_file(implode('', $file['tmp_name']))
					]
				];
			}else{
				$response = [
					'success' => false,
					'error_code' => 403408
				];
			}
		}else{
			$response = [
				'success' => false,
				'error_message' => 'Invalid Credentials'
			];
		}
		return $response;

	}

	public function uploadToS3($file_name, $file){

		$putObject = $this->s3->putObject(
			[
				'Bucket' => $this->bucket, // Bucket name
				'Key'    => $file_name, // Key = File name (on the server)
				'SourceFile'   => implode('', $file['tmp_name']), // The file to be put
				'ACL'    => 'public-read' // Access Control List set to public read
            ]
		);

        if($putObject){
        	return true;
        }else{
        	throw new \Exception('Something went wrong while uploading to the s3 bucket.');
        }

	}


}

?>