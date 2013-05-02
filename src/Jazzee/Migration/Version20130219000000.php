<?php
namespace Jazzee\Migration;

/**
 * Update to use the files table to store all blobs and reference them by their
 * hash tag in attachments and element_answers 
 */
class Version20130219000000 extends \Doctrine\DBAL\Migrations\AbstractMigration
{
  protected $_tmpDir = null;
  
  public function preUp(\Doctrine\DBAL\Schema\Schema $schema) {
    parent::preUp($schema);
    $this->_tmpDir = rtrim(sys_get_temp_dir(),\DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR . uniqid();
    mkdir($this->_tmpDir, 0777, true);
    $this->storeElementAnswers();
    $this->storeAttachments();
  }
  
  public function up(\Doctrine\DBAL\Schema\Schema $schema)
  {
    // Create table: files
    $table = $schema->createTable('files');
    $table->addColumn('id', 'bigint', array('autoincrement' => true));
    $table->addColumn('hash', 'string', array('length' => 128));
    $table->addColumn('encodedBlob', 'text', array());
    $table->addColumn('referenceCount', 'integer', array());
    $table->addColumn('lastModification', 'datetime', array());
    $table->addColumn('lastAccess', 'datetime', array());
    $table->addUniqueIndex(array('hash'), 'UNIQ_6354059D1B862B8');
    $table->setPrimaryKey(array('id'), 'primary');

    // Alter table: attachments
    $table = $schema->getTable('attachments');
    $table->addColumn('attachmentHash', 'string', array('length' => 128));
    $table->addColumn('thumbnailHash', 'string', array(
        'length' => 128,
        'notNull' => false,
    ));
    $table->dropColumn('attachment');
    $table->dropColumn('thumbnail');
  }
  
  public function postUp(\Doctrine\DBAL\Schema\Schema $schema) {
    parent::postUp($schema);
    $this->migrateElementAnswers();
    $this->migrateAttachments();
    foreach (scandir($this->_tmpDir) as $item) {
      if ($item == '.' || $item == '..') continue;
      unlink($this->_tmpDir.DIRECTORY_SEPARATOR.$item);
    }
    rmdir($this->_tmpDir);
  }
  
  
  public function preDown(\Doctrine\DBAL\Schema\Schema $schema) {
    parent::preDown($schema);
    $this->_tmpDir = rtrim(sys_get_temp_dir(),\DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR . uniqid();
    mkdir($this->_tmpDir, 0777, true);
    $this->storeFiles();
  }

  public function down(\Doctrine\DBAL\Schema\Schema $schema)
  {
    // Drop table: files
    $schema->dropTable('files');
    // Alter table: attachments
    $table = $schema->getTable('attachments');
    $table->addColumn('attachment', 'text', array(
        'precision' => 10,
        'comment' => '',
    ));
    $table->addColumn('thumbnail', 'text', array(
        'precision' => 10,
        'notNull' => false,
        'comment' => '',
    ));
    $table->dropColumn('attachmenthash');
    $table->dropColumn('thumbnailhash');
  }
  
  public function postDown(\Doctrine\DBAL\Schema\Schema $schema) {
    parent::postDown($schema);
    $this->migrateFiles();
    foreach (scandir($this->_tmpDir) as $item) {
      if ($item == '.' || $item == '..') continue;
      unlink($this->_tmpDir.DIRECTORY_SEPARATOR.$item);
    }
    rmdir($this->_tmpDir);
  }
  
  protected function storeElementAnswers()
  {
    $inserts = array();
    $sql = 'SELECT id FROM element_answers WHERE eBlob IS NOT NULL';
    $ids = $this->connection->executeQuery($sql)->fetchAll(\PDO::FETCH_COLUMN);
    $fetchElement = $this->connection->prepare('SELECT eBlob FROM element_answers WHERE id=?');
    $total = count($ids);
    foreach($ids as $id){
      $fetchElement->execute(array($id));
      $encodedBlob = $fetchElement->fetchColumn();
      $blob = base64_decode($encodedBlob);
      $hash = \sha1($blob);
      $path = $this->_tmpDir . \DIRECTORY_SEPARATOR .  $hash;
      if(!file_exists($path)){
        file_put_contents($path, $blob);
      }
      $inserts[$id] = $hash;
      
    }
    file_put_contents($this->_tmpDir . \DIRECTORY_SEPARATOR . 'element_answers_inserts', \serialize($inserts));
    $this->write("<info>Stored {$total} element_answers.</info>");
  }
  
  protected function storeAttachments()
  {
    $inserts = array();
    $sql = 'SELECT id FROM attachments';
    $ids = $this->connection->executeQuery($sql)->fetchAll(\PDO::FETCH_COLUMN);
    $fetchAttachment = $this->connection->prepare('SELECT attachment FROM attachments WHERE id=?');
    $total = count($ids);
    foreach($ids as $id){
      $fetchAttachment->execute(array($id));
      $encodedBlob = $fetchAttachment->fetchColumn();
      $blob = base64_decode($encodedBlob);
      $hash = \sha1($blob);
      $path = $this->_tmpDir . \DIRECTORY_SEPARATOR .  $hash;
      if(!file_exists($path)){
        file_put_contents($path, $blob);
      }
      $inserts[$id] = $hash;
    }
    file_put_contents($this->_tmpDir . \DIRECTORY_SEPARATOR . 'attachment_inserts', \serialize($inserts));
    $this->write("<info>Stored {$total} attachments.</info>");
  }
  
  protected function storeFiles()
  {
    $files = array();
    $sql = 'SELECT id FROM files';
    $ids = $this->connection->executeQuery($sql)->fetchAll(\PDO::FETCH_COLUMN);
    $fetchFile = $this->connection->prepare('SELECT hash,encodedBlob FROM files WHERE id=?');
    $total = count($ids);
    foreach($ids as $id){
      $fetchFile->execute(array($id));
      $file = $fetchFile->fetch(\PDO::FETCH_ASSOC);
      $path = $this->_tmpDir . \DIRECTORY_SEPARATOR .  $file['hash'];
      if(!file_exists($path)){
        file_put_contents($path, base64_decode($file['encodedBlob']));
      }
      $arr = array('attachments'=>array(), 'element_answers'=>array());
      foreach($this->connection->executeQuery('SELECT id FROM attachments WHERE attachmentHash=?', array($file['hash']))->fetchAll(\PDO::FETCH_COLUMN) as $id){
        $arr['attachments'][] = $id;
      }
      foreach($this->connection->executeQuery('SELECT id FROM element_answers WHERE eShortString=?', array($file['hash']))->fetchAll(\PDO::FETCH_COLUMN) as $id){
        $arr['element_answers'][] = $id;
      }
      $files[$file['hash']] = $arr;
    }
    file_put_contents($this->_tmpDir . \DIRECTORY_SEPARATOR . 'files_inserts', \serialize($files));
    $this->write("<info>Stored {$total} files.</info>");
  }
  
  protected function migrateElementAnswers()
  {
    $arr = unserialize(file_get_contents($this->_tmpDir . \DIRECTORY_SEPARATOR . 'element_answers_inserts'));
    $fetchElement = $this->connection->prepare('SELECT eBlob FROM element_answers WHERE id=?');
    $checkHash = $this->connection->prepare('SELECT id FROM files WHERE hash=?');
    $insertFile = $this->connection->prepare('INSERT INTO files (hash, encodedBlob, referenceCount) VALUES (?,?,?)');
    $updateFile = $this->connection->prepare('UPDATE files set referenceCount=referenceCount+1 WHERE hash=?');
    $updateAnswer = $this->connection->prepare('UPDATE element_answers SET eBlob = null, eShortString=? WHERE id =?');
    $new = 0;
    $update = 0;
    $total = count($arr);
    foreach($arr as $id => $hash){
      $encodedBlob = base64_encode(file_get_contents($this->_tmpDir . \DIRECTORY_SEPARATOR .  $hash));
      $checkHash->execute(array($hash));
      if($checkHash->rowCount() == 0){
        $new++;
        $insertFile->execute(array($hash, $encodedBlob, 1));
      } else {
        $update++;
        $updateFile->execute(array($hash));
      }
      $updateAnswer->execute(array($hash, $id));
    }
    $this->write("<info>Migrated {$total} element_answers.  Created {$new} new files and reused {$update}.</info>");
  }
  
  protected function migrateAttachments()
  {
    $arr = unserialize(file_get_contents($this->_tmpDir . \DIRECTORY_SEPARATOR . 'attachment_inserts'));
    $checkHash = $this->connection->prepare('SELECT id FROM files WHERE hash=?');
    $insertFile = $this->connection->prepare('INSERT INTO files (hash, encodedBlob, referenceCount) VALUES (?,?,?)');
    $updateFile = $this->connection->prepare('UPDATE files set referenceCount=referenceCount+1 WHERE hash=?');
    $updateAttachment = $this->connection->prepare('UPDATE attachments SET attachmentHash=? WHERE id =?');
    $new = 0;
    $update = 0;
    $total = count($arr);
    foreach($arr as $id => $hash){
      $encodedBlob = base64_encode(file_get_contents($this->_tmpDir . \DIRECTORY_SEPARATOR .  $hash));
      $checkHash->execute(array($hash));
      if($checkHash->rowCount() == 0){
        $new++;
        $insertFile->execute(array($hash, $encodedBlob, 1));
      } else {
        $update++;
        $updateFile->execute(array($hash));
      }
      $updateAttachment->execute(array($hash, $id));
    }
    $this->write("<info>Migrated {$total} attachments.  Created {$new} new files and reused {$update}.</info>");
  }
  
  protected function migrateFiles()
  {
    $files = unserialize(file_get_contents($this->_tmpDir . \DIRECTORY_SEPARATOR . 'files_inserts'));
    $updateAttachment = $this->connection->prepare('UPDATE attachments SET attachment=? WHERE id =?');
    $updateElement = $this->connection->prepare('UPDATE element_answers SET eBlob=? , eShortString=null WHERE id =?');
    $elementAnswers = 0;
    $attachments = 0;
    foreach($files as $hash => $arr){
      $encodedBlob = base64_encode(file_get_contents($this->_tmpDir . \DIRECTORY_SEPARATOR .  $hash));
      foreach($arr['element_answers'] as $id){
        $updateElement->execute(array($encodedBlob, $id));
        $elementAnswers++;
      }
      foreach($arr['attachments'] as $id){
        $updateAttachment->execute(array($encodedBlob, $id));
        $attachments++;
      }
      $updateAttachment->execute(array($hash, $id));
    }
    $this->write("<info>Migrated {$elementAnswers} element answers and {$attachments} attachments.</info>");
  }
}
