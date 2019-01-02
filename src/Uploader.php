<?php

class Uploader
{
  public $bucket;
  public $filename;

  public function __construct($bucket)
  {
    if(isset($_GET['bucket']) && !is_null($_GET['bucket'])) //Switch from array_key_exists to isset
    {
      $this->bucket = 'owoapi';
    }
    else
    {
      die("\$_GET[\"bucket\"] is not set correctly");
    }
  }

  public function upload($apikey)
  {
    //TODO: write code to upload file with an apikey
  }
}

?>