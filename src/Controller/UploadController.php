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
use App\Uploader\Uploader;

use Aws\S3\S3Client;

class UploadController extends AbstractController {

	/**
     * Matches /upload exactly
     *
     * @Route("/upload/pomf", name="upload_file_pomf_QS")
     */

	public function upload_file_pomf_QS(Request $request){
		
		if($request->query->has('key')){

			if($request->query->has('bucket')){

				/* Initiate the Uploader Object */
				$uploader = new Uploader($request->query->get('bucket'));

				/* Get the API key from the query, then proceed to the uploader */
				$api_key = $request->query->get('key');
				$uploadFile = $uploader->Upload($api_key, $_FILES['files']);

				return new Response(json_encode($uploadFile));

			}else{

				$api_key = $request->query->get('key');
				$uploader = new Uploader();

				$uploadFile = $uploader->Upload($api_key, $_FILES['files']);

				return new Response(json_encode($uploadFile));

			}

		}elseif($request->headers->has('Authorization')){

			if($request->query->has('bucket')){

				$uploader = new Uploader($request->query->get('bucket'));

				$api_key = $request->headers->get('Authorization');
				$uploadFile = $uploader->Upload($api_key, $_FILES['files']);

				return new Response(json_encode($uploadFile));

			}else{
				return new Response(json_encode([
					'success' => false,
					'error_message' => 'no_auth_method_provided'
				]));
			}

		}else{

			return new Response('To use the API, you must either add an API key via a query parameter, or via trailing slash.');
		
		}
	}

	/**
     * Matches /upload/pomf/APIKEY
     *
     * @Route("/upload/pomf/{apiKey}", name="upload_file_pomf_noQS")
     */

	public function upload_file_pomf_noQS(Request $request, $apiKey){

			if($request->query->has('bucket')){

				/* Initiate the Uploader Object */
				$uploader = new Uploader($request->query->get('bucket'));

				/* Get the API key from the query, then proceed to the uploader */
				$api_key = apiKey;
				$uploadFile = $uploader->Upload($api_key, $_FILES['files']);

				return new Response(json_encode($uploadFile));

			}else{

				$api_key = $apiKey;
				$uploader = new Uploader();

				$uploadFile = $uploader->Upload($api_key, $_FILES['files']);

				return new Response(json_encode($uploadFile));

			}
		
	}
}
?>