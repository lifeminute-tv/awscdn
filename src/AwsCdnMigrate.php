<?php

/**
 * @file
 * Contains \Drupal\aws_stream.
 */
namespace  Drupal\awscdn;

use Drupal\bcove\Bcove;
use Drupal\awscdn\Aws;
use Drupal\awscdn\AwsCdnStorage;
use Drupal\awscdn\AwsCdnLogger;
use Drupal\awscdn\AwsCdnLink;
use Drupal\Core\Entity\EntityQuery;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;



class AwsCdnMigrate {

  protected $bcove;
  protected $aws;
  protected $links;
  protected $dbh;
  protected $logger;
  protected $entity;


  /**
   * CustomPageController constructor.
   * @param LoggerChannelFactoryInterface $logger_factory
   */
  public function __construct(
    Bcove $bcove, 
    Aws $aws, 
    AwsCdnStorage $dbh, 
    AwsCdnLink $links, 
    AwsCdnLogger $logger,
    EntityTypeManager $entityMgr 
    ){
    $this->bcove = $bcove;
    $this->aws = $aws;
    $this->dbh = $dbh;
    $this->links = $links;
    $this->logger = $logger;
    $this->entityMgr = $entityMgr;

    

  }

  /**
   * @param ContainerInterface $container
   *
   * @return ContainerInjectionInterface|static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('bcove'), 
      $container->get('awscdn.aws'), 
      $container->get('awscdn.storage'), 
      $container->get('awscdn.link'),
      $container->get('awscdn.logger'),
      $container->get('entity_type.manager')
    );
  }

  public function inDB (){

  }

  public function bcids ($nid = null){
    $videos = [];
    $num = 10;

    $storage = $this->entityMgr->getStorage('node');
    $query = \Drupal::entityQuery('node')
      ->range(0, $num)
      ->exists('field_bcoveid')
      ->accessCheck(FALSE);
    if($nid){
      $query->condition('nid', $nid, 'NOT IN');
    }

    $nids = $query->execute();
    $nodes = $storage->loadMultiple($nids);
    foreach($nodes as $node){
      $videos[] = [
        'bcid'    => $node->field_bcoveid->value,
        'nodeid'     => $node->nid->value,
        'pubDate' => $node->created->value
      ];
    }
    return $videos;
    kint($videos);
    kint($nids);
    kint($nodes);
  //  kint($nodes);
  //field_bcoveid
  }
  
  public function test($word){
    echo "hazzah $word!";
    $this->bcids();
  }
}