<?php

namespace Drupal\awscdn\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\awscdn\Logger;
use Drupal\awscdn\Aws;
use Symfony\Component\DependencyInjection\ContainerInterface;



class AwsCdnController extends ControllerBase{


    protected $logger;
    protected $aws;

    public function __construct( Logger $logger,  Aws $aws) {
        $this->logger = $logger;
        $this->aws = $aws;

      }
    
      /**
       * {@inheritdoc}
       */
      public static function create(ContainerInterface $container) {
        return new static(
          $container->get('awscdn.logger' ),
           $container->get('awscdn.aws' ),
        );


      }

      
      public function streamlist (){
        $channels = $this->aws->getChannels();
        kint($channels);
        return [
          '#theme' => 'streamlist',
          '#channels' => $channels,
          '#attached' => [
            'library' => [
              'awscdn/live.vidjs',
              'awscdn/live.awsjs',
              'awscdn/live.css',
              'awscdn/stream.list'
            ],
          ],
          '#cache' => [
            'max-age' => 0,
          ]
        ];

      }

      public function test(){
      
   
        echo "bray";
        $this->aws->test('dogbones');
        exit;
      }
}

