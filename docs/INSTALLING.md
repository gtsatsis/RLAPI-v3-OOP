# Installing RLAPI

## Requirements

Make sure you have `composer` installed. Run `composer install`, however if you have worked with it before, you probably already knew this.

You'll need [Minio](https://min.io/) for buckets, and [PostgreSQL](https://www.postgresql.org/) for the database.

Optionally, you may also include your [Bugsnag](https://bugsnag.com), [Sentry](https://sentry.io), and [Sqreen](https://sqreen.com), [reCaptcha](https://www.google.com/recaptcha/admin), and SMTP settings later in the configuration.


## Database Configuration

RLAPI requires a database in order to work, as such, you will have to create a database with a user that has full privileges.

Here's how we create and import the database.

```bash
username@host:~/rlapi$ sudo -u postgres psql

postgres# CREATE USER rlapi_devel WITH PASSWORD 'your_secure_password_here';
postgres# CREATE DATABASE rlapi_devel WITH OWNER rlapi_devel;
postgres# \q

username@host:~/rlapi$ sudo -u postgres psql < rlapi_devel.psql
```

## Configuration

Rename `.env.example` to `.env`.

### `.env` file

The API is mostly configured using the `.env` file, which contains the environment variables used on runtime.

```
APP_ENV=dev # Can either be dev or prod
APP_SECRET= # App Secret
```

Your app secret can be whatever, however I recommend you use some randomly generated string.

Make sure that when you're pushing to production, change `dev` to `prod`

```
DB_HOST=localhost 
DB_NAME=
DB_USERNAME=
DB_PASSWORD=
```

These will be your database settings from when you set up and installed PostgreSQL. Change these according to your own install settings

```
S3_ENDPOINT=http://127.0.0.1:9000 # S3 Endpoint
S3_API_KEY= # S3 Access Key
S3_API_SECRET= # S3 Access Key Secret
S3_BUCKET= # S3 Bucket
```

Set these to your Minio stock preferences. Make sure to create a bucket for RLAPI within Minio (or just use AWS like a cloud-based nerd).

```
FILENAME_DICTIONARY=0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ
FILENAME_LENGTH=10

SHORTENER_DICTIONARY=0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ
SHORTENER_LENGTH=4
```

The default for these are good enough, change them if you need to.

```
INSTANCE_NAME= # API Name (i.e. RATELIMITED, RLAPI)
INSTANCE_URL= # API URL (i.e. https://api.ratelimited.me/)
INSTANCE_CONTACT= # Service Abuse Contact
INSTANCE_FILE_HANDLER_ENABLED= # Does this instance have the File Handler? true/false
```

Set this to your own preference, just make sure YOU ACTUALLY PUT A COMPLIANT ABUSE CONTACT (gotta be compliant, you know? please?)

```
SECURITY_TXT_ENABLED= # true/false - Is the https://securitytxt.org/ policy enabled
SECURITY_CONTACT= # Mailto or URL to your security contact
SECURITY_POLICY= # URL to your security policy
SECURITY_ACKNOWLEDGEMENTS= # Page to your hacker ack's (Thanks)
SECURITY_LANGS= # Comma seperated list of languages for your security contact
```

These are for the auto-generated `/.well-known/security.txt` file that comes with RLAPI.

Make sure they're valid, or just disable it. Broken installations really put a nail in this services coffin.

```
REGISTRATIONS=false # True/False - Provides a way to enable/disable registrations.
PROMOS=false # True/False - Provides a way to issue on-signup codes with limited uses that allow for a free upgrade to another tier
```

Specify your preferences in this config section.

```
TMP_STORE= # Local file location to store files to. Must have trailing slash.
```

Make sure this is a valid directory, as Minio uses this for the correct mime-type DB-side.

```
SMTP_SERVER= # SMTP Server Hostname/IP
SMTP_PORT= # SMTP Server Port
SMTP_USERNAME= # SMTP Login (Username)
SMTP_PASSWORD= # SMTP Login (Password)
SUPPORT_EMAIL= # Send E-Mails From This
```

Set this to work, if you're going to custom-host SMTP, please make sure you have SSL enabled, because it will show up as untrusted/insecure or in the Spam folder.

```
CAPTCHA_ENABLED=false # Captcha Status
RECAPTCHA_SECRET=null # Google Recaptcha Secret
```

This is for Recaptcha (for the dumb, that little `I'm not a robot` checkbox that usually pops up into a image-based quiz that takes 10 minutes to complete). Use it if you want, it's not required, but it DOES help with spam. *Note: It is currently not implemented properly, and has to be re-written.*

Congratulations, you got something working. Follow on to the Addon configuration section below *as if you don't theres about 4 lines that you'll need because things will break if you keep them gone*.

#### Addon configuration

```
SENTRY_DSN= # Sentry DSN, Including password/key
SENTRY_RELEASE= # Sentry Release Version
SQREEN_ENABLED=false # Sqreen Enabled
BUGSNAG_KEY= # Bugsnag API Key
```

`SENTRY_DSN`: The Sentry DSN is the **private** API connection string given by [Sentry](https://sentry.io).

`SENTRY_RELEASE`: This is not mandatory, but is recommended if you make custom changes to the source, as it will help you find out which release caused an issue and when.

`SQREEN_ENABLED`: If you have [Sqreen](https://sqreen.com) installed on your system/PHP installation, you can enable the app-specific playbooks here. If not, no worries.

`BUGSNAG_KEY`: This is the [Bugsnag](https://bugsnag.com) API key, second error tracking platform supported by RLAPI.


### Webserver Configuration

We currently support running RLAPI via Apache `apache2` and NGINX, we will only show you how to install it with NGINX.

Install the `php7.2-fpm nginx` packages, and then move the `php.api.conf` file to `/etc/nginx/sites-available/php.api`.

Change the `server_name` variable to **your server hostname**, and add in your SSL configuration.

### Voila!

After all of this, start your webserver, and the magic smoke will come alive.