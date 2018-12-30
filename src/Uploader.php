<?php

/**
 * Class
 */

class Uploader
{
  public $bucket;
  public $filename;

  public function __construct($bucket) {
    if(array_key_exists($_GET, 'bucket') && !is_null($_GET['bucket']){
	$this->bucket = $_GET['bucket'];
    }else{
	$this->bucket = 'owoapi';
  }
  
  public function upload($apikey) {
	
  }
}
