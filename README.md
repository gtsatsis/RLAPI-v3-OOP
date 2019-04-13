# RLAPI
### An open-source file hosting API
----

[![Security Rating](https://sonar.ratelimited.me/api/project_badges/measure?project=RLAPI-v3&metric=security_rating)](https://sonar.ratelimited.me/dashboard?id=RLAPI-v3)

RLAPI is an API developed for [RATELIMITED](https://ratelimited.me), a file hosting service started by [George Tsatsis](https://github.com/gtsatsis) in early 2017.

### Stability

RLAPI is currently in a very early stage of development. There will be bugs, and there's going to be a ton of changes.

### Setup

Requirements:

	1. Know-how regarding Linux, as we do not support running RLAPI on Windows environments.
	2. Apache & PHP (Stable on 7.2).
	3. A [Sentry](https://sentry.io) DSN.
	4. [Minio](https://minio.io), or AWS S3.
	5. An SMTP server.
	6. Composer.

Installing:
	
	1. Clone this repository, and point your Apache VirtualHost to `/public/`.
	2. Run `composer install`, and wait until all dependancies have been installed.
	3. Import the `rlapi_devel.pgsql` file into Postgres.
	4. Set up the variables inside `.env`, see `.env.example` for info.
	5. Create an account and an API key, then verify and upload.

### Security
Found a security issue? Want to make sure it's patched? Contact us via our [HackerOne Page](https://hackerone.com/ratelimited)

### Original Development Team

- [gtsatsis](https://github.com/gtsatsis)
- [Sxribe](https://github.com/Sxribe) (RESIGNED)
- [SamuelCSimao](https://github.com/samueldcs)
