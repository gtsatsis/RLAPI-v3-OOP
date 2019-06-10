<?php

namespace App\Controller;

require_once __DIR__.'/../../vendor/autoload.php';

use App\Shortener\Shortener;
use App\Utils\Auth;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShortenerController extends AbstractController
{
    private $auth;

    private $shortener;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->shortener = new Shortener();
    }

    /**
     * Matches /shorten/polr exactly.
     *
     * @Route("/shorten/polr", name="shorten_url_polr")
     * @Route("/shorten", name="shorten_url")
     */
    public function shorten_url(Request $request)
    {
        if ($request->query->has('key') && $request->query->has('url')) {
            if ($this->auth->isValidUUID($request->query->get('key'))) {
                if (!empty($request->query->get('url'))) {
                    if ($request->query->has('custom_ending')) {
                        $shorten = $this->shortener->shorten($request->query->get('key'), $request->query->get('url'), $request->query->get('custom_ending'));
                    } else {
                        $shorten = $this->shortener->shorten($request->query->get('key'), $request->query->get('url'));
                    }

                    $response = new Response(json_encode($shorten));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                } else {
                    $response = new Response(json_encode([
                        'success' => false,
                        'error_message' => 'url_cannot_be_empty',
                    ]));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                }
            } else {
                $response = new Response(json_encode([
                    'success' => false,
                    'error_message' => 'key_not_in_uuid_format',
                ]));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        } else {
            $response = new Response(json_encode([
                'success' => false,
                'error_message' => 'request_must_include_key_and_url',
            ]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }

    /**
     * Matches /shorten/lookup exactly.
     *
     * @Route("/shorten/lookup", name="lookup_url_QS")
     */
    public function lookup(Request $request)
    {
        if ($request->query->has('short_name')) {
            if (!empty($request->query->get('short_name'))) {
                $lookup = $this->shorterner->lookup($request->query->get('short_name'));
                $response = new Response(json_encode($lookup));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            } else {
                $response = new Response(json_encode([
                    'success' => false,
                    'error_message' => 'short_name_must_not_be_empty',
                ]));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        } else {
            $response = new Response(json_encode([
                'success' => false,
                'error_message' => 'request_must_include_short_name',
            ]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }
}
