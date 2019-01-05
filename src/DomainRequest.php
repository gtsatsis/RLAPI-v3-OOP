<?php

include_once '../vendor/autoload.php';
use RLME\SentrySys;

class DomainRequest
{
    // nts: https://whoisapi.whoisxmlapi.com/products
    
    public $domain;
    public $key;

    public function __construct($domain)
    {
        include_once '../inc/development_domainReq_password.php';
        $this->domain = htmlentities($domain);
        $this->sentry_instance = new SentryInstance();
        $this->api_key = $d_apikey;
    }

    public function getExpirationDate()
    {
        $requestUrl = "https://www.whoisxmlapi.com/whoisserver/WhoisService?apiKey=" . $this->api_key . "&domainName=" . $this->domain . "&outputFormat=XML";
        $req = file_get_contents($requestUrl);
        $decoded_xml = simplexml_load_string($req);
        return $decoded_xml->registryData->expiresDate;
    }
}

?>