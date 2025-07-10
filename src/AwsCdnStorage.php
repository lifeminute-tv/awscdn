<?php

namespace Drupal\awscdn;

use Drupal\awscdn\AwsCdnLogger;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the LM feed storage service.
 */
class AwsCdnStorage {
  protected $dbh;
  protected $txn;
  protected $logger;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Database\Connection $dbh
   *   The database connection.
   */
  public function __construct(Connection $dbh, AwsCdnLogger $logger) {
    $this->dbh = $dbh;
    $this->logger = $logger;
    $this->txn;
  }

  public static function create(ContainerInterface $container) {
    return new static($container->get('database'), $container->get('awscdn.logger'));
  }

  public function startTransaction (){
    if(!$this->txn){
      $this->txn = $this->dbh->startTransaction();
      return $this->txn;
    }
  }

  public function stopTransaction (){
    if($this->txn){
      unset($this->txn);
    }
  }


  public function inDB (){
    $q = $this->dbh->select('awscdn_videos', 'v')
    ->fields('v', ['nodeid']);
    return $q->execute()->fetchAll();
  }

  public function videoEntry ($entry){
    $q = $this->dbh->insert('awscdn_videos')
    ->fields($entry);
    return $q->execute()->fetchAll();
  }

  public function getMulti ($uploadID){
    $q = $this->dbh->select('lmfiles_uploadid', 'm')
    ->fields('m', []);
    $q->condition('UploadId', $uploadID, '=');
    return $q->execute()->fetchAll()[0];
  }

  public function deleteMulti($uploadID){
      $q =  $this->dbh->delete('lmfiles_uploadid');
      $q->condition('UploadId', $uploadID, '=');
      return $q->execute();
  }

  public function createUploadID ($upload){
    try {
      $id =  $this->dbh->insert('lmfiles_uploadID')
        ->fields(array(
          'UploadId'  => $upload['UploadId'],
          'Key'       => $upload['Key'],
          'filesize'  => $upload['filesize'],
          'bucket'    => $upload['bucket'],
          'area'      => $upload['area'],
          'folder'    => $upload['folder'],
          'modDate'   => $upload['modDate'],
          'creator'   => $upload['creator'],
         // 'createTime'  => \Drupal::time()->getRequestTime(),
          ))->execute();
      return $id;
    } catch (\Exception $error) {
      $this->e($error);
    }
  }

  public function createUploadPart ($uploadID, $partID){
    try {
      $this->dbh->insert('lmfiles_uploadIDparts')
        ->fields(array(
          'UploadId'  => $uploadID,
          'part'       => $partID,
          ))->execute();
    } catch (\Exception $error) {
      $this->e($error);
    }
  }

  public function deleteUploadParts($uploadID){
    $q =  $this->dbh->delete('lmfiles_uploadIDparts');
    $q->condition('UploadId', $uploadID, '=');
    return $q->execute();
}

  public function createFile ($file){
    try {
      $id =  $this->dbh->insert('lmfiles_files')
        ->fields(array(
          'name'          => $file['name'],
          'bucket'        => $file['bucket'],
          'extension'     => $file['ext'],
          'size'          => $file['size'],
          'nameOriginal'  => $file['nameOriginal'],
          'createDate'    => $file['createDate'],
          'uploadDate'    => $file['uploadDate'],
          'creator'       => $file['creator'],

          ))->execute();
      return $id;
    } catch (\Exception $error) {
      $this->e($error);
    }
  }

  public function updateBucket($fileID, $fileBucket){
    try {
      $q =  $this->dbh->update('lmfiles_files')
      ->fields(array('bucket' => $fileBucket))
      ->condition('id', $fileID, '=')
      ->execute();

      return $q;
    } catch (\Exception $error) {
      $this->e('Update Bucket Error: ', $error);
    }

  }

  public function fileArea ($file, $area){
    try {
      $id =  $this->dbh->insert('lmfiles_fileArea')
        ->fields(array(
          'file'  => $file,
          'area'  => $area,
          ))->execute();
      return $id;
    } catch (\Exception $error) {
      $this->e($error);
    }
  }

  public function bucketSize ($bucket = NULL){
    $query = $this->dbh->select('lmfiles_files', 'n')
    ->fields('n', ['bucket'])
    ->groupBy('bucket');
    $query->addExpression('SUM(n.size)', 'sum');
    if($bucket){
      $query->condition('n.bucket',  $bucket, '=');
    }
    return $query->execute()->fetchAll();
  }



  public function createMime ($ext, $mime){
    try {
      $id =  $this->dbh->insert('lmfiles_mime')
        ->fields(array(
          'ext'  => $ext,
          'mimeType'  => $mime,
          ))->execute();
      return $id;
    } catch (\Exception $error) {
      $this->e($error);
    }
  }

  public function rollback (){
    if($this->txn){
      $this->txn->rollBack();
      $this->stopTransaction();
    }
  }

  private function e ($error){
    if($this->txn){
      $this->txn->rollBack();
      $this->stopTransaction();
    }
    \Drupal::logger('lmfilesDB')->error($error->getMessage());
    echo "Sorry, a Database Error Occurred";
    exit;
  }
}