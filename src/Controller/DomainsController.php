<?php

namespace App\Controller;

require_once __DIR__.'/../../vendor/autoload.php';

use App\Models\Domains;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DomainsController extends AbstractController
{
    /**
     * Matches /domains/add exactly.
     *
     * @Route("/domains/add", name="create_user_domain")
     */
    public function create_user_domain(Request $request)
    {
        $domains = new Domains();

        if ($request->request->has('api_key') && $request->request->has('domain')) {
            if ($request->request->has('wildcard') && $request->request->has('public')) {
                $domain_add = $domains->add_domain($request->request->get('api_key'), $request->request->get('domain'), $request->request->get('wildcard'), $request->request->get('public'));
                $response = new Response(json_encode($domain_add));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            } elseif ($request->request->has('public')) {
                $domain_add = $domains->add_domain($request->request->get('api_key'), $request->request->get('domain'), false, $request->request->get('public'));
                $response = new Response(json_encode($domain_add));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            } elseif ($request->request->has('wildcard')) {
                $domain_add = $domains->add_domain($request->request->get('api_key'), $request->request->get('domain'), $request->request->get('wildcard'), true);
                $response = new Response(json_encode($domain_add));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            } else {
                $domain_add = $domains->add_domain($request->request->get('api_key'), $request->request->get('domain'), false, true);
                $response = new Response(json_encode($domain_add));
                $response->headers->set('Content-Type', 'application/json');

                return $response;
            }
        }
    }

    /**
     * Matches /domains/verify exactly.
     *
     * @Route("/domains/verify/{domain}", name="verify_domain")
     */
    public function verify_domain(Request $request, $domain)
    {
        $domains = new Domains();

        $verify = $domains->verify_domain_txt($domain);

        $response = new Response(json_encode($verify));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
