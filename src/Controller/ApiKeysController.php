<?php
namespace App\Controller;

require_once __DIR__ . '/../../vendor/autoload.php';

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Session\Session;

use App\Models\User;
use App\Models\Apikeys;
use App\Utils\Auth;

class ApiKeysController extends AbstractController {

		/**
     * Matches /users/email_auth/api_keys/create exactly
     *
     * @Route("/users/email_auth/api_keys/create", name="create_user_api_key_email_auth")
     */

	public function create_user_api_key_email_auth(Request $request){
		$api_keys = new Apikeys();
		$auth = new Auth();

		if($request->request->has('email') && $request->request->has('api_key_name') && $request->request->has('password')){
			$new_api_key = $api_keys->create_user_api_key_email_auth($email, $request->request->get('api_key_name'), $request->request->get('password'));

			$response = new Response(json_encode($new_api_key));
			$response->headers->set('Content-Type', 'application/json');

			return $response;

		}else{

			$response = new Response(json_encode(array('success' => false, 'error_code' => 1082)));
			$response->headers->set('Content-Type', 'application/json');

			return $response;
			
			}
	}

	/**
     * Matches /users/{id}/api_keys/create exactly
     *
     * @Route("/users/{id}/api_keys/create", name="create_user_api_key")
     */

	public function create_user_api_key(Request $request, $id){
		$api_keys = new Apikeys();
		$auth = new Auth();

		if($auth->isValidUUID($id)){

			if($request->request->has('api_key_name') && $request->request->has('password')){
			$new_api_key = $api_keys->create_user_api_key($id, $request->request->get('api_key_name'), $request->request->get('password'));

				$response = new Response(json_encode($new_api_key));
				$response->headers->set('Content-Type', 'application/json');

				return $response;

			}else{

				$response = new Response(json_encode(array('success' => false, 'error_code' => 1082)));
				$response->headers->set('Content-Type', 'application/json');

				return $response;
			
			}
		}else{

			$response = new Response(json_encode(array('success' => false, 'error_code' => 1083)));
			$response->headers->set('Content-Type', 'application/json');

			return $response;
		
		}
	}

	/**
     * Matches /users/{id}/api_keys/{api_key}/delete/ exactly
     *
     * @Route("/users/{id}/api_keys/{api_key}/delete", name="delete_user_api_key")
     */

	public function delete_user_api_key(Request $request, $id, $api_key){
		$api_keys = new Apikeys();
		$auth = new Auth();

		if($auth->isValidUUID($id)){

			if($request->request->has('password')){
				$delete_api_key = $api_keys->delete_user_api_key($id, $api_key, $request->request->get('password'));

				$response = new Response(json_encode($delete_api_key));
				$response->headers->set('Content-Type', 'application/json');

				return $response;
			
			}else{
				
				$response = new Response(json_encode(array('success' => false, 'error_code' => 1082)));
				$response->headers->set('Content-Type', 'application/json');

				return $response;
			
			}

		}else{

			$response = new Response(json_encode(array('success' => false, 'error_code' => 1083)));
			$response->headers->set('Content-Type', 'application/json');

			return $response;
		
		}
	}

	/**
     * Matches /users/{id}/api_keys/{api_key}/rename/ exactly
     *
     * @Route("/users/{id}/api_keys/{api_key}/rename", name="rename_user_api_key")
     */	

	public function rename_user_api_key(Request $request, $id, $api_key){
		$api_keys = new Apikeys();
		$auth = new Auth();

		if($auth->isValidUUID($api_key)){

			if($request->request->has('password') && $request->request->has('api_key_name')){
				$rename_api_key = $api_keys->rename_user_api_key($id, $api_key, $request->request->get('api_key_name'), $request->request->get('password'));

				$response = new Response(json_encode($rename_api_key));
				$response->headers->set('Content-Type', 'application/json');

				return $response;

			}else{

				$response = new Response(json_encode(array('success' => false, 'error_code' => 1082)));
				$response->headers->set('Content-Type', 'application/json');

				return $response;
			
			}

		}else{

			$response = new Response(json_encode(array('success' => false, 'error_code' => 1083)));
			$response->headers->set('Content-Type', 'application/json');

			return $response;
		
		}
	}

	/**
     * Matches /users/{id}/api_keys/{api_key}/regen/ exactly
     *
     * @Route("/users/{id}/api_keys/{api_key}/regen", name="regen_user_api_key")
     */

	public function regen_user_api_key(Request $request, $id, $api_key){
		$api_keys = new Apikeys();
		$auth = new Auth();

		if($auth->isValidUUID($api_key)){

			if($request->request->has('password')){

				$regen_api_key = $api_keys->regenerate_user_api_key($id, $api_key, $request->request->get('password'));

				$response = new Response(json_encode($regen_api_key));
				$response->headers->set('Content-Type', 'application/json');

				return $response;

			}else{

				$response = new Response(json_encode(array('success' => false, 'error_code' => 1082)));
				$response->headers->set('Content-Type', 'application/json');

				return $response;
			
			}

		}else{

			$response = new Response(json_encode(array('success' => false, 'error_code' => 1083)));
			$response->headers->set('Content-Type', 'application/json');

			return $response;
		
		}
	}

}

?>