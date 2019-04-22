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
     * Matches /buckets/{id}/create exactly.
     *
     * @Route("/buckets/{id}/create", name="create_user_bucket")
     */
    public function create_user_bucket(Request $request, $id)
    {
        $auth = new Auth();
        $buckets = new Buckets();

        if ($auth->isValidUUID($id)) {
            if ($request->request->has('bucket_name') && $request->request->has('password')) {
                $new_bucket = $buckets->create_new_user_bucket($id, $request->request->get('bucket_name'), $request->request->get('password'));

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
     * Matches /buckets/{id}/delete exactly.
     *
     * @Route("/buckets/{id}/delete", name="delete_user_bucket")
     */
    public function delete_user_bucket(Request $request, $id)
    {
        $auth = new Auth();
        $buckets = new Buckets();

        if ($auth->isValidUUID($id)) {
            if ($request->request->has('bucket_name') && $request->request->has('password')) {
                $deleted_bucket = $buckets->delete_user_bucket($id, $request->request->get('bucket_name'), $request->request->get('password'));

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

    /**
     * Matches /buckets/{id}/assign_domain exactly.
     *
     * @Route("/buckets/{id}/assign_domain", name="assign_domain_to_bucket")
     */
    public function assign_domain_to_bucket(Request $request, $id)
    {
        $auth = new Auth();
        $buckets = new Buckets();

        if ($auth->isValidUUID($id)) {
            if ($request->request->has('bucket_name') && $request->request->has('password') && $request->request->has('domain')) {
                $assign_domain = $buckets->assign_domain_to_bucket($id, $request->request->get('password'), $request->request->get('bucket_name'), $request->request->get('domain'));

                $response = new Response(json_encode($assign_domain));
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
