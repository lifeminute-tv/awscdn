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


   public function fixBcid (){

    $offset = 0;
    $num = 2000;
    $storage = $this->entityMgr->getStorage('node');

    while(1){
      kint("offset: $offset");
      
      $query = \Drupal::entityQuery('node')
        ->range($offset, $num)
        ->exists('field_bcoveid')
        ->sort('created', 'ASC')
        ->accessCheck(FALSE);

      $nids = $query->execute();
      $nodes = $storage->loadMultiple($nids);
      foreach($nodes as $node){
        if( preg_match('/\s/',$node->field_bcoveid->value)){
          kint('Fixin Space');
          kint($node->nid->value);
          kint($node->field_bcoveid->value);
          $bcid = str_replace(' ', '', $node->field_bcoveid->value);
          $node->field_bcoveid->value = "temp";
          $node->save();
          kint($node->field_bcoveid->value);
          $node->field_bcoveid->value = $bcid;
          $node->save();
          kint($node->field_bcoveid->value);
        }
      }
      $offset += $num;
    }
   }

  public function videoDB (){

    $run = 1;
    while($run){
      $videos = $this->bcids();
      if(count($videos)){
        foreach($videos as $video){
          $this->dbh->videoEntry($video);
        }
      }else{
        $run = NULL;
      }
      kint('Ahoy Mateyu');
    }
  }


  public function bcids (){
    $videos = [];
    $num = 10;

    $bcids = $this->dbh->inDB();

    $storage = $this->entityMgr->getStorage('node');
    $query = \Drupal::entityQuery('node')
      ->range(0, $num)
      ->exists('field_bcoveid')
      ->sort('created', 'ASC')
      ->accessCheck(FALSE);
    if(count($bcids)){
      $query->condition('field_bcoveid', $bcids, 'NOT IN');
    }

    $nids = $query->execute();
    $nodes = $storage->loadMultiple($nids);
    foreach($nodes as $node){
      $videos[] = [
        'bcid'    => (int) $node->field_bcoveid->value,
        'nodeid'     => $node->nid->value,
        'pubDate' =>  date( 'Y-m-d H:i:s', $node->created->value)
      ];
    }
    return $videos;
  }
  
  public function test($word){


    echo "hazzah $word!";

    $this->videoDB();
    exit;
 
    $this->fixBcid();


    exit;
    $this->videoDB();
  }
}