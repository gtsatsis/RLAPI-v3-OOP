<?php

namespace App\Controller;

require_once __DIR__.'/../../vendor/autoload.php';

use App\Uploader\Uploader;
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
                            if ($request->query->has('encrypt')) {
                                if ('true' == $request->query->get('encrypt')) {
                                    $encrypt = true;
                                } else {
                                    $encrypt = false;
                                }
                            } else {
                                $encrypt = false;
                            }
                            /* Initiate the Uploader Object */
                            $uploader = new Uploader($request->query->get('bucket'), $encrypt);

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
                        if ($request->query->has('encrypt')) {
                            if ('true' == $request->query->get('encrypt')) {
                                $encrypt = true;
                            } else {
                                $encrypt = false;
                            }
                        } else {
                            $encrypt = false;
                        }
                        $api_key = $request->query->get('key');
                        $uploader = new Uploader(getenv('S3_BUCKET'), $encrypt);

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
                            if ($request->query->has('encrypt')) {
                                if ('true' == $request->query->get('encrypt')) {
                                    $encrypt = true;
                                } else {
                                    $encrypt = false;
                                }
                            } else {
                                $encrypt = false;
                            }

                            $uploader = new Uploader($request->query->get('bucket'), $encrypt);

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
                            if ($request->query->has('encrypt')) {
                                if ('true' == $request->query->get('encrypt')) {
                                    $encrypt = true;
                                } else {
                                    $encrypt = false;
                                }
                            } else {
                                $encrypt = false;
                            }

                            $uploader = new Uploader(getenv('S3_BUCKET'), $encrypt);
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
                        if ($request->query->has('encrypt')) {
                            if ('true' == $request->query->get('encrypt')) {
                                $encrypt = true;
                            } else {
                                $encrypt = false;
                            }
                        } else {
                            $encrypt = false;
                        }

                        /* Initiate the Uploader Object */
                        $uploader = new Uploader($request->query->get('bucket'), $encrypt);

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
                        if ($request->query->has('encrypt')) {
                            if ('true' == $request->query->get('encrypt')) {
                                $encrypt = true;
                            } else {
                                $encrypt = false;
                            }
                        } else {
                            $encrypt = false;
                        }

                        $api_key = $apiKey;
                        $uploader = new Uploader(getenv('S3_BUCKET'), $encrypt);

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
}
