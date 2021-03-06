<?php

namespace App\Utils;

use Symfony\Component\Dotenv\Dotenv;

class SqreenLib
{
    public function __construct()
    {
        /* Load the env file */
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__.'/../../.env');
    }

    public function sqreen_auth_track($success, $identifier)
    {
        if (getenv('SQREEN_ENABLED')) {
            \sqreen\auth_track($success, ['email' => $identifier]);
        }
    }

    public function sqreen_signup_track($identifier)
    {
        if (getenv('SQREEN_ENABLED')) {
            \sqreen\signup_track(['email' => $identifier]);
        }
    }

    public function sqreen_track_upload($identifier)
    {
        if (getenv('SQREEN_ENABLED')) {
            \sqreen\track('app.ratelimited.rlapi.upload', ['properties' => ['email' => $identifier]]);
        }
    }

    public function sqreen_track_shorten($identifier)
    {
        if (getenv('SQREEN_ENABLED')) {
            \sqreen\track('app.ratelimited.rlapi.shorten', ['properties' => ['email' => $identifier]]);
        }
    }

    public function sqreen_track_file_delete()
    {
        if (getenv('SQREEN_ENABLED')) {
            \sqreen\track('app.ratelimited.rlapi.file.deleted');
        }
    }

    public function sqreen_track_password_reset()
    {
        if (getenv('SQREEN_ENABLED')) {
            \sqreen\track('app.reset_password_request');
        }
    }

    public function sqreen_track_user_deletion()
    {
        if (getenv('SQREEN_ENABLED')) {
            \sqreen\track('user.account_deleted');
        }
    }
}
