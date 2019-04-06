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

class AdminController extends AbstractController {

	/**
     * Matches /admin/migration/password_reset_all exactly
     *
     * @Route("/admin/migration/password_reset_all", name="password_reset_all")
     */

	public function password_reset_all(Request $request){
		$auth = new Auth();

		if($request->request->has('api_key') && $request->request->has('password')){
		
			if($auth->isValidUUID($request->request->get('api_key'))){

				$password_reset_all = $auth->password_reset_all_migration($request->request->get('api_key'), $request->request->get('password'));

				$response = new Response(json_encode($password_reset_all));
				$response->headers->set('Content-Type', 'application/json');

				return $response;

			}else{

				$response = new Response(json_encode(array('success' => false, 'error_code' => 1083)));
				$response->headers->set('Content-Type', 'application/json');

				return $response;
		
			}

		}else{

			$response = new Response(json_encode(array('success' => false, 'error_code' => 1082)));
			$response->headers->set('Content-Type', 'application/json');

			return $response;
			
		}
	}

	/**
     * Matches /admin/migration/verify_all_emails exactly
     *
     * @Route("/admin/migration/verify_all_emails", name="verify_all_emails")
     */

	public function verify_all_emails(Request $request){
		$auth = new Auth();

		if($request->request->has('api_key') && $request->request->has('password')){

			if($auth->isValidUUID($request->request->get('api_key'))){

				$verify_all_emails = $auth->verify_all_emails_migration($request->request->get('api_key'), $request->request->get('password'));

				$response = new Response(json_encode($verify_all_emails));
				$response->headers->set('Content-Type', 'application/json');

				return $response;
			
			}else{

				$response = new Response(json_encode(array('success' => false, 'error_code' => 1083)));
				$response->headers->set('Content-Type', 'application/json');

				return $response;
		
			}

		}else{
			
			$response = new Response(json_encode(array('success' => false, 'error_code' => 1082)));
			$response->headers->set('Content-Type', 'application/json');

			return $response;
			
		}
	}

}

?>