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

//use App\Utils\Sentry;

class ApiKeysController extends AbstractController {

	/**
     * Matches /users/{id}/api_keys/create exactly
     *
     * @Route("/users/{id}/api_keys/create", name="create_user_api_key")
     */

	public function create_user_api_key(Request $request, $id){
		$api_keys = new Apikeys();

		if($request->request->has('api_key_name') && $request->request->has('password')){
			$new_api_key = $api_keys->createUserApiKey($id, $request->request->get('api_key_name'), $request->request->get('password'));

			return new Response(json_encode($new_api_key));
		}else{
			return new Response(json_encode(array('success' => false, 'errorcode' => 302882)));
		}
	}

	/**
     * Matches /users/{id}/api_keys/{api_key}/delete/ exactly
     *
     * @Route("/users/{id}/api_keys/{api_key}/delete/", name="delete_user_api_key")
     */

	public function delete_user_api_key(Request $request, $id, $api_key){
		$api_keys = new Apikeys();

		if($request->request->has('password')){
			$delete_api_key = $api_keys->deleteUserAPIKey($id, $api_key, $request->request->get('password'));

			return new Response($delete_api_key);
		}else{
			return new Response(json_encode(array('success' => false, 'errorcode' => 302882)));
		}
	}

	/**
     * Matches /users/{id}/api_keys/{api_key}/rename/ exactly
     *
     * @Route("/users/{id}/api_keys/{api_key}/rename/", name="rename_user_api_key")
     */	

	public function rename_user_api_key(Request $request, $id, $api_key){
		$api_keys = new Apikeys();

		if($request->request->has('password') && $request->request->has('api_key_name')){
			$api_keys->renameApiKey($id, $api_key, $request->request->get('api_key_name'), $request->request->get('password'));
		}else{
			return new Response(json_encode(array('success' => false, 'errorcode' => 302882)));
		}
	}

	/**
     * Matches /users/{id}/api_keys/{api_key}/regen/ exactly
     *
     * @Route("/users/{id}/api_keys/{api_key}/regen/", name="regen_user_api_key")
     */

	public function regen_user_api_key(Request $request, $id, $api_key){
		$api_keys = new Apikeys();

		if($request->request->has('password')){

			$api_keys->regenerateApiKey($id, $api_key, $request->request->get('password'));

		}else{
			return new Response(json_encode(array('success' => false, 'errorcode' => 302882)));
		}
	}

}

?>