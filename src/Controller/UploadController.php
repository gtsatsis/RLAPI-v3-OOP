<?php

namespace App\Controller;

require_once __DIR__.'/../../vendor/autoload.php';

use App\Uploader\Uploader;
use App\Uploader\JsonUploader;
use App\Utils\Auth;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UploadController extends AbstractController
{
    public function __construct()
    {
        /* Load the env file */
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__.'/../../.env');
    }

    /**
     * Matches /upload/pomf exactly.
     *
     * @Route("/upload/pomf", name="upload_file_pomf_QS")
     */
    public function upload_file_pomf_QS(Request $request)
    {
        $auth = new Auth();

        if ($request->query->has('key') && $request->query->has('bucket')) {
            if ($auth->isValidUUID($request->query->get('key'))) {
                if (array_key_exists('files', $_FILES)) {
                    if (!is_null($_FILES['files'])) {
                        if ($auth->upload_to_cb_allowed($request->query->get('key'), $request->query->get('bucket'))) {
                            /* Initiate the Uploader Object */
                            $uploader = new Uploader($request->query->get('bucket'));

                            /* Get the API key from the query, then proceed to the uploader */
                            $api_key = $request->query->get('key');
                            $uploadFile = $uploader->Upload($api_key, $_FILES['files']);

                            if (200 == $uploadFile['status_code']) {
                                $response = new Response(json_encode($uploadFile['response']));
                                $response->headers->set('Content-Type', 'application/json');
                            } else {
                                $response = new Response(json_encode($uploadFile['response']));
                                $response->headers->set('Content-Type', 'application/json');
                                $response->setStatusCode($uploadFile['status_code']);
                            }

                            return $response;
                        } else {
                            $response = new Response(json_encode([
                                'success' => false,
                                'error_message' => 'You are not authorized by the bucket administrator.',
                            ]));
                            $response->headers->set('Content-Type', 'application/json');
                            $response->setStatusCode(401);

                            return $response;
                        }
                    } else {
                        $response = new Response(json_encode([
                            'success' => false,
                            'error_message' => 'no_file_provided',
                        ]));
                        $response->headers->set('Content-Type', 'application/json');
                        $response->setStatusCode(400);

                        return $response;
                    }
                } else {
                    $response = new Response(json_encode([
                        'success' => false,
                        'error_message' => 'no_file_provided',
                    ]));
                    $response->headers->set('Content-Type', 'application/json');
                    $response->setStatusCode(400);

                    return $response;
                }
            } else {
                $response = new Response(json_encode([
                    'success' => false,
                    'error_message' => 'key_not_uuid_format',
                ]));
                $response->headers->set('Content-Type', 'application/json');
                $response->setStatusCode(401);

                return $response;
            }
        } elseif ($request->query->has('key')) {
            if ($auth->isValidUUID($request->query->get('key'))) {
                if (array_key_exists('files', $_FILES)) {
                    if (!is_null($_FILES['files'])) {
                        $api_key = $request->query->get('key');
                        $uploader = new Uploader(getenv('S3_BUCKET'));

                        $uploadFile = $uploader->Upload($api_key, $_FILES['files']);

                        if (200 == $uploadFile['status_code']) {
                            $response = new Response(json_encode($uploadFile['response']));
                            $response->headers->set('Content-Type', 'application/json');
                        } else {
                            $response = new Response(json_encode($uploadFile['response']));
                            $response->headers->set('Content-Type', 'application/json');
                            $response->setStatusCode($uploadFile['status_code']);
                        }

                        return $response;
                    } else {
                        $response = new Response(json_encode([
                            'success' => false,
                            'error_message' => 'no_file_provided',
                        ]));
                        $response->headers->set('Content-Type', 'application/json');
                        $response->setStatusCode(400);

                        return $response;
                    }
                } else {
                    $response = new Response(json_encode([
                        'success' => false,
                        'error_message' => 'no_file_provided',
                    ]));
                    $response->headers->set('Content-Type', 'application/json');
                    $response->setStatusCode(400);

                    return $response;
                }
            } else {
                $response = new Response(json_encode([
                    'success' => false,
                    'error_message' => 'key_not_uuid_format',
                ]));
                $response->headers->set('Content-Type', 'application/json');
                $response->setStatusCode(401);

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

                            if (200 == $uploadFile['status_code']) {
                                $response = new Response(json_encode($uploadFile['response']));
                                $response->headers->set('Content-Type', 'application/json');
                            } else {
                                $response = new Response(json_encode($uploadFile['response']));
                                $response->headers->set('Content-Type', 'application/json');
                                $response->setStatusCode($uploadFile['status_code']);
                            }

                            return $response;
                        } else {
                            $uploader = new Uploader(getenv('S3_BUCKET'));
                            $api_key = $request->headers->get('Authorization');
                            $uploadFile = $uploader->Upload($api_key, $_FILES['files']);

                            if (200 == $uploadFile['status_code']) {
                                $response = new Response(json_encode($uploadFile['response']));
                                $response->headers->set('Content-Type', 'application/json');
                            } else {
                                $response = new Response(json_encode($uploadFile['response']));
                                $response->headers->set('Content-Type', 'application/json');
                                $response->setStatusCode($uploadFile['status_code']);
                            }

                            return $response;
                        }
                    } else {
                        $response = new Response(json_encode([
                            'success' => false,
                            'error_message' => 'no_file_provided',
                        ]));
                        $response->headers->set('Content-Type', 'application/json');
                        $response->setStatusCode(400);

                        return $response;
                    }
                } else {
                    $response = new Response(json_encode([
                        'success' => false,
                        'error_message' => 'no_file_provided',
                    ]));
                    $response->headers->set('Content-Type', 'application/json');
                    $response->setStatusCode(400);

                    return $response;
                }
            } else {
                $response = new Response(json_encode([
                        'success' => false,
                        'error_message' => 'key_not_uuid_format',
                    ]));
                $response->headers->set('Content-Type', 'application/json');
                $response->setStatusCode(401);

                return $response;
            }
        } else {
            $response = new Response(json_encode([
                'success' => false,
                'error_message' => 'no_auth_method_provided',
            ]));
            $response->headers->set('Content-Type', 'application/json');
            $response->setStatusCode(401);

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

                        if (200 == $uploadFile['status_code']) {
                            $response = new Response(json_encode($uploadFile['response']));
                            $response->headers->set('Content-Type', 'application/json');
                        } else {
                            $response = new Response(json_encode($uploadFile['response']));
                            $response->headers->set('Content-Type', 'application/json');
                            $response->setStatusCode($uploadFile['status_code']);
                        }

                        return $response;
                    } else {
                        $api_key = $apiKey;
                        $uploader = new Uploader();

                        $uploadFile = $uploader->Upload($api_key, $_FILES['files']);

                        if (200 == $uploadFile['status_code']) {
                            $response = new Response(json_encode($uploadFile['response']));
                            $response->headers->set('Content-Type', 'application/json');
                        } else {
                            $response = new Response(json_encode($uploadFile['response']));
                            $response->headers->set('Content-Type', 'application/json');
                            $response->setStatusCode($uploadFile['status_code']);
                        }

                        return $response;
                    }
                } else {
                    $response = new Response(json_encode([
                        'success' => false,
                        'error_message' => 'no_file_provided',
                    ]));
                    $response->headers->set('Content-Type', 'application/json');
                    $response->setStatusCode(400);

                    return $response;
                }
            } else {
                $response = new Response(json_encode([
                    'success' => false,
                    'error_message' => 'no_file_provided',
                ]));
                $response->headers->set('Content-Type', 'application/json');
                $response->setStatusCode(400);

                return $response;
            }
        } else {
            $response = new Response(json_encode([
                    'success' => false,
                    'error_message' => 'key_not_uuid_format',
                ]));
            $response->headers->set('Content-Type', 'application/json');
            $response->setStatusCode(401);

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
