<?php

namespace App\Utils;

use Symfony\Component\Dotenv\Dotenv;

// Written by BrightSkyz

class EncryptionUtils
{
    private $encryptionPasswordDictionary;

    public function __construct()
    {
        // Load the env file
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__.'/../../.env');

        $this->encryptionPasswordDictionary = getenv('ENCRYPTION_PASSWORD_DICTIONARY');
    }

    public function generateRandomPassword($length = 10)
    {
        // Generate a random password
        $charactersLength = strlen($this->encryptionPasswordDictionary);
        $randomPassword = '';
        for ($i = 0; $i < $length; ++$i) {
            $randomPassword .= $this->encryptionPasswordDictionary[rand(0, $charactersLength - 1)];
        }

        return $randomPassword;
    }

    public function encryptData($data, $key = null, $outFile = '', $cipher = 'aes-128-gcm')
    {
        // Generate password if blank
        if (null == $key) {
            $key = $this->generateRandomPassword(10);
        }

        // Encryption magic
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $encrypted = openssl_encrypt($data, $cipher, $key, $options = 0, $iv, $tag);

        // Return response based on if you output the data to a file
        if ('' == $outFile) {
            // Return the data in response (and not save to disk)
            return array(
                'success' => true,
                'fileOut' => false,
                'data' => json_encode(array(
                    'cipher' => $cipher,
                    'iv' => bin2hex($iv),
                    'tag' => bin2hex($tag),
                    'data' => $encrypted,
                )),
                'password' => $key,
            );
        } else {
            // Return minimal response (save data to disk instead of returning it)
            if (file_exists($outFile)) {
                unlink($outFile);
            }
            file_put_contents($outFile, json_encode(
                array(
                    'cipher' => $cipher,
                    'iv' => bin2hex($iv),
                    'tag' => bin2hex($tag),
                    'data' => $encrypted,
                )
            ));

            return array(
                'success' => true,
                'fileOut' => true,
                'password' => $key,
            );
        }
    }

    public function decryptData($data, $key)
    {
        $json = json_decode($data, true);

        return openssl_decrypt($json['data'], $json['cipher'], $key, $options = 0, hex2bin($json['iv']), hex2bin($json['tag']));
    }
}
