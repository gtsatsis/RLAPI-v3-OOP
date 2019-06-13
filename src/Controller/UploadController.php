<?php

namespace App\Controller;

require_once __DIR__.'/../../vendor/autoload.php';

use App\Uploader\Uploader;
use App\Uploader\JsonUploader;
use App\Utils\Auth;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UploadController extends AbstractController
{
    /**
     * Matches /upload/pomf exactly.
     *
     * @Route("/upload/pomf", name="upload_file_pomf_QS")
     */
    public function upload_file_pomf_QS(Request $request)
    {
        $auth = new Auth();

        if ($request->query->has('key')) {
            if ($auth->isValidUUID($request->query->get('key'))) {
                if (array_key_exists('files', $_FILES)) {
                    if (!is_null($_FILES['files'])) {
                        if ($request->query->has('bucket')) {
                            /* Initiate the Uploader Object */
                            $uploader = new Uploader($request->query->get('bucket'));

                            /* Get the API key from the query, then proceed to the uploader */
                            $api_key = $request->query->get('key');
                            $uploadFile = $uploader->Upload($api_key, $_FILES['files']);

                            $response = new Response(json_encode($uploadFile));
                            $response->headers->set('Content-Type', 'application/json');

                            return $response;
                        } else {
                            $api_key = $request->query->get('key');
                            $uploader = new Uploader();

                            $uploadFile = $uploader->Upload($api_key, $_FILES['files']);

                            $response = new Response(json_encode($uploadFile));
                            $response->headers->set('Content-Type', 'application/json');

                            return $response;
                        }
                    } else {
                        $response = new Response(json_encode([
                            'success' => false,
                            'error_message' => 'no_file_provided',
                        ]));
                        $response->headers->set('Content-Type', 'application/json');

                        return $response;
                    }
                } else {
                    $response = new Response(json_encode([
                        'success' => false,
                        'error_message' => 'no_file_provided',
                    ]));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                }
            } else {
                $response = new Response(json_encode([
                    'success' => false,
                    'error_message' => 'key_not_uuid_format',
                ]));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        } elseif ($request->headers->has('Authorization')) {
            if ($auth->isValidUUID($request->headers->get('Authorization'))) {
                if (array_key_exists('files', $_FILES)) {
                    if (!is_null($_FILES['files'])) {
                        if ($request->query->has('bucket')) {
                            $uploader = new Uploader($request->query->get('bucket'));

                            $api_key = $request->headers->get('Authorization');
                            $uploadFile = $uploader->Upload($api_key, $_FILES['files']);

                            $response = new Response(json_encode($uploadFile));
                            $response->headers->set('Content-Type', 'application/json');

                            return $response;
                        } else {
                            $uploader = new Uploader();
                            $api_key = $request->headers->get('Authorization');
                            $uploadFile = $uploader->Upload($api_key, $_FILES['files']);

                            $response = new Response(json_encode($uploadFile));
                            $response->headers->set('Content-Type', 'application/json');

                            return $response;
                        }
                    } else {
                        $response = new Response(json_encode([
                            'success' => false,
                            'error_message' => 'no_file_provided',
                        ]));
                        $response->headers->set('Content-Type', 'application/json');

                        return $response;
                    }
                } else {
                    $response = new Response(json_encode([
                        'success' => false,
                        'error_message' => 'no_file_provided',
                    ]));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                }
            } else {
                $response = new Response(json_encode([
                        'success' => false,
                        'error_message' => 'key_not_uuid_format',
                    ]));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        } else {
            $response = new Response(json_encode([
                'success' => false,
                'error_message' => 'no_auth_method_provided',
            ]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }

    /**
     * Matches /upload/pomf/APIKEY.
     *
     * @Route("/upload/pomf/{apiKey}", name="upload_file_pomf_noQS")
     */
    public function upload_file_pomf_noQS(Request $request, $apiKey)
    {
        $auth = new Auth();

        if ($auth->isValidUUID($apiKey)) {
            if (array_key_exists('files', $_FILES)) {
                if (!is_null($_FILES['files'])) {
                    if ($request->query->has('bucket')) {
                        /* Initiate the Uploader Object */
                        $uploader = new Uploader($request->query->get('bucket'));

                        /* Get the API key from the query, then proceed to the uploader */
                        $api_key = apiKey;

                        $uploadFile = $uploader->Upload($api_key, $_FILES['files']);

                        $response = new Response(json_encode($uploadFile));
                        $response->headers->set('Content-Type', 'application/json');

                        return $response;
                    } else {
                        $api_key = $apiKey;
                        $uploader = new Uploader();

                        $uploadFile = $uploader->Upload($api_key, $_FILES['files']);

                        $response = new Response(json_encode($uploadFile));
                        $response->headers->set('Content-Type', 'application/json');

                        return $response;
                    }
                } else {
                    $response = new Response(json_encode([
                        'success' => false,
                        'error_message' => 'no_file_provided',
                    ]));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                }
            } else {
                $response = new Response(json_encode([
                    'success' => false,
                    'error_message' => 'no_file_provided',
                ]));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        } else {
            $response = new Response(json_encode([
                    'success' => false,
                    'error_message' => 'key_not_uuid_format',
                ]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
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

                        $response = new Response(json_encode($upload_json));
                        $response->headers->set('Content-Type', 'application/json');

                        return $response;
                    } else {
                        $response = new Response(json_encode([
                            'success' => false,
                            'error_message' => 'request_does_not_have_json_data',
                        ]));

                        $response->headers->set('Content-Type', 'application/json');

                        return $response;
                    }
                } else {
                    $response = new Response(json_encode([
                        'success' => false,
                        'error_message' => 'key_not_in_uuid_format',
                    ]));

                    $response->headers->set('Content-Type', 'application/json');

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

            return $response;
        }
    }

    /**
     * Matches /upload/json/jsonId/update.
     * 
     * @Route("/upload/json/{$jsonId}/update", name="update_json")
     */
    public function update_json(Request $request, $jsonId)
    {
        if (getenv('JSON_UPLOADER_ENABLED')) {
            $authentication = new Auth();

            if ($request->query->has('key')) {
                if ($authentication->isValidUUID($request->query->get('key'))) {
                    if ($request->request->has('data')) {
                        $jsonUploader = new JsonUploader();
                        $upload_json = $jsonUploader->update($request->query->get('key'), $json_id, $request->request->get('data'));

                        $response = new Response(json_encode($upload_json));
                        $response->headers->set('Content-Type', 'application/json');

                        return $response;
                    } else {
                        $response = new Response(json_encode([
                            'success' => false,
                            'error_message' => 'request_does_not_have_json_data',
                        ]));

                        $response->headers->set('Content-Type', 'application/json');

                        return $response;
                    }
                } else {
                    $response = new Response(json_encode([
                        'success' => false,
                        'error_message' => 'key_not_in_uuid_format',
                    ]));

                    $response->headers->set('Content-Type', 'application/json');

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

            return $response;
        }
    }
}
