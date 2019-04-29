<?php

namespace App\Controller;

require_once __DIR__.'/../../vendor/autoload.php';

use App\Models\Admin;
use App\Utils\Auth;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * Matches /admin/verify_email exactly.
     *
     * @Route("/admin/verify_email", name="verify_email")
     */
    public function verify_email(Request $request)
    {
        $admin = new Admin();
        $auth = new Auth();

        if ($request->request->has('api_key') && $request->request->has('password') && $request->request->has('email')) {
            if ($auth->isValidUUID($request->request->get('api_key'))) {
                $verify_email = $admin->verify_user_emails($request->request->get('api_key'), $request->request->get('password'), $request->request->get('email'));

                $response = new Response(json_encode($verify_email));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            } else {
                $response = new Response(json_encode(array('success' => false, 'error_code' => 1083)));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        } else {
            $response = new Response(json_encode(array('success' => false, 'error_code' => 1082)));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }

    /**
     * Matches /admin/delete_user exactly.
     *
     * @Route("/admin/delete_user", name="delete_user_admin")
     */
    public function delete_user(Request $request)
    {
        $admin = new Admin();
        $auth = new Auth();

        if ($request->request->has('api_key') && $request->request->has('password') && $request->request->has('email') && $request->request->has('user_id')) {
            if ($auth->isValidUUID($request->request->get('api_key'))) {
                $delete_user = $admin->delete_user($request->request->get('api_key'), $request->request->get('password'), $request->request->get('email'), $request->request->get('user_id'));

                $response = new Response(json_encode($delete_user));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            } else {
                $response = new Response(json_encode(array('success' => false, 'error_code' => 1083)));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        } else {
            $response = new Response(json_encode(array('success' => false, 'error_code' => 1082)));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }

    /**
     * Matches /admin/get/userid/{email} exactly.
     *
     * @Route("/admin/get/userid/{email}", name="get_user_userid")
     */
    public function get_user_userid(Request $request, $email)
    {
        $admin = new Admin();
        $auth = new Auth();

        if ($request->request->has('api_key') && $request->request->has('password')) {
            if ($auth->isValidUUID($request->request->get('api_key'))) {
                $get_userId = $admin->get_userId_by_email($request->request->get('api_key'), $request->request->get('password'), $email);

                $response = new Response(json_encode($active_promos));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            } else {
                $response = new Response(json_encode(array('success' => false, 'error_code' => 1083)));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        } else {
            $response = new Response(json_encode(array('success' => false, 'error_code' => 1082)));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }

    /**
     * Matches /admin/promos/active exactly.
     *
     * @Route("/admin/promos/active", name="active_promos")
     */
    public function active_promos(Request $request)
    {
        $admin = new Admin();
        $auth = new Auth();

        if ($request->request->has('api_key') && $request->request->has('password')) {
            if ($auth->isValidUUID($request->request->get('api_key'))) {
                $active_promos = $admin->get_all_active_promos($request->request->get('api_key'), $request->request->get('password'));

                $response = new Response(json_encode($active_promos));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            } else {
                $response = new Response(json_encode(array('success' => false, 'error_code' => 1083)));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        } else {
            $response = new Response(json_encode(array('success' => false, 'error_code' => 1082)));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }

    /**
     * Matches /admin/promos/create exactly.
     *
     * @Route("/admin/promos/create", name="create_promo")
     */
    public function create_promo(Request $request)
    {
        $admin = new Admin();
        $auth = new Auth();

        if ($request->request->has('api_key') && $request->request->has('password') && $request->request->has('promo_code') && $request->request->has('promo_max_uses') && $request->request->has('promo_tier')) {
            if ($auth->isValidUUID($request->request->get('api_key'))) {
                $create_promo = $admin->create_promo($request->request->get('api_key'), $request->request->get('password'), $request->request->get('promo_code'), $request->request->get('promo_max_uses'), $request->request->get('promo_tier'));

                $response = new Response(json_encode($create_promo));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            } else {
                $response = new Response(json_encode(array('success' => false, 'error_code' => 1083)));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        } else {
            $response = new Response(json_encode(array('success' => false, 'error_code' => 1082)));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }
}
