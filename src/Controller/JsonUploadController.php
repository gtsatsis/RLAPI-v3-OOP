<?php

namespace App\Controller;

require_once __DIR__.'/../../vendor/autoload.php';

use App\Uploader\JsonUploader;
use App\Utils\Auth;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class JsonUploadController extends AbstractController
{
    public function __construct()
    {
        /* Load the env file */
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__.'/../../.env');
    }

    /**
     * Matches /upload/json.
     *
     * @Route("/upload/json", name="upload_json")
     */
    public function upload_json(Request $request)
    {
        if (getenv('JSON_UPLOADER_ENABLED')) {
            $authentication = new Auth();

            if ($request->query->has('key')) {
                if ($authentication->isValidUUID($request->query->get('key'))) {
                    if ($request->request->has('data')) {
                        $jsonUploader = new JsonUploader();
                        $upload_json = $jsonUploader->upload($request->query->get('key'), $request->request->get('data'));

                        if (200 == $upload_json['status_code']) {
                            $response = new Response(json_encode($upload_json['response']));
                            $response->headers->set('Content-Type', 'application/json');
                        } else {
                            $response = new Response(json_encode($upload_json['response']));
                            $response->headers->set('Content-Type', 'application/json');
                            $response->setStatusCode($upload_json['status_code']);
                        }

                        return $response;
                    } else {
                        $response = new Response(json_encode([
                            'success' => false,
                            'error_message' => 'request_does_not_have_json_data',
                        ]));

                        $response->headers->set('Content-Type', 'application/json');
                        $response->setStatusCode(400);

                        return $response;
                    }
                } else {
                    $response = new Response(json_encode([
                        'success' => false,
                        'error_message' => 'key_not_in_uuid_format',
                    ]));

                    $response->headers->set('Content-Type', 'application/json');
                    $response->setStatusCode(401);

                    return $response;
                }
            } elseif ($request->headers->has('Authorization')) {
            }
        } else {
            $response = new Response(json_encode([
                'success' => false,
                'error_message' => 'This instance does not support the JSON Uploader feature.',
            ]));

            $response->headers->set('Content-Type', 'application/json');
            $response->setStatusCode(501);

            return $response;
        }
    }

    /**
     * Matches /upload/json/json_id/update.
     *
     * @Route("/upload/json/{json_id}/update", name="update_json_noSlash")
     * @Route("/upload/json/{json_id}/update/", name="update_json_withSlash")
     */
    public function update_json(Request $request, $json_id)
    {
        if (getenv('JSON_UPLOADER_ENABLED')) {
            $authentication = new Auth();

            if ($request->query->has('key')) {
                if ($authentication->isValidUUID($request->query->get('key'))) {
                    if ($authentication->isValidUUID($json_id)) {
                        if ($request->request->has('data')) {
                            $jsonUploader = new JsonUploader();
                            $update_json = $jsonUploader->update($request->query->get('key'), $json_id, $request->request->get('data'));

                            if (200 == $update_json['status_code']) {
                                $response = new Response(json_encode($update_json['response']));
                                $response->headers->set('Content-Type', 'application/json');
                            } else {
                                $response = new Response(json_encode($update_json['response']));
                                $response->headers->set('Content-Type', 'application/json');
                                $response->setStatusCode($update_json['status_code']);
                            }

                            return $response;
                        } else {
                            $response = new Response(json_encode([
                                'success' => false,
                                'error_message' => 'request_does_not_have_json_data',
                            ]));

                            $response->headers->set('Content-Type', 'application/json');
                            $response->setStatusCode(400);

                            return $response;
                        }
                    } else {
                        $response = new Response(json_encode([
                            'success' => false,
                            'error_message' => 'invalid_json_id',
                        ]));

                        $response->headers->set('Content-Type', 'application/json');
                        $response->setStatusCode(400);

                        return $response;
                    }
                } else {
                    $response = new Response(json_encode([
                        'success' => false,
                        'error_message' => 'key_not_in_uuid_format',
                    ]));

                    $response->headers->set('Content-Type', 'application/json');
                    $response->setStatusCode(401);

                    return $response;
                }
            } elseif ($request->headers->has('Authorization')) {
            } else {
                $response = new Response(json_encode([
                    'success' => false,
                    'error_message' => 'no_auth_method_provided',
                ]));

                $response->headers->set('Content-Type', 'application/json');
                $response->setStatusCode(401);

                return $response;
            }
        } else {
            $response = new Response(json_encode([
                'success' => false,
                'error_message' => 'This instance does not support the JSON Uploader feature.',
            ]));

            $response->headers->set('Content-Type', 'application/json');
            $response->setStatusCode(501);

            return $response;
        }
    }

    /**
     * Matches /upload/json/json_id/delete.
     *
     * @Route("/upload/json/{json_id}/delete", name="delete_json_noSlash")
     * @Route("/upload/json/{json_id}/delete/", name="delete_json_withSlash")
     */
    public function delete_json(Request $request, $json_id)
    {
        if (getenv('JSON_UPLOADER_ENABLED')) {
            $authentication = new Auth();

            if ($request->query->has('key')) {
                if ($authentication->isValidUUID($request->query->get('key'))) {
                    if ($authentication->isValidUUID($json_id)) {
                        $jsonUploader = new JsonUploader();
                        $delete_json = $jsonUploader->delete($request->query->get('key'), $json_id);

                        if (200 == $delete_json['status_code']) {
                            $response = new Response(json_encode($delete_json['response']));
                            $response->headers->set('Content-Type', 'application/json');
                        } else {
                            $response = new Response(json_encode($delete_json['response']));
                            $response->headers->set('Content-Type', 'application/json');
                            $response->setStatusCode($delete_json['status_code']);
                        }

                        return $response;
                    } else {
                        $response = new Response(json_encode([
                            'success' => false,
                            'error_message' => 'invalid_json_id',
                        ]));

                        $response->headers->set('Content-Type', 'application/json');
                        $response->setStatusCode(400);

                        return $response;
                    }
                } else {
                    $response = new Response(json_encode([
                        'success' => false,
                        'error_message' => 'key_not_in_uuid_format',
                    ]));

                    $response->headers->set('Content-Type', 'application/json');
                    $response->setStatusCode(401);

                    return $response;
                }
            } elseif ($request->headers->has('Authorization')) {
            } else {
                $response = new Response(json_encode([
                    'success' => false,
                    'error_message' => 'no_auth_method_provided',
                ]));

                $response->headers->set('Content-Type', 'application/json');
                $response->setStatusCode(401);

                return $response;
            }
        } else {
            $response = new Response(json_encode([
                'success' => false,
                'error_message' => 'This instance does not support the JSON Uploader feature.',
            ]));

            $response->headers->set('Content-Type', 'application/json');
            $response->setStatusCode(501);

            return $response;
        }
    }
}
