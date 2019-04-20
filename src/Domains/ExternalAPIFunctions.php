<?php

namespace App\Domains;

require_once __DIR__.'/../../vendor/autoload.php';

use App\Utils\Sentry;

class ExternalAPIFunctions
{
    // nts: https://whoisapi.whoisxmlapi.com/products

    public $api_key;
    public $domain;
    public $key;

    public function __construct($domain)
    {
        include_once '../../inc/development_domainReq_password.php';
        $this->domain = $domain;
        $this->sentry_instance = new Sentry();
        $this->api_key = $d_apikey;
    }

    public function getExpirationDate()
    {
        $requestUrl = 'https://www.whoisxmlapi.com/whoisserver/WhoisService?apiKey='.$this->api_key.'&domainName='.$this->domain.'&outputFormat=XML';
        $req = file_get_contents($requestUrl);
        $decoded_xml = simplexml_load_string($req);

        return $decoded_xml->registryData->expiresDate;
    }
}
