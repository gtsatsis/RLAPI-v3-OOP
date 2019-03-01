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
use App\Models\Buckets;

//use App\Utils\Sentry;

class BucketsController extends AbstractController {

	/**
     * Matches /buckets/{id}/create exactly
     *
     * @Route("/buckets/{id}/create", name="create_user_bucket")
     */

	public function create_user_bucket(Request $request, $id){
		$buckets = new Buckets();

		if($request->request->has('bucket_name') && $request->request->has('password')){
			$new_bucket = $buckets->create_new_user_bucket($id, $request->request->get('bucket_name'), $request->request->get('password'));

			return new Response(json_encode($new_bucket));
		}else{
			return new Response(json_encode(array('success' => false, 'errorcode' => 302882)));
		}
	}

	/**
     * Matches /buckets/{id}/delete exactly
     *
     * @Route("/buckets/{id}/delete", name="delete_user_bucket")
     */

	public function delete_user_bucket(Request $request, $id){
		$buckets = new Buckets();

		if($request->request->has('bucket_name') && $request->request->has('password')){
			$deleted_bucket = $buckets->delete_user_bucket($id, $request->request->get('bucket_name'), $request->request->get('password'));

			return new Response(json_encode($deleted_bucket));
		}else{
			return new Response(json_encode(array('success' => false, 'errorcode' => 302882)));
		}
	}

	/**
     * Matches /buckets/{id}/assign_domain exactly
     *
     * @Route("/buckets/{id}/assign_domain", name="assign_domain_to_bucket")
     */

	public function assign_domain_to_bucket(Request $request, $id){
		$buckets = new Buckets();

		if($request->request->has('bucket_name') && $request->request->has('password') && $request->request->has('domain')){
			$assign_domain = $buckets->assign_domain_to_bucket($id, $request->request->get('password'), $request->request->get('bucket_name'), $request->request->get('domain'));

			return new Response(json_encode($assign_domain));
		}else{
			return new Response(json_encode(array('success' => false, 'errorcode' => 302882)));
		}
	}

}

?>