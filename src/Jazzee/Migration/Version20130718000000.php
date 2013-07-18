<?php

namespace Jazzee\Migration;

/**
 * Fix problem with education page child answers
 */
class Version20130718000000 extends \Doctrine\DBAL\Migrations\AbstractMigration
{
  public function up(\Doctrine\DBAL\Schema\Schema $schema)
  {
    $sql = 'SELECT id from answers WHERE page_id IN ' .
      '(SELECT id from pages where type_id= ' .
      '(SELECT id from page_types where class="\\\Jazzee\\\Page\\\Education")) ' .
      'AND school_id IS NOT NULL';
    $rows = $this->connection->executeQuery($sql)->fetchAll();
    $fixAnswer = $this->connection->prepare('DELETE FROM answers WHERE parent_id = ? AND id NOT IN (SELECT answer_id from element_answers)');
    $count = 0;
    foreach($rows as $row){
      $fixAnswer->execute(array($row['id']));
      $count++;
    }
    $this->write("<info>Removed {$count} answers for Education pages where a bug caused an extra child answer to be stored.</info>");
  }
  
  public function down(\Doctrine\DBAL\Schema\Schema $schema){}
}
