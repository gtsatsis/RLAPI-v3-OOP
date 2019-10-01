<?php

namespace App\Controller;

require_once __DIR__.'/../../vendor/autoload.php';

use App\Models\Domains;
use App\Models\Stats;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * Matches / exactly.
     *
     * @Route("/", name="index_page", methods={"GET"})
     */
    public function index_page()
    {
        $stats = new Stats();

        $stats_array = $stats->getStats();

        $information_array = [
            'instance_info' => [
                'name' => getenv('INSTANCE_NAME'),
                'url' => getenv('INSTANCE_URL'),
                'contact' => getenv('INSTANCE_CONTACT'),
                'has_file_handler' => getenv('INSTANCE_FILE_HANDLER_ENABLED'),
            ],
            'instance_stats' => [
                $stats_array,
            ],
            'software_info' => [
                'name' => 'RLAPI',
                'version' => 3,
            ],
        ];

        if (getenv('SECURITY_TXT_ENABLED')) {
            $information_array['instance_info']['security'] = getenv('INSTANCE_URL').'.well-known/security.txt';
        }

        $response = new Response(json_encode($information_array));

        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode(200);

        return $response;
    }

    /**
     * Matches /.well-known/security.txt.
     *
     * @Route("/.well-known/security.txt", name="security_text", methods={"GET"})
     */
    public function security_text()
    {
        if (getenv('SECURITY_TXT_ENABLED')) {
            $response = new Response('Contact: '.getenv('SECURITY_CONTACT')."\n".
                'Acknowledgments: '.getenv('SECURITY_ACKNOWLEDGEMENTS')."\n".
                'Preferred-Languages: en'."\n".
                'Canonical: '.getenv('INSTANCE_URL').'.well-known/security.txt'."\n".
                'Policy: '.getenv('SECURITY_POLICY'));

            $response->headers->set('Content-Type', 'text/plain');
            $response->setStatusCode(200);
        } else {
            $response = new Response(json_encode([
                'error_message' => 'security_dot_txt_not_enabled',
            ]));

            $response->headers->set('Content-Type', 'application/json');
            $response->setStatusCode(501);
        }

        return $response;
    }

    /**
     * Matches /upload exactly.
     *
     * @Route("/upload", name="upload_index_page")
     */
    public function upload_index_page()
    {
        $response = new Response(json_encode([
            'error_message' => 'route_not_in_use',
        ]));

        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode(404);

        return $response;
    }

    /**
     * Matches /status exactly.
     *
     * @Route("/status", name="status_page", methods={"GET"})
     */
    public function status_page()
    {
        $response = new Response(json_encode([
            'success' => 'true',
            'code' => 200,
        ]));

        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode(200);

        return $response;
    }

    /**
     * Matches /domains/list exactly.
     *
     * @Route("/domains", name="domains", methods={"GET"})
     * @Route("/domains/list", name="domains_list", methods={"GET"})
     */
    public function domains_list()
    {
        $domains = new Domains();

        $list_domains = $domains->list_domains();

        $response = new Response(json_encode($list_domains));

        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode(200);

        return $response;
    }

    /**
     * Matches /stats exactly.
     *
     * @Route("/stats", name="node_stats", methods={"GET"})
     */
    public function node_stats()
    {
        $stats = new Stats();

        $stats_array = $stats->getStats();

        $response = new Response(json_encode($stats_array));

        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode(200);

        return $response;
    }
}
