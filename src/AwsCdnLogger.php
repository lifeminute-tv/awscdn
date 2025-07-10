<?php

/**
 * @file
 * Contains \Drupal\aws_stream.
 */
namespace  Drupal\awscdn;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;



class AwsCdnLogger {

  protected $logger;
  protected $msg;


  /**
   * CustomPageController constructor.
   * @param LoggerChannelFactoryInterface $logger_factory
   */
  public function __construct(LoggerChannelFactoryInterface $logger, MessengerInterface $msg)
  {
    $this->logger = $logger->get('awscdn');
    $this->msg = $msg;

  }

  /**
   * @param ContainerInterface $container
   *
   * @return ContainerInjectionInterface|static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('logger.channel.default'),
      $container->get('messenger')
    );
  }


  public function drupalMessage ($message, $data = NULL){ //drupal message
    if($data){
      $message .= ' DATA: '. json_encode($data);
    }
    $this->msg->addMessage($message);
  }

  public function info($message, $data = NULL) {
    if($data){
      $message .= ' DATA: '. json_encode($data);
    }
    $this->logger->info($message);
  }

  public function e($message, $data = NULL) {
    if($data){
      $message .= ' DATA: '. json_encode($data);
    }
    $this->logger->error($message);  
  }

    public function e_page($message, $data = NULL) {
      $message = 'FATAL ERROR: '. $message;
    if($data){
      $message .= ' | DATA: '. json_encode($data);
    }
    $this->logger->error($message);  
    exit;
  }



}