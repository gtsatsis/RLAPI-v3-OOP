<?php

namespace App\Uploader;

use App\Utils\Auth;
use App\Utils\FileUtils;
use App\Utils\EncryptionUtils;
use Symfony\Component\Dotenv\Dotenv;

class Uploader
{
    private $dbconn;

    private $s3;

    private $bucket;

    private $encrypt;

    private $authentication;

    private $encryptUtil;

    public function __construct($bucket, $encrypt)
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
                'credentials' => new \Aws\Credentials\Credentials(getenv('S3_API_KEY'), getenv('S3_API_SECRET')), // Credentials
                'use_path_style_endpoint' => true, // Minio Compatible (https://minio.io)
            ]
        );

        $this->bucket = $bucket;
        if ($encrypt) {
            $this->encrypt = 't';
        } else {
            $this->encrypt = 'f';
        }

        $this->authentication = new Auth();

        $this->encryptUtil = new EncryptionUtils();
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
                    if ($fileUtils->isUnique($file_name)) {
                        $file_name_is_unique = true;
                    } else {
                        $file_name_is_unique = false;
                        $file_name = $fileUtils->generateFileName($extension);
                    }
                }

                $file_md5_hash = md5_file(implode('', $file['tmp_name']));
                $file_sha1_hash = sha1_file(implode('', $file['tmp_name']));
                $file_original_name = implode('', $file['name']);

                $check_against_hashlist = $fileUtils->check_object_against_hashlist($file_md5_hash, $file_sha1_hash);

                if ('t' == $this->encrypt) {
                    if (implode('', $file['size']) < 12582912) {
                        $encrypt_data = $this->encryptUtil->encryptData(file_get_contents(implode('', $file['tmp_name'])), null, implode('', $file['tmp_name']));
                        if ($encrypt_data['success']) {
                            unset($encrypt_data['data']);
                            $password = $encrypt_data['password'];
                        }
                    } else {
                        $this->encrypt = 'f';
                    }
                }

                if (true == $check_against_hashlist['clearance']) {
                    $fileUtils->log_object($api_key, $file_name, $file_original_name, $file_md5_hash, $file_sha1_hash, $this->bucket, $this->encrypt);

                    if (move_uploaded_file(implode('', $file['tmp_name']), getenv('TMP_STORE').$file_name)) {
                        $file_loc = getenv('TMP_STORE').$file_name;
                    } else {
                        throw new \Exception('Unable to move uploaded file.');
                    }

                    $upload = $this->uploadToS3($file_name, $file_loc);

                    if ($upload) {
                        $response = [
                            'status_code' => 200,
                            'response' => [
                                'success' => true,
                                'files' => [
                                        [
                                            'url' => $file_name,
                                            'name' => $file_original_name,
                                            'hash_md5' => $file_md5_hash,
                                            'hash_sha1' => $file_sha1_hash,
                                        ],
                                ],
                            ],
                        ];

                        if ('t' == $this->encrypt) {
                            $response['response']['files'][0]['url'] = $file_name.'?password='.$password;
                            $response['response']['files'][0]['url_plain'] = $file_name;
                            $response['response']['files'][0]['encryption_password'] = $password;
                        }
                    } else {
                        $response = [
                            'status_code' => 500,
                            'response' => [
                                'success' => false,
                                'error_code' => 403408,
                            ],
                        ];
                    }
                } else {
                    if ('cp' == $check_against_hashlist['reason']) {
                        pg_prepare($this->dbconn, 'block_by_cp_upload', 'INSERT INTO watchlist (api_key, timestamp, reason) VALUES ($1, $2, $3)');
                        pg_execute($this->dbconn, 'block_by_cp_upload', array($api_key, time(), 'uploaded child abuse material'));
                        pg_prepare($this->dbconn, 'block_user_by_cp_upload', 'UPDATE users SET is_blocked = true WHERE id = (SELECT user_id FROM tokens WHERE token = $1)');
                        pg_execute($this->dbconn, 'block_user_by_cp_upload', array($api_key));
                        $response = [
                            'status_code' => 451,
                            'response' => [
                                'success' => false,
                                'message' => 'Content Banned.',
                            ],
                        ];
                    } else {
                        $response = [
                'status_code' => 451,
                'response' => [
                    'success' => false,
                    'message' => 'Content Banned.',
                ],
            ];
                    }
                }
            } else {
                $response = [
                    'status_code' => 413,
                    'response' => [
                        'success' => false,
                        'error_code' => 1010,
                        'error_message' => 'Maximum Allowed Filesize Exceeded',
                    ],
                ];
            }
        } else {
            $response = [
                'status_code' => 401,
                'response' => [
                    'success' => false,
                    'error_message' => 'Invalid Credentials',
                ],
            ];
        }
        if($authentication){
            if (true == $check_against_hashlist['clearance']) {
                if (null != $file_name) {
                    unlink(getenv('TMP_STORE').$file_name);
                } else {
                    unlink(implode('', $file['tmp_name']));
                }
            }
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
