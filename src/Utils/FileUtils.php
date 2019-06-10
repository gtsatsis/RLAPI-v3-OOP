<?php

namespace App\Utils;

use App\Models\User;
use Symfony\Component\Dotenv\Dotenv;

class FileUtils
{
    private $dbconn;

    public function __construct()
    {
        /* Load the env file */
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__.'/../../.env');
        /* Connect to database */
        $this->dbconn = pg_connect('host='.getenv('DB_HOST').' port=5432 dbname='.getenv('DB_NAME').' user='.getenv('DB_USERNAME').' password='.getenv('DB_PASSWORD'));
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

    public function log_object($api_key, $file_name, $file_original_name, $file_md5_hash, $file_sha1_hash)
    {
        $users = new User();

        $user_id = $users->get_user_by_api_key($api_key);

        $user_id = $user_id['id'];
        if (!empty($user_id)) {
            pg_prepare($this->dbconn, 'log_object', 'INSERT INTO files VALUES ($1, $2, $3, $4, $5, $6, $7)');
            $executePreparedStatement = pg_execute($this->dbconn, 'log_object', array($file_name, $file_original_name, time(), $user_id, $api_key, $file_md5_hash, $file_sha1_hash));

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
            pg_prepare($this->dbconn, 'log_short', 'INSERT INTO shortened_urls VALUES ($1, $2, $3, $4, $5, $6, $7)');
            /* user_id, token, short_id, short_name, url, url_safe, timestamp */
            $executePreparedStatement = pg_execute($this->dbconn, 'log_short', array($user_id[0], $api_key, $id, $short_name, $url, $url_safe, time()));

            if ($executePreparedStatement) {
                return true;
            } else {
                throw new \Exception('Something went wrong while inserting a shortened url into the database.');
            }
        }
    }
}
