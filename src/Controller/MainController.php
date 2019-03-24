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

class MainController extends AbstractController {

	/**
     * Matches / exactly
     *
     * @Route("/", name="index_page")
     */

	public function index_page(){
		return new Response("You have reached the RLAPI v3.0 Index Page");
	}

	/**
     * Matches /upload exactly
     *
     * @Route("/upload", name="upload_index_page")
     */

	public function upload_index_page(){
		return new Response("Upload Index");
	}
}
?>