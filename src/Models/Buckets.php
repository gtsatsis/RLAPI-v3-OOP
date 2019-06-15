<?php

namespace App\Models;

require_once __DIR__.'/../../vendor/autoload.php';

use App\Utils\Getters;
use App\Utils\Auth;
use Symfony\Component\Dotenv\Dotenv;

class Buckets
{
    private $dbconn;

    private $s3;

    private $getter;

    public function __construct()
    {
        /* Load the env file */
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__.'/../../.env');

        /* Connect to database */
        $this->dbconn = pg_connect('host='.getenv('DB_HOST').' port=5432 dbname='.getenv('DB_NAME').' user='.getenv('DB_USERNAME').' password='.getenv('DB_PASSWORD'));

        $this->authentication = new Auth();

        $this->s3 = new \Aws\S3\S3Client(
            [
                'version' => 'latest', // Latest S3 version
                'region' => 'us-east-1', // The service's region
                'endpoint' => getenv('S3_ENDPOINT'), // API to point to
                'credentials' => new \Aws\Credentials\Credentials(getenv('S3_API_KEY'), getenv('S3_API_SECRET')), // Credentials
                'use_path_style_endpoint' => true, // Minio Compatible (https://minio.io)
            ]
        );

        $this->getter = new Getters();
    }

    /* Begin Create Bucket Function */

    public function create_new_user_bucket(string $bucket_name, string $username, string $password, string $allocated_domain = null)
    {
        $user_id = $this->getter->get_user_id_by_username($username);

        if ($this->authentication->validate_password($user_id, $password)) {
            if ($this->authentication->bucket_allowance($user_id)) {
                $create_bucket = $this->s3->createBucket(['ACL' => 'public-read', 'Bucket' => $bucket_name, 'CreateBucketConfiguration' => ['LocationConstraint' => 'us-east-1']]);

                if (null != $create_bucket) {
                    pg_prepare($this->dbconn, 'insert_bucket', 'INSERT INTO buckets (user_id, bucket_name, allocated_domain) VALUES ($1, $2, $3)');
                    $execute_prepared_statement = pg_execute($this->dbconn, 'insert_bucket', array($user_id, $bucket_name, $allocated_domain));

                    if ($execute_prepared_statement) {
                        return [
                                'success' => true,
                                'bucket' => [
                                    'location' => $create_bucket->get('Location'),
                                ],
                            ];
                    } else {
                        throw new Exception('Error Processing create_new_user_bucket Request: SQL');
                    }
                } else {
                    throw new Exception('Error Processing create_new_user_bucket Request');
                }
            } else {
                return [
                    'success' => false,
                    'error_code' => 1122,
                    'error_message' => 'private_bucket_allowance_reached',
                ];
            }
        } else {
            return [
                'success' => false,
                'error_code' => 1002,
                'error_message' => 'invalid_user_id_or_password',
            ];
        }
    }

    /* End Create Bucket Function */

    /* Begin Delete Bucket Function */

    public function delete_user_bucket(string $bucket_id, string $username, string $password)
    {
        $user_id = $this->getter->get_user_id_by_username($username);

        if ($this->authentication->validate_password($user_id, $password)) {
            if ($this->authentication->owns_bucket($user_id, $bucket_id)) {
                $bucket_name = $this->getter->get_bucket_name_by_id($bucket_id);
                $delete_bucket = $this->s3->deleteBucket(['Bucket' => $bucket_name]);

                if (null != $delete_bucket) {
                    pg_prepare($this->dbconn, 'delete_bucket', 'DELETE FROM buckets WHERE bucket_name = $1');
                    $execute_prepared_statement = pg_execute($this->dbconn, 'delete_bucket', array($bucket_name));

                    if ($execute_prepared_statement) {
                        return [
                            'success' => true,
                        ];
                    } else {
                        throw new Exception('Error Processing delete_bucket Request: sql');
                    }
                }
            } else {
                return [
                    'success' => false,
                    'error_code' => 1100,
                    'error_message' => 'unauthorized_not_owner',
                ];
            }
        } else {
            return [
                'success' => false,
                'error_code' => 1002,
                'error_message' => 'invalid_user_id_or_password',
            ];
        }
    }
}
