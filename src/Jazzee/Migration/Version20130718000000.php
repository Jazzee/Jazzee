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
      'AND school_id IS NOT NULL ' .
      'AND id IN (SELECT parent_id FROM answers)';
    $rows = $this->connection->executeQuery($sql)->fetchAll();
    $fixAnswer = $this->connection->prepare('DELETE FROM answers WHERE parent_id = ? AND id NOT IN (SELECT answer_id from element_answers)');
    $count = 0;
    foreach($rows as $row){
      $fixAnswer->execute(array($row['id']));
      $count++;
    }
    if($count > 0){
      $this->write("<info>Removed {$count} answers for Education pages where a bug caused an extra child answer to be stored.</info>");
    }

    $sql = 'SELECT id, page_id from answers WHERE parent_id IN ' . 
      '(SELECT id from answers WHERE page_id IN ' .
      '(SELECT id from pages where type_id= ' .
      '(SELECT id from page_types where class="\\\Jazzee\\\Page\\\Education")) ' .
      'AND school_id IS NULL) AND id NOT IN (SELECT answer_id FROM element_answers)';
    $rows = $this->connection->executeQuery($sql)->fetchAll();
    $statement = 'INSERT INTO element_answers (answer_id, element_id, position, eShortString) VALUES ' .
    '(:answerId, (SELECT id FROM elements WHERE page_id = :pageId AND fixedId=' . \Jazzee\Page\Education::ELEMENT_FID_NAME . '), 0, "Unknown School"), ' .
    '(:answerId, (SELECT id FROM elements WHERE page_id = :pageId AND fixedId=' . \Jazzee\Page\Education::ELEMENT_FID_COUNTRY . '), 0, "Unknown"), ' .
    '(:answerId, (SELECT id FROM elements WHERE page_id = :pageId AND fixedId=' . \Jazzee\Page\Education::ELEMENT_FID_CITY . '), 0, "Unknown")';
    $addUnknowSchool = $this->connection->prepare($statement);
    $count = 0;
    foreach($rows as $row){
      $addUnknowSchool->bindValue(':answerId', $row['id'],  \PDO::PARAM_INT);
      $addUnknowSchool->bindValue(':pageId', $row['page_id'], \PDO::PARAM_INT);
      $addUnknowSchool->execute();
      $count++;
    }
    if($count > 0){
      $this->write("<info>Modified {$count} answers for Education pages where a bug caused Known School data to be dropped.</info>");
    }
  }
  
  public function down(\Doctrine\DBAL\Schema\Schema $schema){}
}
