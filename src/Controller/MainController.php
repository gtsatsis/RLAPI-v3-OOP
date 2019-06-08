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
     * @Route("/", name="index_page")
     */
    public function index_page()
    {
        $stats = new Stats();

        $stats_array = $stats->getStats();

        $response = new Response(json_encode([
            'instance_info' => [
                'name' => getenv('INSTANCE_NAME'),
                'url' => getenv('INSTANCE_URL'),
                'contact' => getenv('INSTANCE_CONTACT'),
                'has_file_handler' => getenv('INSTANCE_FILE_HANDLER_ENABLED'),
            ],
            'instance_stats' => [
                $stats_array,
            ],
            'sotware_info' => [
                'name' => 'RLAPI',
                'version' => 3,
            ],
        ]));

        $response->headers->set('Content-Type', 'application/json');

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

        return $response;
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

    /**
     * Matches /domains exactly.
     *
     * @Route("/domains", name="domains")
     */
    public function domains()
    {
        $response = new Response(json_encode([
            'message' => 'check_domains/list',
        ]));

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Matches /domains/list exactly.
     *
     * @Route("/domains/list", name="domains_list")
     */
    public function domains_list()
    {
        $domains = new Domains();

        $list_domains = $domains->list_domains();

        $response = new Response(json_encode($list_domains));

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Matches /stats exactly.
     *
     * @Route("/stats", name="node_stats")
     */
    public function node_stats()
    {
        $stats = new Stats();

        $stats_array = $stats->getStats();

        $response = new Response(json_encode($stats_array));

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
