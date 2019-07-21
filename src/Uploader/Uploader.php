<?php

namespace App\Uploader;

use App\Utils\Auth;
use App\Utils\FileUtils;
use Symfony\Component\Dotenv\Dotenv;

class Uploader
{
    private $dbconn;

    private $s3;

    private $bucket;

    private $authentication;

    public function __construct($bucket = null)
    {
        /* Load the env file */
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__.'/../../.env');

        /* Connect to database */
        $this->dbconn = pg_connect('host='.getenv('DB_HOST').' port=5432 dbname='.getenv('DB_NAME').' user='.getenv('DB_USERNAME').' password='.getenv('DB_PASSWORD'));

        $this->s3 = new \Aws\S3\S3Client(
            [
                'version' => 'latest', // Latest S3 version
                'region' => 'us-east-1', // The service's region
                'endpoint' => getenv('S3_ENDPOINT'), // API to point to
                'credentials' => new \Aws\Credentials\Credentials(getenv('S3_API_KEY'), getenv('S3_API_SECRET')), // Credentials (s3Credentials.inc.php)
                'use_path_style_endpoint' => true, // Minio Compatible (https://minio.io)
            ]
        );

        if (null == $bucket) {
            $this->bucket = getenv('S3_BUCKET');
        } else {
            $this->bucket = $bucket;
        }

        $this->authentication = new Auth();
    }

    public function Upload($api_key, $file)
    {
        $file_name = null;

        $authentication = $this->authentication->upload_authentication($api_key);

        if ($authentication) {
            if ($this->authentication->maximum_filesize_assessment($api_key, implode('', $file['size']))) {
                $fileUtils = new FileUtils();

                $extension = pathinfo(implode('', $file['name']), PATHINFO_EXTENSION);

                $file_name = $fileUtils->generateFileName($extension, 10);
                $file_name_is_unique = false;

                while (!$file_name_is_unique) {
                    if ($fileUtils->isUnique($file_name, getenv('S3_ENDPOINT').'/'.$this->bucket.'/')) {
                        $file_name_is_unique = true;
                    } else {
                        $file_name_is_unique = false;
                        $file_name = $fileUtils->generateFileName($extension);
                    }
                }

                $file_md5_hash = md5_file(implode('', $file['tmp_name']));
                $file_sha1_hash = sha1_file(implode('', $file['tmp_name']));
                $file_original_name = implode('', $file['name']);

                $fileUtils->log_object($api_key, $file_name, $file_original_name, $file_md5_hash, $file_sha1_hash);

                if (move_uploaded_file(implode('', $file['tmp_name']), getenv('TMP_STORE').$file_name)) {
                    $file_loc = getenv('TMP_STORE').$file_name;
                } else {
                    throw new \Exception('Unable to move uploaded file.');
                }

                $upload = $this->uploadToS3($file_name, $file_loc);

                if ($upload) {
                    $response = [
                        'success' => true,
                        'files' => [
                                [
                                    'url' => $file_name,
                                    'name' => $file_original_name,
                                    'hash_md5' => $file_md5_hash,
                                    'hash_sha1' => $file_sha1_hash,
                                ],
                        ],
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'error_code' => 403408,
                    ];
                }
            } else {
                $response = [
                    'success' => false,
                    'error_code' => 1010,
                    'error_message' => 'Maximum Allowed Filesize Exceeded',
                ];
            }
        } else {
            $response = [
                'success' => false,
                'error_message' => 'Invalid Credentials',
            ];
        }

        if (null != $file_name) {
            unlink(getenv('TMP_STORE').$file_name);
        } else {
            unlink(implode('', $file['tmp_name']));
        }

        return $response;
    }

    public function uploadToS3($file_name, $file_loc)
    {
        $putObject = $this->s3->putObject(
            [
                'Bucket' => $this->bucket, // Bucket name
                'Key' => $file_name, // Key = File name (on the server)
                'SourceFile' => $file_loc, // The file to be put
                'ACL' => 'public-read', // Access Control List set to public read
            ]
        );

        if ($putObject) {
            return true;
        } else {
            throw new \Exception('Something went wrong while uploading to the s3 bucket.');
        }
    }
}
