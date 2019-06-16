<?php

namespace App\Controller;

require_once __DIR__.'/../../vendor/autoload.php';

use App\Models\Buckets;
use App\Utils\Auth;
use App\Utils\Getters;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BucketsController extends AbstractController
{

    private $getter;
    private $buckets;
    private $authentication;
    
    public function __construct()
    {
        $this->getter = new Getters();
        $this->buckets = new Buckets();
        $this->authentication = new Auth();
    }
    /**
     * Matches /buckets/create exactly.
     *
     * @Route("/buckets/create", name="create_bucket")
     */
    public function create_bucket(Request $request)
    {
        if($request->request->has('api_key') && $request->request->has('bucket_name')){
            if ($this->authentication->isValidUUID($request->request->get('api_key'))) {
                $create_bucket = $this->buckets->create($request->request->get('api_key'), $request->request->get('bucket_name'));
                $response = new Response(json_encode($create_bucket));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }else{
                $response = new Response(json_encode(['success' => false, 'error' => ['error_message' => 'API key not in UUID format']]));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        }else{
            $response = new Response(json_encode(['success' => false, 'error' => ['error_message' => 'Missing required parameters', 'required' => ['api_key', 'bucket_name']]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

    }

    /**
     * Matches /buckets/{bucket_id}/delete exactly.
     *
     * @Route("/buckets/{bucket_id}/delete", name="delete_bucket")
     */
    public function delete_bucket(Request $request, $bucket_id)
    {
        if($request->request->has('api_key')){
            if ($this->authentication->isValidUUID($request->request->get('api_key'))) {
                $delete_bucket = $this->buckets->delete($request->request->get('api_key'), $bucket_id);
                $response = new Response(json_encode($delete_bucket));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }else{
                $response = new Response(json_encode(['success' => false, 'error' => ['error_message' => 'API key not in UUID format']]));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        }else{
            $response = new Response(json_encode(['success' => false, 'error' => ['error_message' => 'Missing required parameters', 'required' => ['api_key']]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }
}
