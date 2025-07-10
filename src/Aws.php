<?php

/**
 * @file
 * Contains \Drupal\bcove\.
 */
namespace Drupal\awscdn;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\key\KeyRepository;
use Drupal\awscdn\AwsCdnLogger;
use Drupal\awscdn\AwsCdnLink;
use Drupal\awscdn\AwsCdnStorage;
use Aws\S3\S3Client;
use Aws\CommandPool;
use Aws\Exception\AwsException;
use Aws\ResultInterface;




class Aws {

  protected $key;
  protected $dbh;
  protected $links;
  protected $logger;
  protected $lockdown;


  const DEFAULT_REGION =  'us-east-1';
  const UNITS = [
    'GB' => (1024 ** 3),
    'TB' => (1024 ** 4),
  ];
  const MAX_SITE =  50 * self::UNITS['GB']; 

  public function __construct(
      KeyRepository $key, 
      AwsCdnStorage $dbh, 
      AwsCdnLink $links, 
      AwsCdnLogger $logger,  
    ) {
    $this->key = $key;
    $this->dbh = $dbh;
    $this->links = $links;
    $this->logger = $logger;
    $this->lockdown = false;
    
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static( 
      $container->get('key.repository'), 
      $container->get('awscdn.storage'), 
      $container->get('awscdn.links'),
      $container->get('awscdn.logger'),
    );
  }




  public function test($bucket){

   echo "SON";
   exit;

  }
}

  