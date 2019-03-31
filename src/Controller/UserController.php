<?php
namespace App\Controller;

require_once __DIR__ . '/../../vendor/autoload.php';

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use App\Models\User;
use App\Models\Apikeys;
use App\Utils\Auth;

class UserController extends AbstractController {

	/**
     * Matches /users/create exactly
     *
     * @Route("/users/create", name="create_user")
     */

	public function create_user(Request $request){
		$users = new User();

		if($request->request->has('username') && $request->request->has('password') && $request->request->has('email')){
			$createUser = $users->create_user($request->request->get('username'), $request->request->get('password'), $request->request->get('email'));

			return new Response(json_encode($createUser));
		}else{
			return new Response(json_encode(array('success' => false, 'errorcode' => 302882)));
		}
	}

	/**
     * Matches /users/{id}/delete exactly
     *
     * @Route("/users/{id}/delete", name="delete_user")
     */

	public function delete_user(Request $request, $id){

		$auth = new Auth();
		if($auth->isValidUUID($id)){

			if($request->request->has('email') && $request->request->has('password')){
				$users = new User();

				$deleteUser = $users->delete_user($id, $request->request->get('email'), $request->request->get('password'));

				return new Response(json_encode($deleteUser));
			}else{
				return new Response(json_encode(array('success' => false, 'errorcode' => 302882)));
			}

		}else{
			return new Response(json_encode(array('success' => false, 'errorcode' => 302883)));
		}
	}

	/**
     * Matches /users/{id}/set_tier exactly
     *
     * @Route("/users/{id}/set_tier", name="set_user_tier")
     */	

	public function set_user_tier(Request $request, $id){
		$users = new User();
		$auth = new Auth();

		if($auth->isValidUUID($id)){

			if($request->request->has('tier') && $request->request->has('api_key')){
				$setTier = $users->user_set_tier($id, $request->request->get('tier'), $request->request->get('api_key'));

				return new Response(json_encode($setTier));
			}else{
				return new Response(json_encode(array('success' => false, 'errorcode' => 302882)));
			}

		}else{

			return new Response(json_encode(array('success' => false, 'errorcode' => 302883)));
		
		}
	}
	
	/**
     * Matches /users/{id}/email exactly
     *
     * @Route("/users/{id}/update_email", name="set_user_email")
     */	

	public function set_user_email(Request $request, $id){
		$users = new User();
		$auth = new Auth();

		if($auth->isValidUUID($id)){

			if($request->request->has('password') && $request->request->has('newEmail')){

				$users->user_set_email($id, $request->request->get('newEmail'), $request->request->get('password'));

			}else{
				return new Response(json_encode(array('success' => false, 'errorcode' => 302882)));
			}
		}else{

			return new Response(json_encode(array('success' => false, 'errorcode' => 302883)));
		
		}

	}

	/**
     * Matches /users/{id}/password exactly
     *
     * @Route("/users/{id}/update_password", name="set_user_password")
     */	

	public function update_user_password(Request $request, $id){
		$users = new User();
		$auth = new Auth();

		if($auth->isValidUUID($id)){

			if($request->request->has('password') && $request->request->has('newPassword')){
				$updatePassword = $users->user_set_password($id, $request->request->get('password'), $request->request->get('newPassword'));

				return new Response(json_encode($updatePassword));
			}else{
			
				return new Response(json_encode(array('success' => false, 'errorcode' => 302882)));
			
			}

		}else{
			
			return new Response(json_encode(array('success' => false, 'errorcode' => 302883)));
		
		}
	}

	/**
     * Matches /users/{id}/verify_email/{verification_id} exactly
     *
     * @Route("/users/{id}/verify_email/{verification_id}", name="set_user_password")
     */	

	public function verify_user_email($id, $verification_id){
		$auth = new Auth();

		if($auth->isValidUUID($id)){
			if($auth->isValidUUID($verification_id)){

				$users = new User();

				$verify_email = $users->user_verify_email($id, $verification_id);
			
				return new Response(json_encode($verify_email));

			}else{

				return new Response(json_encode(array('success' => false, 'errorcode' => 302882)));
			
			}
		}else{

			return new Response(json_encode(array('success' => false, 'errorcode' => 302883)));
		
		}
	}	

}

?>