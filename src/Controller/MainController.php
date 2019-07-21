<?php

namespace App\Controller;

require_once __DIR__.'/../../vendor/autoload.php';

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class MainController extends AbstractController
{
    /**
     * Matches / exactly.
     *
     * @Route("/", name="index_page")
     */
    public function index_page()
    {
        return new Response('You have reached the RLAPI v3.0 Index Page');
    }

    /**
     * Matches /upload exactly.
     *
     * @Route("/upload", name="upload_index_page")
     */
    public function upload_index_page()
    {
        return new Response('Upload Index');
    }

    /**
     * Matches /status exactly.
     *
     * @Route("/status", name="status_page")
     */
    public function status_page()
    {
        $response = new Response(json_encode([
            'success' => 'true',
            'code' => 200,
        ]));

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Matches /api exactly.
     *
     * @Route("/api", name="api_endpoint_deprecated")
     */
    public function api_endpoint_deprecated()
    {
        $response = new Response(json_encode([
            'message' => 'api_endpoint_deprecated',
        ]));

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
