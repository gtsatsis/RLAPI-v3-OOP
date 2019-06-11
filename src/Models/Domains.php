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

    public function add_domain($api_key, $domain, $wildcard, $public)
    {
        $users = new User();

        if ($this->authentication->api_key_is_admin($api_key)) {
            if ($this->is_valid_domain_name($domain)) {
                $id = Uuid::uuid4();
                $id = $id->toString();

                $verification_hash = $this->generate_domain_verification_hash($domain);
                $user = $users->get_user_by_api_key($api_key);

                if (true == $wildcard) {
                    $wildcard = 't';
                } else {
                    $wildcard = 'f';
                }

                if (true == $public) {
                    $public = 't';
                } else {
                    $public = 'f';
                }

                pg_prepare($this->dbconn, 'insert_domain', 'INSERT INTO domains (id, user_id, api_key, domain_name, official, wildcard, public, verified, verification_hash) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9)');
                $execute = pg_execute($this->dbconn, 'insert_domain', array($id, $user['id'], $api_key, $domain, 'f', $wildcard, $public, 'f', $verification_hash));
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

        $dnsController = new \Spatie\Dns\Dns($txt_record['name']);
        $dns_record = $dnsController->getRecords('TXT');

        $extract_quote_part = preg_match("/(?:(?:\"(?:\\\\\"|[^\"])+\")|(?:'(?:\\\'|[^'])+'))/is", $dns_record, $extract_quote_part);
        if ($extract_quote_part == '"'.$txt_record['contents'].'"') {
            pg_prepare($this->dbconn, 'verify_domain', 'UPDATE domains SET verified = true WHERE domain_name = $1');
            pg_execute($this->dbconn, 'verify_domain', array($domain));

            return [
                'domains' => [
                    $domain => [
                        'verified' => true,
                    ],
                ],
            ];
        } else {
            return [
                'domains' => [
                    $domain => [
                        'verified' => false,
                    ],
                ],
            ];
        }
    }
}
