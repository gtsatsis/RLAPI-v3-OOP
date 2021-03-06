<?php

namespace App\Utils;

use App\Models\User;
use Aws\S3\S3Client;
use Symfony\Component\Dotenv\Dotenv;

class FileUtils
{
    private $dbconn;

    private $sqreen;

    public function __construct()
    {
        /* Load the env file */
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__.'/../../.env');
        /* Connect to database */
        $this->dbconn = pg_connect('host='.getenv('DB_HOST').' port=5432 dbname='.getenv('DB_NAME').' user='.getenv('DB_USERNAME').' password='.getenv('DB_PASSWORD'));
        $this->sqreen = new SqreenLib();
    }

    public function generateFileName($extension)
    {
        // Generate a random name
        $fileName = substr(str_shuffle(str_repeat(getenv('FILENAME_DICTIONARY'), getenv('FILENAME_LENGTH'))), 0, getenv('FILENAME_LENGTH'));

        // Add file extension
        $fileName .= '.'.$extension;

        return $fileName;
    }

    public function generateShortName()
    {
        // Generate a random name
        $short_name = substr(str_shuffle(str_repeat(getenv('SHORTENER_DICTIONARY'), getenv('SHORTENER_LENGTH'))), 0, getenv('SHORTENER_LENGTH'));

        return '~.'.$short_name; // Shortener identifies shortURL vs fileName by using `~.` as the "filename" and the extension as the identifier. Hacky, but works.
    }

    public function isUnique($filename)
    {
        $statement = pg_prepare($this->dbconn, 'is_filename_unique', 'SELECT COUNT(*) FROM files WHERE filename = $1');
        $executePreparedStatement = pg_execute($this->dbconn, 'is_filename_unique', array($filename));

        $result = pg_fetch_array($executePreparedStatement);

        if (0 == $result[0]) {
            return true;
        }

        return false;
    }

    public function isShortUnique($short_name)
    {
        $statement = pg_prepare($this->dbconn, 'is_shortname_unique', 'SELECT COUNT(*) FROM shortened_urls WHERE short_name = $1');
        $executePreparedStatement = pg_execute($this->dbconn, 'is_shortname_unique', array($short_name));

        $result = pg_fetch_array($executePreparedStatement);

        if (0 == $result[0]) {
            return true;
        }

        return false;
    }

    public function log_object($api_key, $file_name, $file_original_name, $file_md5_hash, $file_sha1_hash, $bucket, $encrypted)
    {
        $users = new User();

        $user_id = $users->get_user_by_api_key($api_key);

        $user_id = $user_id['id'];
        if (!empty($user_id)) {
            pg_prepare($this->dbconn, 'log_object', 'INSERT INTO files (filename, originalfilename, timestamp, user_id, token, md5, sha1, deleted, bucket, encrypted) VALUES ($1, $2, $3, $4, $5, $6, $7, false, $8, $9)');
            $executePreparedStatement = pg_execute($this->dbconn, 'log_object', array($file_name, $file_original_name, time(), $user_id, $api_key, $file_md5_hash, $file_sha1_hash, $bucket, $encrypted));

            if ($executePreparedStatement) {
                return true;
            } else {
                throw new \Exception('Something went wrong while inserting a file into the database.');
            }
        }
    }

    public function log_short($api_key, $id, $short_name, $url, $url_safe)
    {
        $users = new User();

        $user_id = $users->get_user_by_api_key($api_key);

        if (!empty($user_id)) {
            pg_prepare($this->dbconn, 'log_short', 'INSERT INTO shortened_urls (user_id, token, id, short_name, url, url_safe, timestamp) VALUES ($1, $2, $3, $4, $5, $6, $7)');
            /* user_id, token, short_id, short_name, url, url_safe, timestamp */
            $executePreparedStatement = pg_execute($this->dbconn, 'log_short', array($user_id[0], $api_key, $id, $short_name, $url, $url_safe, time()));

            if ($executePreparedStatement) {
                return true;
            } else {
                throw new \Exception('Something went wrong while inserting a shortened url into the database.');
            }
        }
    }

    public function get_file_owner($file_name, $user_id, $api_key, $bucket)
    {
        $auth = new Auth();

        pg_prepare($this->dbconn, 'get_file_owner', 'SELECT COUNT(*) FROM files WHERE filename = $1 AND token = $2 AND user_id = $3 AND bucket = $4');
        $count = pg_fetch_array(pg_execute($this->dbconn, 'get_file_owner', array($file_name, $api_key, $user_id, $bucket)));

        if (1 == $count[0]) {
            if ($bucket != getenv('S3_BUCKET')) {
                if ('self' == $auth->get_cb_permissions($api_key, $bucket)['rlapi.custom.bucket.file.delete'] || 'all' == $auth->get_cb_permissions($api_key, $bucket)['rlapi.custom.bucket.file.delete']) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    public function delete_file($file_name, $bucket)
    {
        $s3 = new S3Client([
            'version' => 'latest', // Latest S3 version
            'region' => 'us-east-1', // The service's region
            'endpoint' => getenv('S3_ENDPOINT'), // API to point to
            'credentials' => new \Aws\Credentials\Credentials(getenv('S3_API_KEY'), getenv('S3_API_SECRET')), // Credentials
            'use_path_style_endpoint' => true, // Minio Compatible (https://minio.io)
        ]);

        $s3->deleteObject([
            'Bucket' => $bucket,
            'Key' => $file_name,
        ]);

        pg_prepare($this->dbconn, 'mark_file_as_deleted', 'UPDATE files SET deleted = true WHERE filename = $1 AND bucket = $2');
        pg_execute($this->dbconn, 'mark_file_as_deleted', array($file_name, $bucket));

        $this->sqreen->sqreen_track_file_delete();

        return [
            'success' => true,
        ];
    }

    public function check_object_against_hashlist($md5, $sha1)
    {
        pg_prepare($this->dbconn, 'select_from_hashlist', 'SELECT md5, sha1, reason, COUNT(*) FROM blocked_hashes WHERE md5 = $1 AND sha1 = $2 GROUP BY md5, sha1, reason');
        $results = pg_fetch_array(pg_execute($this->dbconn, 'select_from_hashlist', array($md5, $sha1)));

        if ($results['count'] >= 1) {
            if ('cp' == $results['reason']) {
                return [
                    'clearance' => false,
                    'reason' => 'cp',
                ];
            } elseif ('banned' == $results['reason']) {
                return [
                    'clearance' => false,
                    'reason' => 'banned',
                ];
            } else {
                return [
                    'clearance' => false,
                    'reason' => $results['reason'], /* Fallback */
                ];
            }
        } else {
            return [
                'clearance' => true,
            ];
        }
    }
}
