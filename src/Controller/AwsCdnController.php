<?php

namespace Drupal\awscdn\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\awscdn\AwsCdnLogger;
use Drupal\awscdn\AwsCdnMigrate;
use Drupal\awscdn\Aws;
use Symfony\Component\DependencyInjection\ContainerInterface;



class AwsCdnController extends ControllerBase{

    protected $migrate;
    protected $logger;
    protected $aws;
 

    public function __construct( AwsCdnLogger $logger,  Aws $aws, AwsCdnMigrate $migrate) {
        $this->logger = $logger;
        $this->aws = $aws;
        $this->migrate = $migrate;
      }
    
      /**
       * {@inheritdoc}
       */
      public static function create(ContainerInterface $container) {
        return new static(
          $container->get('awscdn.logger'),
           $container->get('awscdn.aws'),
           $container->get('awscdn.migrate'),
        );


      }

    

      public function test(){
      
   
        echo "bray";
        $this->migrate->test('dogbones');
        exit;
      }
}

