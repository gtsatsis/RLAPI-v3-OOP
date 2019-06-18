<?php

namespace App\Controller;

require_once __DIR__.'/../../vendor/autoload.php';

use App\Models\Buckets;
use App\Utils\Auth;
use App\Utils\Getters;
use App\Utils\FileUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BucketsController extends AbstractController
{
    private $getter;

    private $buckets;

    private $authentication;

    public function __construct()
    {
        $this->getter = new Getters();
        $this->buckets = new Buckets();
        $this->authentication = new Auth();
    }

    /**
     * Matches /buckets/create exactly.
     *
     * @Route("/buckets/create", name="create_bucket")
     */
    public function create_bucket(Request $request)
    {
        if ($request->request->has('api_key') && $request->request->has('bucket_name')) {
            if ($this->authentication->isValidUUID($request->request->get('api_key'))) {
                $create_bucket = $this->buckets->create($request->request->get('api_key'), $request->request->get('bucket_name'));
                $response = new Response(json_encode($create_bucket));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            } else {
                $response = new Response(json_encode(['success' => false, 'error' => ['error_message' => 'API key not in UUID format']]));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        } else {
            $response = new Response(json_encode(['success' => false, 'error' => ['error_message' => 'Missing required parameters', 'required' => ['api_key', 'bucket_name']]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }

    /**
     * Matches /buckets/{bucket_id}/delete exactly.
     *
     * @Route("/buckets/{bucket_id}/delete", name="delete_bucket")
     */
    public function delete_bucket(Request $request, $bucket_id)
    {
        if ($request->request->has('api_key')) {
            if ($this->authentication->isValidUUID($request->request->get('api_key'))) {
                $delete_bucket = $this->buckets->delete($request->request->get('api_key'), $bucket_id);
                $response = new Response(json_encode($delete_bucket));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            } else {
                $response = new Response(json_encode(['success' => false, 'error' => ['error_message' => 'API key not in UUID format']]));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        } else {
            $response = new Response(json_encode(['success' => false, 'error' => ['error_message' => 'Missing required parameters', 'required' => ['api_key']]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }

    /**
     * Matches /buckets/{bucket_id}/users exactly.
     *
     * @Route("/buckets/{bucket_id}/users", name="get_users")
     */
    public function get_users(Request $request, $bucket_id)
    {
        if ($request->isMethod('get')) {
            if ($request->query->has('key')) {
                if ($this->authentication->isValidUUID($request->query->get('key'))) {
                    if ($this->buckets->bucket_exists($this->getter->getBucketNameFromID($bucket_id)) && $this->buckets->user_is_in_bucket($request->request->get('api_key'), $bucket_id)) {
                        $permissions = $this->buckets->get_permissions($request->query->get('key'), $bucket_id);
                        if (true == $permissions['rlapi.custom.bucket.users.get']) {
                            $get_users = $this->bucket->get_users($bucket_id);
                            $response = new Response(json_encode($get_users));
                            $response->headers->set('Content-Type', 'application/json');

                            return $response;
                        } else {
                            $response = new Response(json_encode(['success' => false, 'error' => ['error_message' => 'Unauthorized']]));
                            $response->headers->set('Content-Type', 'application/json');

                            return $response;
                        }
                    } else {
                        $response = new Response(json_encode(['success' => false, 'error' => ['error_message' => 'Unauthorized']]));
                        $response->headers->set('Content-Type', 'application/json');

                        return $response;
                    }
                } else {
                    $response = new Response(json_encode(['success' => false, 'error' => ['error_message' => 'Key not in UUID format']]));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                }
            } elseif ($request->headers->has('Authorization')) {
                if ($this->authentication->isValidUUID($request->headers->get('Authorization'))) {
                    if ($this->buckets->bucket_exists($this->getter->getBucketNameFromID($bucket_id)) && $this->buckets->user_is_in_bucket($request->headers->get('Authorization'), $bucket_id)) {
                        $permissions = $this->buckets->get_permissions($request->headers->get('Authorization'), $bucket_id);
                        if (true == $permissions['rlapi.custom.bucket.users.get']) {
                            $get_users = $this->buckets->get_users($bucket_id);
                            $response = new Response(json_encode($get_users));
                            $response->headers->set('Content-Type', 'application/json');

                            return $response;
                        } else {
                            $response = new Response(json_encode(['success' => false, 'error' => ['error_message' => 'Unauthorized']]));
                            $response->headers->set('Content-Type', 'application/json');

                            return $response;
                        }
                    } else {
                        $response = new Response(json_encode(['success' => false, 'error' => ['error_message' => 'Unauthorized']]));
                        $response->headers->set('Content-Type', 'application/json');

                        return $response;
                    }
                } else {
                    $response = new Response(json_encode(['success' => false, 'error' => ['error_message' => 'Key not in UUID format']]));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                }
            } else {
                $response = new Response(json_encode(['success' => false, 'error' => ['error_message' => 'No authentication method provided']]));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        } else {
            $response = new Response(json_encode(['success' => false, 'error' => ['error_message' => 'Request must be of type GET']]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }

    /**
     * Matches /buckets/{bucket_id}/users/add.
     *
     * @Route("/buckets/{bucket_id}/users/add", name="add_user")
     */
    public function add_user(Request $request, $bucket_id)
    {
        if ($request->request->has('api_key')) {
            if ($this->authentication->isValidUUID($bucket_id) && $this->authentication->isValidUUID($request->request->get('api_key'))) {
                if ($this->buckets->bucket_exists($this->getter->getBucketNameFromID($bucket_id)) && $this->buckets->user_is_in_bucket($request->request->get('api_key'), $bucket_id)) {
                    $permissions = $this->buckets->get_permissions($request->request->get('api_key'), $bucket_id);
                    if (true == $permissions['rlapi.custom.bucket.user.add']) {
                        $add_user = $this->buckets->add_user($request->request->get('username'), $bucket_id);
                        $response = new Response(json_encode($add_user));
                        $response->headers->set('Content-Type', 'application/json');

                        return $response;
                    } else {
                        $response = new Response(json_encode(['success' => false, 'error' => ['error_message' => 'Unauthorized']]));
                        $response->headers->set('Content-Type', 'application/json');

                        return $response;
                    }
                } else {
                    $response = new Response(json_encode(['success' => false, 'error' => ['error_message' => 'Unauthorized']]));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                }
            } else {
                $response = new Response(json_encode(['success' => false, 'error' => ['error_message' => 'Bucket ID and API key not in UUID format.']]));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        } else {
            $response = new Response(json_encode(['success' => false, 'error' => ['error_message' => 'Missing required parameters', 'required' => ['api_key']]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }

    /**
     * Matches /buckets/{bucket_id}/users/{user_name}/remove.
     *
     * @Route("/buckets/{bucket_id}/users/{user_name}/remove", name="remove_user")
     */
    public function remove_user(Request $request, $bucket_id, $user_name)
    {
        if ($request->request->has('api_key')) {
            if ($this->authentication->isValidUUID($bucket_id) && $this->authentication->isValidUUID($request->request->get('api_key'))) {
                if ($this->buckets->bucket_exists($this->getter->getBucketNameFromID($bucket_id)) && $this->buckets->user_is_in_bucket($request->request->get('api_key'), $bucket_id)) {
                    $permissions = $this->buckets->get_permissions($request->request->get('api_key'), $bucket_id);
                    if (true == $permissions['rlapi.custom.bucket.user.remove'] && $this->buckets->actor_permission_higher_than_user($permissions['rlapi.custom.bucket.permission.priority'], $username, $bucket_id)) {
                        $remove_user = $this->buckets->remove_user($request->request->get('username'), $bucket_id);
                        $response = new Response(json_encode($remove_user));
                        $response->headers->set('Content-Type', 'application/json');

                        return $response;
                    } else {
                        $response = new Response(json_encode(['success' => false, 'error' => ['error_message' => 'Unauthorized']]));
                        $response->headers->set('Content-Type', 'application/json');

                        return $response;
                    }
                } else {
                    $response = new Response(json_encode(['success' => false, 'error' => ['error_message' => 'Unauthorized']]));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                }
            } else {
                $response = new Response(json_encode(['success' => false, 'error' => ['error_message' => 'Bucket ID and API key not in UUID format.']]));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        } else {
            $response = new Response(json_encode(['success' => false, 'error' => ['error_message' => 'Missing required parameters', 'required' => ['api_key']]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }

    /**
     * Matches /buckets/{bucket_id}/uploads/{file_name}/delete.
     *
     * @Route("/buckets/{bucket_id}/uploads/{file_name}/delete", name="delete_file")
     */
    public function delete_file(Request $request, $bucket_id, $file_name)
    {
        $file_utils = new FileUtils();
        if ($request->request->has('api_key')) {
            if ($this->authentication->isValidUUID($bucket_id) && $this->authentication->isValidUUID($request->request->get('api_key'))) {
                $bucket_name = $this->getter->getBucketNameFromID($bucket_id);
                if ($file_utils->get_file_owner($file_name, $this->getter->get_user_id_by_api_key($request->request->get('api_key')), $request->request->get('api_key'), $bucket_name)) {
                    $delete_file = $file_utils->delete_file($file_name, $bucket_name);
                    $response = new Response(json_encode($delete_file));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                } else {
                    $response = new Response(json_encode(['success' => false, 'error' => ['error_message' => 'Unauthorized']]));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                }
            } else {
                $response = new Response(json_encode(['success' => false, 'error' => ['error_message' => 'API key and Bucket ID not in UUID format']]));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        } else {
            $response = new Response(json_encode(['success' => false, 'error' => ['error_message' => 'Missing required parameters', 'required' => ['api_key']]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }
}
