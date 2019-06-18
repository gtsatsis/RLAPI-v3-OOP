<?php

namespace App\Models;

require_once __DIR__.'/../../vendor/autoload.php';

use App\Utils\Getters;
use App\Utils\Auth;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Dotenv\Dotenv;

class Buckets
{
    private $dbconn;

    private $s3;

    private $authentication;

    private $getter;

    public function __construct()
    {
        /* Load the env file */
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__.'/../../.env');

        /* Connect to database */
        $this->dbconn = pg_connect('host='.getenv('DB_HOST').' port=5432 dbname='.getenv('DB_NAME').' user='.getenv('DB_USERNAME').' password='.getenv('DB_PASSWORD'));

        $this->authentication = new Auth();

        $this->getter = new Getters();

        $this->s3 = new \Aws\S3\S3Client(
            [
                'version' => 'latest', // Latest S3 version
                'region' => 'us-east-1', // The service's region
                'endpoint' => getenv('S3_ENDPOINT'), // API to point to
                'credentials' => new \Aws\Credentials\Credentials(getenv('S3_API_KEY'), getenv('S3_API_SECRET')), // Credentials
                'use_path_style_endpoint' => true, // Minio Compatible (https://minio.io)
            ]
        );
    }

    public function create($api_key, $bucket_name)
    {
        if ($this->authentication->bucket_allowance($api_key) || $this->authentication->api_key_is_admin($api_key)) {
            $user = $this->getter->get_user_by_api_key($api_key);
            if (!$this->bucket_exists($bucket_name) && getenv('S3_BUCKET') != $bucket_name) {
                $create_bucket = $this->s3->createBucket(['ACL' => 'public', 'Bucket' => $bucket_name, 'CreateBucketConfiguration' => ['LocationConstraint' => 'us-east-1']]);
                $s3Policy = '{"Version": "2012-10-17","Statement": [{"Action": ["s3:ListBucket"],"Effect": "Allow","Principal": {"AWS": ["*"]},"Resource": ["arn:aws:s3:::%s"],"Sid": ""},{"Action": ["s3:PutObject","s3:GetObject", "s3:DeleteObject"],"Effect": "Allow","Principal": {"AWS": ["*"]},"Resource": ["arn:aws:s3:::%s/*"],"Sid": ""}]}';
                $this->s3->putBucketPolicy(['Bucket' => $bucket_name, 'Policy' => sprintf($s3Policy, $bucket_name, $bucket_name)]);
                if (!empty($create_bucket)) {
                    $bucket_data = [
                        'owner' => [
                            'id' => $user['id'],
                            'api_key' => $api_key,
                        ],
                        'users' => [
                            $user['id'] => [
                                'permissions' => [
                                    'rlapi.custom.bucket.permission.priority' => 200, /* Highest priority, soft-limit of 200 (Bucket Owner). Priorities can be used to allow modification of lower-priority users' permissions. */
                                    'rlapi.custom.bucket.upload' => true, /* bool, can upload */
                                    'rlapi.custom.bucket.manage' => true, /* bool, can manage general bucket settings */
                                    'rlapi.custom.bucket.users.get' => true, /* bool, can get a list of users in the bucket */
                                    'rlapi.custom.bucket.user.add' => true, /* bool, can add a user */
                                    'rlapi.custom.bucket.user.remove' => true, /* bool, can remove a user */
                                    'rlapi.custom.bucket.user.block' => true, /* bool, can block a user (set upload permission to false) */
                                    'rlapi.custom.bucket.user.unblock' => true, /* bool, can unblock a user (set upload permission to true) */
                                    'rlapi.custom.bucket.file.delete' => 'all', /* all, self, none*/
                                ],
                            ],
                        ],
                    ];
                    $bucket_data = json_encode($bucket_data);
                    $bucket_id = Uuid::uuid4();
                    $bucket_id = $bucket_id->toString();
                    pg_prepare($this->dbconn, 'insert_bucket', 'INSERT INTO buckets (id, user_id, api_key, bucket, data) VALUES ($1, $2, $3, $4, $5)');
                    pg_execute($this->dbconn, 'insert_bucket', array($bucket_id, $user['id'], $api_key, $bucket_name, $bucket_data));

                    return [
                        'success' => true,
                        'buckets' => [
                            $bucket_name => [
                                'id' => $bucket_id,
                                'owner' => [
                                    'username' => $user['username'],
                                    'email' => $user['email'],
                                ],
                            ],
                        ],
                    ];
                } else {
                    throw new \Exception('Bucket Creation Failed');
                }
            } else {
                return [
                    'success' => false,
                    'error' => [
                        'error_message' => 'Bucket'.$bucket_name.'already exists.',
                    ],
                ];
            }
        } else {
        }
    }

    public function delete($api_key, $bucket_id)
    {
        $user = $this->getter->get_user_by_api_key($api_key);
        if ($this->authentication->owns_bucket($user['id'], $bucket_id) || $this->authentication->api_key_is_admin($api_key)) {
            pg_prepare($this->dbconn, 'fetch_bucket', 'SELECT * FROM buckets WHERE id = $1');
            $bucket_details = pg_fetch_array(pg_execute($this->dbconn, 'fetch_bucket', array($bucket_id)));
            $delete_bucket = $this->s3->deleteBucket(['Bucket' => $bucket_details['bucket']]);
            pg_prepare($this->dbconn, 'delete_bucket', 'DELETE FROM buckets WHERE id = $1');
            pg_execute($this->dbconn, 'delete_bucket', array($bucket_id));

            return [
                'success' => true,
            ];
        } else {
            return [
                'success' => false,
                'error' => [
                    'error_message' => 'Unauthorized',
                ],
            ];
        }
    }

    public function add_user($username, $bucket_id)
    {
        pg_prepare($this->dbconn, 'fetch_user_by_username', 'SELECT * FROM users WHERE is_blocked IS NOT true AND username = $1 LIMIT 1');
        $user = pg_fetch_array(pg_execute($this->dbconn, 'fetch_user_by_username', array($username)));
    
        if(!empty($user['id'])){
            pg_prepare($this->dbconn, 'fetch_bucket_data', 'SELECT data FROM buckets WHERE id = $1');
            $bucket_data = pg_fetch_array(pg_execute($this->dbconn, 'fetch_bucket_data', array($bucket_id)));
            $bucket_data = json_decode($bucket_data[0], true);

            $bucket_data['users'][$user['id']] = [
                'permissions' => [
                    'rlapi.custom.bucket.permission.priority' => 1, /* Highest priority, soft-limit of 200 (Bucket Owner). Priorities can be used to allow modification of lower-priority users' permissions. */
                    'rlapi.custom.bucket.upload' => true, /* bool, can upload */
                    'rlapi.custom.bucket.manage' => false, /* bool, can manage general bucket settings */
                    'rlapi.custom.bucket.users.get' => false, /* bool, can get a list of users in the bucket */
                    'rlapi.custom.bucket.user.add' => false, /* bool, can add a user */
                    'rlapi.custom.bucket.user.remove' => false, /* bool, can remove a user */
                    'rlapi.custom.bucket.user.block' => false, /* bool, can block a user (set upload permission to false) */
                    'rlapi.custom.bucket.user.unblock' => false, /* bool, can unblock a user (set upload permission to true) */
                    'rlapi.custom.bucket.file.delete' => 'none', /* all, self, none*/
                ],
            ];

            $bucket_data = json_encode($bucket_data);

            pg_prepare($this->dbconn, 'insert_updated_bucket_data', 'UPDATE buckets SET data = $1 WHERE id = $2');
            pg_execute($this->dbconn, 'insert_updated_bucket_data', array($bucket_data, $bucket_id));

            return [
                'success' => true,
                'buckets' => [
                    $bucket_id => [
                        'users' => [
                            'added' => [
                                $username => [
                                    'permissions' => [
                                        'rlapi.custom.bucket.permission.priority' => 1, 
                                        'rlapi.custom.bucket.upload' => true,
                                        'rlapi.custom.bucket.manage' => false,
                                        'rlapi.custom.bucket.users.get' => false, 
                                        'rlapi.custom.bucket.user.add' => false,
                                        'rlapi.custom.bucket.user.remove' => false,
                                        'rlapi.custom.bucket.user.block' => false, 
                                        'rlapi.custom.bucket.user.unblock' => false,
                                        'rlapi.custom.bucket.file.delete' => 'none', 
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ];
        }else{
            return ['success' => false, 'error_message' => 'invalid username'];
        }
    }

    public function get_users($bucket_id)
    {
        pg_prepare($this->dbconn, 'fetch_bucket_data_get_users', 'SELECT data FROM buckets WHERE id = $1');
        $bucket_data = pg_fetch_array(pg_execute($this->dbconn, 'fetch_bucket_data_get_users', array($bucket_id)));
        
        $bucket_data = json_decode($bucket_data[0], true);

        return $bucket_data['users'];
    }

    public function bucket_exists($bucket_name)
    {
        pg_prepare($this->dbconn, 'bucket_exists', 'SELECT COUNT(*) FROM buckets WHERE bucket = $1');
        $array = pg_fetch_array(pg_execute($this->dbconn, 'bucket_exists', array($bucket_name)));

        if (1 == $array[0]) {
            return true;
        } else {
            return false;
        }
    }

    public function user_is_in_bucket($api_key, $bucket_id)
    {
        pg_prepare($this->dbconn, 'fetch_user_is_in_bucket', 'SELECT * FROM users WHERE is_blocked IS NOT true AND id = (SELECT user_id FROM tokens WHERE token = $1 LIMIT 1) LIMIT 1');
        $user = pg_fetch_array(pg_execute($this->dbconn, 'fetch_user_is_in_bucket', array($api_key)));

        pg_prepare($this->dbconn, 'fetch_bucket_data', 'SELECT data FROM buckets WHERE id = $1');
        $bucket_data = pg_fetch_array(pg_execute($this->dbconn, 'fetch_bucket_data', array($bucket_id)));
        $bucket_data = json_decode($bucket_data[0], true);

        if (array_key_exists($user['id'], $bucket_data['users'])) {
            return true;
        }else{
            return false;
        }
    }

    public function get_permissions($api_key, $bucket_id)
    {
        pg_prepare($this->dbconn, 'fetch_user_get_permissions', 'SELECT * FROM users WHERE is_blocked IS NOT true AND id = (SELECT user_id FROM tokens WHERE token = $1 LIMIT 1) LIMIT 1');
        $user = pg_fetch_array(pg_execute($this->dbconn, 'fetch_user_get_permissions', array($api_key)));
    
        pg_prepare($this->dbconn, 'fetch_bucket_data_get_permissions', 'SELECT data FROM buckets WHERE id = $1');
        $bucket_data = pg_fetch_array(pg_execute($this->dbconn, 'fetch_bucket_data_get_permissions', array($bucket_id)));
        $bucket_data = json_decode($bucket_data[0], true);

        if (array_key_exists($user['id'], $bucket_data['users'])) {
            return $bucket_data['users'][$user['id']]['permissions'];
        }else{
            return [false];
        }
    }
}
