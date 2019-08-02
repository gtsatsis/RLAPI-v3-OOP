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
     * @Route("/buckets", name="create_bucket", methods={"POST","PUT"})
     * @Route("/buckets/create", name="create_bucket", methods={"POST","PUT"})
     */
    public function create_bucket(Request $request)
    {
        $data = json_decode($request->getContents(), true);

        if ($request->headers->has('Authorization') && array_key_exists('bucket_name', $data)) {
            if ($this->authentication->isValidUUID($request->headers->get('Authorization'))) {
                $create_bucket = $this->buckets->create($request->headers->get('Authorization'), $data['bucket_name']);
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
     * Matches /buckets exactly.
     *
     * @Route("/buckets", name="delete_bucket", methods={"DELETE"})
     */
    public function delete_bucket(Request $request)
    {
        $data = json_decode($request->getContents(), true);

        if ($request->headers->has('Authorization')) {
            if ($this->authentication->isValidUUID($request->headers->get('Authorization'))) {
                $delete_bucket = $this->buckets->delete($request->headers->get('Authorization'), $data['bucket_id']);
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
     * @Route("/buckets/{bucket_id}/users", name="get_users", methods={"GET"})
     */
    public function get_users(Request $request, $bucket_id)
    {
        if ($request->headers->has('key')) {
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
    }

    /**
     * Matches /buckets/{bucket_id}/users/add.
     *
     * @Route("/buckets/{bucket_id}/users", name="add_user", methods={"PUT", "POST"})
     * @Route("/buckets/{bucket_id}/users/add", name="add_user", methods={"PUT", "POST"})
     */
    public function add_user(Request $request, $bucket_id)
    {
        $data = json_decode($request->getContents(), true);

        if ($request->headers->has('Authorization')) {
            if ($this->authentication->isValidUUID($bucket_id) && $this->authentication->isValidUUID($request->headers->get('Authorization'))) {
                if ($this->buckets->bucket_exists($this->getter->getBucketNameFromID($bucket_id)) && $this->buckets->user_is_in_bucket($request->headers->get('Authorization'), $bucket_id)) {
                    $permissions = $this->buckets->get_permissions($request->headers->get('Authorization'), $bucket_id);
                    if (true == $permissions['rlapi.custom.bucket.user.add']) {
                        $add_user = $this->buckets->add_user($data['username'], $bucket_id);
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
     * @Route("/buckets/{bucket_id}/users/{user_name}", name="remove_user", methods={"DELETE"})
     * @Route("/buckets/{bucket_id}/users/{user_name}/remove", name="remove_user", methods={"DELETE"})
     */
    public function remove_user(Request $request, $bucket_id, $user_name)
    {
        if ($request->headers->has('Authorization')) {
            if ($this->authentication->isValidUUID($bucket_id) && $this->authentication->isValidUUID($request->headers->get('Authorization'))) {
                if ($this->buckets->bucket_exists($this->getter->getBucketNameFromID($bucket_id)) && $this->buckets->user_is_in_bucket($request->headers->get('Authorization'), $bucket_id)) {
                    $permissions = $this->buckets->get_permissions($request->headers->get('Authorization'), $bucket_id);
                    if (true == $permissions['rlapi.custom.bucket.user.remove'] && $this->buckets->actor_permission_higher_than_user($permissions['rlapi.custom.bucket.permission.priority'], $user_name, $bucket_id)) {
                        $remove_user = $this->buckets->remove_user($user_name, $bucket_id);
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
     * Matches /buckets/{bucket_id}/users/{user_name}/block.
     *
     * @Route("/buckets/{bucket_id}/users/{user_name}/block", name="block_user", methods={"POST", "PATCH"})
     */
    public function block_user(Request $request, $bucket_id, $user_name)
    {
        if ($request->headers->has('Authorization')) {
            if ($this->authentication->isValidUUID($bucket_id) && $this->authentication->isValidUUID($request->headers->get('Authorization'))) {
                if ($this->buckets->bucket_exists($this->getter->getBucketNameFromID($bucket_id)) && $this->buckets->user_is_in_bucket($request->headers->get('Authorization'), $bucket_id)) {
                    $permissions = $this->buckets->get_permissions($request->headers->get('Authorization'), $bucket_id);
                    if (true == $permissions['rlapi.custom.bucket.user.block'] && $this->buckets->actor_permission_higher_than_user($permissions['rlapi.custom.bucket.permission.priority'], $user_name, $bucket_id)) {
                        $block_user = $this->buckets->block_user($user_name, $bucket_id);
                        $response = new Response(json_encode($block_user));
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
     * Matches /buckets/{bucket_id}/users/{user_name}/unblock.
     *
     * @Route("/buckets/{bucket_id}/users/{user_name}/unblock", name="unblock_user")
     */
    public function unblock_user(Request $request, $bucket_id, $user_name)
    {
        if ($request->request->has('api_key')) {
            if ($this->authentication->isValidUUID($bucket_id) && $this->authentication->isValidUUID($request->headers->get('Authorization'))) {
                if ($this->buckets->bucket_exists($this->getter->getBucketNameFromID($bucket_id)) && $this->buckets->user_is_in_bucket($request->headers->get('Authorization'), $bucket_id)) {
                    $permissions = $this->buckets->get_permissions($request->headers->get('Authorization'), $bucket_id);
                    if (true == $permissions['rlapi.custom.bucket.user.unblock'] && $this->buckets->actor_permission_higher_than_user($permissions['rlapi.custom.bucket.permission.priority'], $user_name, $bucket_id)) {
                        $unblock_user = $this->buckets->unblock_user($user_name, $bucket_id);
                        $response = new Response(json_encode($unblock_user));
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
        if ($request->headers->has('Authorization')) {
            if ($this->authentication->isValidUUID($bucket_id) && $this->authentication->isValidUUID($request->headers->get('Authorization'))) {
                $bucket_name = $this->getter->getBucketNameFromID($bucket_id);
                if ($file_utils->get_file_owner($file_name, $this->getter->get_user_id_by_api_key($request->headers->get('Authorization')), $request->headers->get('Authorization'), $bucket_name)) {
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
