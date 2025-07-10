<?php

/**
 * @file
 * Contains \Drupal\bcove\.
 */
namespace Drupal\awscdn;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\key\KeyRepository;


class AwsCdnLink {

  protected $key;
  protected $user;

  const SESSION_NAME = 'lmfiles_data';
  const AUTH_KEY_NAME = 'lm_connect_key';

  const URL = [
    'upload'  => [  
      'path' => '/upload', 
      'route' => 'lmfiles.s3post'
    ],
    'areaForm'  => [  
      'path' => '/form/area/', 
      'route' => 'lmfiles.areaForm'
    ],
    'multi'  => [  
      'path' => '/multi', 
      'route' => 'lmfiles.s3.multi'
    ],
    'multiList'  => [  
      'path' => '/multi/list/', 
      'route' => 'lmfiles.s3.multi.list'
    ],
    'multiAbort'  => [  
      'path' => '/multi/abort/', 
      'route' => 'lmfiles.s3.multi.abort'
    ],
  ];

  const SERVER = [
    '/var/vhosts/lmFiles_PRODUCTION/web' => [
      'type' => 'production', 
      'url' => 'https://files.lifeminute.tv', 
      'lambda-key' => 'lm_connect_key'
    ],

    '/var/vhosts/dev/lmFiles_DEV/web' => [
      'type' => 'dev', 
      'url' => 'https://lmdev.tv', 
      'lambda-key' => 'lm_connect_key_development'
    ],

    'C:\Users\mike\c\htdocs\lmfiles_DEV\web'  => [
        'type' => 'dev', 
        'url' => 'http://lmfiles', 
        'lambda-key' => 'lm_connect_key_development'
      ]
  ];

  const BUCKET = [
    '/var/vhosts/lmFiles_PRODUCTION/web' => [
      'upload' =>  'lifeminute-upload', 
      'file'    => 'lifeminute-files', 
    ],
    '/var/vhosts/dev/lmFiles_DEV/web' => [
      'upload' =>  'lifeminute-upload-development', 
      'file'    => 'lifeminute-files-development', 
    ],
    'C:\Users\mike\c\htdocs\lmfiles_DEV\web'  => [
      'upload' =>  'lifeminute-upload-development', 
      'file'    => 'lifeminute-files-development', 
    ],
  ];

  const IAM = [
    '/var/vhosts/lmFiles_PRODUCTION/web' => [
      'upload' =>  ['access' => 'lifeminute_upload_access',  'secret' => 'lifeminute_upload_secret'],
      'file'    => ['access' => 'lifeminute_file_access',  'secret' => 'lifeminute_file_secret'], 
    ],
    '/var/vhosts/dev/lmFiles_DEV/web' => [
      'upload' =>  ['access' => 'lifeminute_upload_access_development',  'secret' => 'lifeminute_upload_secret_development'],
      'file'    => ['access' => 'lifeminute_file_access_development',  'secret' => 'lifeminute_file_secret_development'], 
    ],
    'C:\Users\mike\c\htdocs\lmfiles_DEV\web'  => [
      'upload' =>  ['access' => 'lifeminute_upload_access_development',  'secret' => 'lifeminute_upload_secret_development'],
      'file'    => ['access' => 'lifeminute_file_access_development',  'secret' => 'lifeminute_file_secret_development'], 
    ],
  ];



  public function __construct(KeyRepository $key) {
      $this->key = $key;

  }

  public static function create(ContainerInterface $container) {
    return new static($container->get('key.repository') );
}

public function auth ($test){
  $authName = self::SERVER[$this->rootPath]['lambda-key'];
  $authKey = $this->key->getKey($authName)->getKeyValue();
  if($authKey == $test){
    unset( $authKey, $authName);
    return 1;
  }else{
    return NULL;
  }
}

public function setSession ($key, $value){

  $session = \Drupal::request()->getSession();
  $data = $session->get(self::SESSION_NAME);
  if($data){
    $data = json_decode($data);
  }else{
    $data = (object) [];
  }
  $data->$key = $value;
  $json = json_encode($data);

  $session->set(self::SESSION_NAME, $json);
}

public function getSession ($key){
  $session = \Drupal::request()->getSession();

  $data = $session->get(self::SESSION_NAME);
  if($data){
    $data = json_decode($data);
  }else{
    return;
  }
  return $data->$key;
}

public function link ($name){
  return self::URL[$name]['path'];
}

public function route ($name){
  return self::URL[$name]['route'];
}

public function linkTree (){
  $tree = [];
  foreach(self::URL as $name => $path){
    $tree[$name] = $path['path'];
  }
  return $tree;
}


public function homeUrl (){
  if(array_key_exists($this->rootPath, self::SERVER)){
    return  self::SERVER[$this->rootPath]['url'];
  }else{
    $message = "path error for homeUrl";
    $log =$this->rootpath;
    $this->errorPage($message, $log);
  }
}

public function serverType (){
  if(array_key_exists($this->rootPath, self::SERVER)){
    return  self::SERVER[$this->rootPath]['type'];
  }else{
    $message = "path error for homeUrl";
    $log =$this->rootpath;
    $this->errorPage($message, $log);
  }
}

public function bucketName ($name){
  if( array_key_exists($this->rootPath, self::BUCKET) &&
      array_key_exists($name, self::BUCKET[$this->rootPath])
  ){
    return self::BUCKET[$this->rootPath][$name];
  }else{
    $message = "path error for upbucket";
    $log = "path: ".$this->rootpath. " , Bucket Name: ". $name;
    $this->errorPage($message, $log);
  }
}

public function iam ($user){
  if(array_key_exists($this->rootPath, self::IAM) &&
     array_key_exists($user, self::IAM[$this->rootPath])
  ){
    return  self::IAM[$this->rootPath][$user];
  }else{
    $message = "path error for user";
    $log =$this->rootpath;
    $this->errorPage($message, $log);
  }
}

public function age ($date){
  $now = time(); // or your date as well
  $date = strtotime($date);
  $datediff =  $now - $date;
  return round(($now - $date) / (60 * 60 * 24));
}











public function test (){
  kint($this->bucketName('upload'));
  echo 'f';
  echo 'me';
 // kint($this->fileBucket());
 // kint($this->bucketKey($this->fileBucket()));
}

  }

  