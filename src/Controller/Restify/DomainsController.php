<?php

namespace App\Controller;

require_once __DIR__.'/../../vendor/autoload.php';

use App\Models\Domains;
use App\Utils\Auth;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DomainsController extends AbstractController
{
    public function __construct()
    {
        $this->authentication = new Auth();
    }

    /**
     * Matches /domains/add exactly.
     *
     * @Route("/domains", name="create_user_domain", methods={"POST"})
     * @Route("/domains/add", name="create_user_domain_bc", methods={"POST"})
     */
    public function create_user_domain(Request $request)
    {
        $domains = new Domains();
        $data = json_decocode($request->getContents(), true);

        if ($request->headers->has('Authorization') && array_key_exists('domain', $data)) {
            if (array_key_exists('wildcard', $data) && array_key_exists('public', $data) && array_key_exists('bucket', $data)) {
                $domain_add = $domains->add_domain($request->headers->get('Authorization'), $data['domain'], $data['wildcard'], $data['public'], $data['bucket']);
                $response = new Response(json_encode($domain_add));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            } elseif (array_key_exists('wildcard', $data) && array_key_exists('public', $data)) {
                $domain_add = $domains->add_domain($request->headers->get('Authorization'), $data['domain'], $data['wildcard'], $data['public'], getenv('S3_BUCKET'));
                $response = new Response(json_encode($domain_add));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            } elseif (array_key_exists('wildcard', $data) && array_key_exists('bucket', $data)) {
                $domain_add = $domains->add_domain($request->headers->get('Authorization'), $data['domain'], $data['wildcard'], true, $data['bucket']);
                $response = new Response(json_encode($domain_add));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            } elseif (array_key_exists('public', $data) && array_key_exists('bucket', $data)) {
                $domain_add = $domains->add_domain($request->headers->get('Authorization'), $data['domain'], false, $data['public'], $data['bucket']);
                $response = new Response(json_encode($domain_add));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            } elseif (array_key_exists('public', $data)) {
                $domain_add = $domains->add_domain($request->headers->get('Authorization'), $data['domain'], false, $data['public'], getenv('S3_BUCKET'));
                $response = new Response(json_encode($domain_add));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            } elseif (array_key_exists('wildcard', $data)) {
                $domain_add = $domains->add_domain($request->headers->get('Authorization'), $data['domain'], $data['wildcard'], true, getenv('S3_BUCKET'));
                $response = new Response(json_encode($domain_add));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            } elseif (array_key_exists('bucket', $data)) {
                $domain_add = $domains->add_domain($request->headers->get('Authorization'), $data['domain'], false, true, $data['bucket']);
                $response = new Response(json_encode($domain_add));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            } else {
                $domain_add = $domains->add_domain($request->headers->get('Authorization'), $data['domain'], false, true, getenv('S3_BUCKET'));
                $response = new Response(json_encode($domain_add));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        } else {
            $response = new Response(json_encode(['message' => 'you_did_not_supply_a_request_body']));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }

    /**
     * Matches /domains/delete exactly.
     *
     * @Route("/domains/delete", name="delete_domain", methods={"DELETE"})
     */
    public function delete_domain(Request $request)
    {
        $domains = new Domains();
        $data = json_decode($request->getContents(), true);

        if ($request->headers->has('Authorization')) {
            if ($this->authentication->isValidUUID($request->headers->get('Authorization'))) {
                if ($this->authentication->domain_exists($domain)) {
                    $domain_delete = $domains->remove_domain($request->headers->get('Authorization'), $data['domain']);
                    $response = new Response(json_encode($domain_delete));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                } else {
                    $response = new Response(json_encode(['message' => 'domain_not_found']));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                }
            } else {
                $response = new Response(json_encode(['message' => 'api_key_not_in_uuid_format']));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        } else {
            $response = new Response(json_encode(['message' => 'you_did_not_supply_an_api_key']));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }

    /**
     * Matches /domains/verify exactly.
     *
     * @Route("/domains/verify/{domain}", name="verify_domain", methods={"GET"})
     */
    public function verify_domain(Request $request, $domain)
    {
        $domains = new Domains();
        if ($this->authentication->domain_exists($domain)) {
            $verify = $domains->verify_domain_txt($domain);

            $response = new Response(json_encode($verify));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } else {
            $response = new Response(json_encode(['message' => 'domain_not_found']));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }

    /**
     * Matches /domains/privacy exactly.
     *
     * @Route("/domains/privacy", name="domain_privacy")
     */
    public function domain_privacy(Request $request, $domain)
    {
        $domains = new Domains();
        $data = json_decode($request->getContents(), true);

        if ($request->headers->has('Authorization') && array_key_exists('domain', $data)) {
            if ($this->authentication->isValidUUID($request->headers->get('Authorization'))) {
                if ($this->authentication->domain_exists($data['domain'])) {
                    if (array_key_exists('privacy', $data)) {
                        if ('public' == $data['privacy']) {
                            $set_privacy = $domains->set_privacy($data['domain'], $request->headers->get('Authorization'), 'public');
                            $response = new Response(json_encode($set_privacy));
                            $response->headers->set('Content-Type', 'application/json');

                            return $response;
                        } elseif ('private' == $data['privacy']) {
                            $set_privacy = $domains->set_privacy($data['domain'], $request->headers->get('Authorization'), 'private');
                            $response = new Response(json_encode($set_privacy));
                            $response->headers->set('Content-Type', 'application/json');

                            return $response;
                        } else {
                            $response = new Response(json_encode(['message' => 'privacy_must_be_public_or_private']));
                            $response->headers->set('Content-Type', 'application/json');

                            return $response;
                        }
                    } else {
                        $response = new Response(json_encode(['message' => 'privacy_is_missing']));
                        $response->headers->set('Content-Type', 'application/json');

                        return $response;
                    }
                } else {
                    $response = new Response(json_encode(['message' => 'domain_not_found']));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                }
            } else {
                $response = new Response(json_encode(['message' => 'api_key_not_in_uuid_format']));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        } else {
            $response = new Response(json_encode(['message' => 'you_did_not_supply_an_api_key']));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }

    /**
     * Matches /domains/{domain}/official exactly.
     *
     * @Route("/domains/{domain}/official", name="domain_official")
     */
    public function domain_official(Request $request, $domain)
    {
        $domains = new Domains();

        if ($request->request->has('api_key')) {
            if ($this->authentication->isValidUUID($request->request->get('api_key'))) {
                if ($this->authentication->api_key_is_admin($request->request->get('api_key'))) {
                    if ($this->authentication->domain_exists($domain)) {
                        $set_official_status = $domains->set_official_status($domain, $request->request->get('official'));
                        $response = new Response(json_encode($set_official_status));
                        $response->headers->set('Content-Type', 'application/json');

                        return $response;
                    } else {
                        $response = new Response(json_encode(['message' => 'domain_not_found']));
                        $response->headers->set('Content-Type', 'application/json');

                        return $response;
                    }
                } else {
                    $response = new Response(json_encode(['success' => false, 'message' => 'unauthorized']));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                }
            } else {
                $response = new Response(json_encode(['message' => 'api_key_not_in_uuid_format']));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        } else {
            $response = new Response(json_encode(['message' => 'you_did_not_supply_an_api_key']));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }

    /**
     * Matches /domains/{domain}/bucket exactly.
     *
     * @Route("/domains/{domain}/bucket", name="domain_bucket")
     */
    public function domain_bucket(Request $request, $domain)
    {
        $domains = new Domains();
        if ($request->request->has('api_key')) {
            if ($this->authentication->isValidUUID($request->request->get('api_key'))) {
                if ($request->request->has('bucket')) {
                    if ($this->authentication->domain_exists($domain)) {
                        $domain_bucket = $domains->set_domain_bucket($request->request->get('api_key'), $domain, $request->request->get('bucket'));

                        $response = new Response(json_encode($domain_bucket));
                        $response->headers->set('Content-Type', 'application/json');

                        return $response;
                    } else {
                        $response = new Response(json_encode(['message' => 'domain_not_found']));
                        $response->headers->set('Content-Type', 'application/json');

                        return $response;
                    }
                } else {
                    $response = new Response(json_encode(['success' => false, 'error' => ['error_message' => 'Request did not contain the bucket body parameter.']]));
                    $response->headers->set('Content-Type', 'application/json');

                    return $response;
                }
            } else {
                $response = new Response(json_encode(['success' => false, 'error' => ['error_message' => 'API key was not in valid UUID format.']]));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        } else {
            $response = new Response(json_encode(['success' => false, 'error' => ['error_message' => 'Request did not contain an API key.', 'required' => ['api_key', 'bucket']]]));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
    }
}
