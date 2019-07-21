<?php

namespace App\Utils;

require_once __DIR__.'/../../vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

class Mailer
{
    public function __construct()
    {
        /* Load the env file */
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__.'/../../.env');
    }

    public function send_verification_email(string $user_email, string $user_id, string $username, string $verification_id)
    {
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__.'/../../templates/');
        $twig = new \Twig\Environment($loader, [
                'cache' => __DIR__.'/../../templates_c/',
        ]);

        $transport = (new \Swift_SmtpTransport(getenv('SMTP_SERVER'), getenv('SMTP_PORT'), 'tls'))
                ->setUsername(getenv('SMTP_USERNAME'))
                ->setPassword(getenv('SMTP_PASSWORD'));

        $mailer = new \Swift_Mailer($transport);

        $message = (new \Swift_Message('Please verify your e-mail to continue using RATELIMITED'))
                ->setFrom(getenv('SUPPORT_EMAIL'))
                ->setTo($user_email)
                ->setSubject('Please verify your e-mail to continue using RATELIMITED')
                ->setBody(
                        $twig->render(
                                'emails/verify.html.twig',
                                [
                                    'username' => $username,
                                    'user_id' => $user_id,
                                    'verification_id' => $verification_id,
                                ]
                        ),
                        'text/html');

        $mailer->send($message);
    }
}
