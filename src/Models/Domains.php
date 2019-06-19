<?php

namespace App\Models;

require_once __DIR__.'/../../vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
use App\Utils\Auth;
use App\Utils\SqreenLib;
use Ramsey\Uuid\Uuid;

class Domains
{
    private $dbconn;

    private $authentication;

    private $sqreen;

    public function __construct()
    {
        /* Load the env file */
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__.'/../../.env');

        /* Connect to database */
        $this->dbconn = pg_connect('host='.getenv('DB_HOST').' port=5432 dbname='.getenv('DB_NAME').' user='.getenv('DB_USERNAME').' password='.getenv('DB_PASSWORD'));

        $this->authentication = new Auth();

        $this->sqreen = new SqreenLib();
    }

    public function list_domains()
    {
        return pg_fetch_all(pg_query($this->dbconn, 'SELECT id, domain_name, official, wildcard FROM domains WHERE verified = true AND public = true'));
    }

    public function generate_domain_verification_hash($domain)
    {
        return sha1(md5($domain));
    }

    public function is_valid_domain_name($domain_name)
    { /* Code taken from SO: https://stackoverflow.com/a/4694816 */
        return preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain_name) //valid chars check
            && preg_match('/^.{1,253}$/', $domain_name) //overall length check
            && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain_name); //length of each label
    }

    public function domain_already_exists($domain)
    {
        pg_prepare($this->dbconn, 'check_if_domain_already_exists', 'SELECT COUNT(*) FROM domains WHERE domain_name = $1');
        $count = pg_fetch_array(pg_execute($this->dbconn, 'check_if_domain_already_exists', array($domain)));

        if (0 == $count[0]) {
            return false;
        } else {
            return true;
        }
    }

    public function add_domain($api_key, $domain, $wildcard, $public, $bucket)
    {
        $users = new User();

        if ($this->authentication->isValidUUID($api_key)) {
            if ($this->is_valid_domain_name($domain) && !$this->domain_already_exists($domain)) {
                $id = Uuid::uuid4();
                $id = $id->toString();

                $verification_hash = $this->generate_domain_verification_hash($domain);
                $user = $users->get_user_by_api_key($api_key);

                if (true == $wildcard) {
                    $wildcard = 't';
                } else {
                    $wildcard = 'f';
                }

                if (true == $public || $this->authentication->domain_allowance($user['id'])) {
                    $public = 't';
                } else {
                    $public = 'f';
                }

                if ($bucket != getenv('S3_BUCKET')) { /* If the bucket isn't the default one */
                    if ($this->authentication->owns_bucket($user['id'], $bucket)) { /* Check if the bucket specified is owned by this user */
                        $bucket = $bucket; /* If it is, do nothing */
                    } else {
                        $bucket = getenv('S3_BUCKET'); /* Else, set the bucket to default */
                    }
                }

                pg_prepare($this->dbconn, 'insert_domain', 'INSERT INTO domains (id, user_id, api_key, domain_name, official, wildcard, public, verified, verification_hash, bucket) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)');
                $execute = pg_execute($this->dbconn, 'insert_domain', array($id, $user['id'], $api_key, $domain, 'f', $wildcard, $public, 'f', $verification_hash, $bucket));
                if ($execute) {
                    return [
                        'success' => true,
                        'domains' => [
                            $domain => [
                                'owner' => [
                                    'user_id' => $user['id'],
                                    'email' => $user['email'],
                                ],
                                'verification' => [
                                    'verified' => false,
                                    'record' => 'TXT',
                                    'name' => 'rl-verify-'.mb_substr($verification_hash, 0, 4),
                                    'contents' => $verification_hash,
                                ],
                                'bucket' => $bucket,
                            ],
                        ],
                    ];
                } else {
                    throw new Exception('Something went horribly wrong while inserting domain into database');
                }
            }
        } else {
            return [
                'error_message' => 'invalid_credentials',
            ];
        }
    }

    public function verify_domain_txt($domain)
    {
        pg_prepare($this->dbconn, 'domain_verification_array', 'SELECT * FROM domains WHERE domain_name = $1');
        $domain_array = pg_fetch_array(pg_execute($this->dbconn, 'domain_verification_array', array($domain)));

        $verification_hash = $domain_array['verification_hash'];
        $txt_record = [
            'name' => 'rl-verify-'.mb_substr($verification_hash, 0, 4).'.'.$domain,
            'contents' => $verification_hash,
        ];
        if (true == getenv('CLOUDFLARE_DCV_ENABLED')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://dcvcheck.cloudflare.com/check');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, '{ "authToken": "'.getenv('CLOUDFLARE_DCV_TOKEN').'", "method":"TXT", "verbose": true, "params": { "domain": "'.$txt_record['name'].'", "challenge": "'.$txt_record['contents'].'", "expectedResponse": "'.$txt_record['contents'].'" } }');
            curl_setopt($ch, CURLOPT_POST, 1);
            $headers = array();
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Error:'.curl_error($ch);
            }
            curl_close($ch);

            $result = json_decode($result, true);
            $min_allowed_accepted_responses = $result['agentRequests'] / 3;
            if ($result['correctResponses'] >= $min_allowed_accepted_responses) {
                pg_prepare($this->dbconn, 'verify_domain', 'UPDATE domains SET verified = true WHERE domain_name = $1');
                pg_execute($this->dbconn, 'verify_domain', array($domain));

                return [
                    'domains' => [
                        $domain => [
                            'verified' => true,
                            'details' => [
                                'method_used' => 'cloudflare',
                                'total_requests' => $result['agentRequests'],
                                'correct_responses' => $result['correctResponses'],
                                'required_correct' => $min_allowed_accepted_responses,
                            ],
                        ],
                    ],
                ];
            } else {
                return [
                    'domains' => [
                        $domain => [
                            'verified' => false,
                            'details' => [
                                'method_used' => 'cloudflare',
                                'total_requests' => $result['agentRequests'],
                                'correct_responses' => $result['correctResponses'],
                                'required_correct' => $min_allowed_accepted_responses,
                            ],
                        ],
                    ],
                ];
            }
        } else {
            $dnsController = new \Spatie\Dns\Dns($txt_record['name']);
            $dns_record = $dnsController->getRecords('TXT');
        }

        preg_match("/(?:(?:\"(?:\\\\\"|[^\"])+\")|(?:'(?:\\\'|[^'])+'))/is", $dns_record, $extracted_quote_part);
        if ($extracted_quote_part[0] == '"'.$txt_record['contents'].'"') {
            pg_prepare($this->dbconn, 'verify_domain', 'UPDATE domains SET verified = true WHERE domain_name = $1');
            pg_execute($this->dbconn, 'verify_domain', array($domain));

            return [
                'domains' => [
                    $domain => [
                        'verified' => true,
                        'details' => [
                            'method_used' => 'builtin_dns',
                        ],
                    ],
                ],
            ];
        } else {
            return [
                'domains' => [
                    $domain => [
                        'verified' => false,
                        'details' => [
                            'method_used' => 'builtin_dns',
                        ],
                    ],
                ],
            ];
        }
    }

    public function remove_domain($api_key, $domain)
    {
        if ($this->authentication->api_key_is_admin($api_key) || $this->authentication->is_domain_owner($api_key, $domain)) {
            pg_prepare($this->dbconn, 'delete_domain', 'DELETE FROM domains WHERE domain_name = $1');
            pg_execute($this->dbconn, 'delete_domain', array($domain));

            return [
                'success' => true,
            ];
        } else {
            return [
                'success' => false,
                'error_message' => 'insufficient_permissions',
            ];
        }
    }

    public function set_privacy($domain, $api_key, $privacy)
    {
        if ($this->authentication->api_key_is_admin($api_key) || $this->authentication->is_domain_owner($api_key, $domain)) {
            if ('private' == $privacy) {
                $privacy_secondary = 'false';
            } else {
                $privacy_secondary = 'true';
            }

            pg_prepare($this->dbconn, 'set_domain_privacy', 'UPDATE domains SET public = $1 WHERE domain_name = $2');
            pg_execute($this->dbconn, 'set_domain_privacy', array($privacy_secondary, $domain));

            return [
                'success' => true,
                'domains' => [
                    $domain => [
                        'privacy' => $privacy,
                    ],
                ],
            ];
        } else {
            return [
                'success' => false,
                'error_message' => 'not_domain_owner',
            ];
        }
    }

    public function set_official_status($domain, $official)
    {
        if ('true' == $official || 'false' == $official) {
            pg_prepare($this->dbconn, 'set_domain_official_status', 'UPDATE domains SET official = $1 WHERE domain_name = $2');
            pg_execute($this->dbconn, 'set_domain_official_status', array($official, $domain));

            return [
                'success' => true,
                'domains' => [
                    $domain => [
                        'official' => $official,
                    ],
                ],
            ];
        } else {
            return [
                'success' => false,
                'error_message' => 'official_must_be_bool',
            ];
        }
    }

    public function set_domain_bucket($api_key, $domain, $bucket)
    {
        if ($this->authentication->api_key_is_admin($api_key) || $this->authentication->is_domain_owner($api_key, $domain)) {
            if ($this->authentication->owns_bucket_by_name_api_key($api_key, $bucket)) {
                pg_prepare($this->dbconn, 'set_domain_bucket', 'UPDATE domains SET bucket = $1 WHERE domain_name = $2');
                pg_execute($this->dbconn, 'set_domain_bucket', array($bucket, $domain));

                pg_prepare($this->dbconn, 'get_bucket_data', 'SELECT data FROM buckets WHERE bucket = $1');
                $bucket_data = pg_fetch_array(pg_execute($this->dbconn, 'get_bucket_data', array($bucket)));

                $bucket_data = json_decode($bucket_data[0]);
                $bucket_data['domains'][$domain] = ['added' => ['on' => time(), 'by' => $api_key]];

                $bucket_data = json_encode($bucket_data);

                pg_prepare($this->dbconn, 'update_bucket_data', 'UPDATE buckets SET data = $1 WHERE bucket = $2');
                pg_execute($this->dbconn, 'update_bucket_data', array($bucket_data, $bucket));

                return [
                    'success' => true,
                    'domains' => [
                        $domain => [
                            'bucket' => $bucket,
                        ],
                    ],
                ];
            } else {
                return [
                    'success' => false,
                    'error_message' => 'not_bucket_owner',
                ];
            }
        } else {
            return [
                'success' => false,
                'error_message' => 'not_domain_owner',
            ];
        }
    }
}
