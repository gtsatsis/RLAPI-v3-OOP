<?php

namespace App\Controller;

require_once __DIR__.'/../../vendor/autoload.php';

use App\Models\Buckets;
use App\Utils\Auth;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BucketsController extends AbstractController
{
    /**
     * Matches /buckets/create exactly.
     *
     * @Route("/buckets/create", name="create_user_bucket")
     */
    public function create_user_bucket(Request $request, $id)
    {
        $auth = new Auth();
        $buckets = new Buckets();

        if ($auth->isValidUUID($id)) {
            if ($request->request->has('bucket_name') && $request->request->has('username') && $request->request->has('password')) {
                $new_bucket = $buckets->create_new_user_bucket($request->request->get('bucket_name'), $request->request->get('username'), $request->request->get('password'));

                $response = new Response(json_encode($new_bucket));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            } else {
                $response = new Response(json_encode(array('success' => false, 'error_code' => 1082)));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        } else {
            $response = new Response(json_encode(array('success' => false, 'error_code' => 1083)));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }

    /**
     * Matches /buckets/{bucket_id}/delete exactly.
     *
     * @Route("/buckets/{bucket_id}/delete", name="delete_user_bucket")
     */
    public function delete_user_bucket(Request $request, $id)
    {
        $auth = new Auth();
        $buckets = new Buckets();

        if ($auth->isValidUUID($id)) {
            if ($request->request->has('username') && $request->request->has('password')) {
                $deleted_bucket = $buckets->delete_user_bucket($bucket_id, $request->request->get('username'), $request->request->get('password'));

                $response = new Response(json_encode($deleted_bucket));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            } else {
                $response = new Response(json_encode(array('success' => false, 'error_code' => 1082)));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        } else {
            $response = new Response(json_encode(array('success' => false, 'error_code' => 1083)));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }
}
