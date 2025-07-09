<?php

/**
 * @file
 * Contains \Drupal\bcove\.
 */
namespace Drupal\awscdn;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\key\KeyRepository;
use Drupal\awscdn\Logger;
use Aws\IVS\IVSClient;
use DateTime;
use DateTimeZone;




class Aws {

  protected $key;
  protected $logger;



  const DEFAULT_REGION =  'us-east-1';


  public function __construct(
      KeyRepository $key, 
      Logger $logger,

    ) {
    $this->key = $key;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static( 
      $container->get('key.repository'), 
      $container->get('awscdn.logger'),

    );
  }

  private function authKey ($name){
    $loginKeys = $this->key->getKey($name)->getKeyValues();

    if($loginKeys['access'] && $loginKeys['secret']){
      return [
        'key' => $loginKeys['access'],
        'secret' => $loginKeys['secret'],
      ];
    }else{
       $this->logger->e('key error: failed to get key for '. $name);
    }
  }



  public function IVSClient(){
    $client_config = [];
    $client_config['credentials'] = $this->authKey('streamkey');
    $client_config['region'] = self::DEFAULT_REGION;
    $client_config['version'] = 'latest';
    return new IVSClient($client_config);
  }


  public function streams($arn){
    $ivs = $this->IVSClient();
    $streams = $ivs->listStreamSessions(['channelArn' => $arn ]);
    return $streams['streamSessions'];
  }

  public function streamLive($arn){
    $ivs = $this->IVSClient();
    $streams = $ivs->listStreamSessions(['channelArn' => $arn ]);
    foreach($streams['streamSessions'] as $stream){
      if(!array_key_exists('endTime',$stream)){
        $now = new DateTime();
        $data = $ivs->getStream(['channelArn' => $arn])['stream'] ;
        $data['uptime'] =  date_diff($now, $stream['startTime'])->format("%H:%I:%S");
        return $data;
      }
    }
  }

    public function getChannels(){
    $ivs = $this->IVSClient();
    $channels = [];
    $list = $ivs->listChannels()['channels'];
    foreach($list as $channel){
      $ch = $channel;
      if($ch['arn']){
        $ch['live'] = $this->streamLive($ch['arn']);
        $channels[] = $ch;
      }

    }
    return $channels;
  }

    public function streaming($channel){
    $live = false;
    $streams =  $this->streams($channel);
    foreach($streams as $stream){
      if(!$stream['endTime']){
        $now = new DateTime();
        kint($stream);
        $live = [
          'streamId' => $stream['streamId'],
          'uptime' => date_diff($now, $stream['startTime'])->format("%H:%I:%S")
        ];
      }
    }
    return $live;
  }

  public function test($bucket){

   echo "SON";
   exit;

  }
}

  