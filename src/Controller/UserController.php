<?php

namespace App\Controller;

require_once __DIR__.'/../../vendor/autoload.php';

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Models\User;
use App\Utils\Auth;

class UserController extends AbstractController
{
    /**
     * Matches /users/create exactly.
     *
     * @Route("/users/create", name="create_user")
     */
    public function create_user(Request $request)
    {
        $users = new User();

        if ($request->request->has('username') && $request->request->has('password') && $request->request->has('email')) {
            if ($request->request->has('promo_code')) {
                $createUser = $users->create_user($request->request->get('username'), $request->request->get('password'), $request->request->get('email'), array('promo_code' => $request->request->get('promo_code')));

                $response = new Response(json_encode($createUser));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            } else {
                $createUser = $users->create_user($request->request->get('username'), $request->request->get('password'), $request->request->get('email'), '');

                $response = new Response(json_encode($createUser));
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
     * Matches /users/{id}/delete exactly.
     *
     * @Route("/users/{id}/delete", name="delete_user")
     */
    public function delete_user(Request $request, $id)
    {
        $auth = new Auth();
        if ($auth->isValidUUID($id)) {
            if ($request->request->has('email') && $request->request->has('password')) {
                $users = new User();

                $deleteUser = $users->delete_user($id, $request->request->get('email'), $request->request->get('password'));

                return new Response(json_encode($deleteUser));
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
     * Matches /users/{id}/set_tier exactly.
     *
     * @Route("/users/{id}/set_tier", name="set_user_tier")
     */
    public function set_user_tier(Request $request, $id)
    {
        $users = new User();
        $auth = new Auth();

        if ($auth->isValidUUID($id)) {
            if ($request->request->has('tier') && $request->request->has('api_key')) {
                $setTier = $users->user_set_tier($id, $request->request->get('tier'), $request->request->get('api_key'));

                return new Response(json_encode($setTier));
            } else {
                return new Response(json_encode(array('success' => false, 'error_code' => 1082)));
            }
        } else {
            return new Response(json_encode(array('success' => false, 'error_code' => 1083)));
        }
    }

    /**
     * Matches /users/{id}/email exactly.
     *
     * @Route("/users/{id}/update_email", name="set_user_email")
     */
    public function set_user_email(Request $request, $id)
    {
        $users = new User();
        $auth = new Auth();

        if ($auth->isValidUUID($id)) {
            if ($request->request->has('password') && $request->request->has('newEmail')) {
                $users->user_set_email($id, $request->request->get('newEmail'), $request->request->get('password'));
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
     * Matches /users/{id}/password exactly.
     *
     * @Route("/users/{id}/update_password", name="set_user_password")
     */
    public function update_user_password(Request $request, $id)
    {
        $users = new User();
        $auth = new Auth();

        if ($auth->isValidUUID($id)) {
            if ($request->request->has('password') && $request->request->has('newPassword')) {
                $updatePassword = $users->user_set_password($id, $request->request->get('password'), $request->request->get('newPassword'));

                $response = new Response(json_encode($updatePassword));
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
     * Matches /users/{id}/verify_email/{verification_id} exactly.
     *
     * @Route("/users/{id}/verify_email/{verification_id}", name="set_user_password")
     */
    public function verify_user_email($id, $verification_id)
    {
        $auth = new Auth();

        if ($auth->isValidUUID($id)) {
            if ($auth->isValidUUID($verification_id)) {
                $users = new User();

                $verify_email = $users->user_verify_email($id, $verification_id);

                $response = new Response(json_encode($verify_email));
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
     * Matches /users/reset_password exactly.
     *
     * @Route("/users/reset_password", name="reset_user_password")
     */
    public function reset_user_password(Request $request)
    {
        $user = new User();

        if ($request->request->has('email')) {
            $email = $request->request->get('email');

            $reset_password = $user->reset_password_send($email);

            $response = new Response(json_encode($reset_password));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } else {
            $response = new Response(json_encode(array('success' => false, 'error_code' => 1082)));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }

    /**
     * Matches /users/reset_password/{id} exactly.
     *
     * @Route("/users/reset_password/{id}", name="reset_user_password_act")
     */
    public function reset_user_password_act(Request $request, $id)
    {
        $user = new User();
        $auth = new Auth();

        if ($auth->isValidUUID($id)) {
            if ($request->request->has('password')) {
                $password = $request->request->get('password');

                $reset_password = $user->user_password_reset($id, $password);

                $response = new Response(json_encode($reset_password));
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
     * Matches /users/{id}/uploads exactly.
     *
     * @Route("/users/{id}/uploads", name="get_user_uploads")
     */
    public function get_user_uploads(Request $request, $id)
    {
        $user = new User();
        $auth = new Auth();

        if ($auth->isValidUUID($id)) {
            $get_uploads = $user->get_user_uploads($id);

            $response = new Response(json_encode($get_uploads));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } else {
            $response = new Response(json_encode(array('success' => false, 'error_code' => 1083)));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }
}
