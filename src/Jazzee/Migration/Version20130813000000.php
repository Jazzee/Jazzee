<?php

namespace Jazzee\Migration;

/**
 * Modify applicant answers for TextInput and TextArea answers to htmlentity
 * all input
 */
class Version20130813000000 extends \Doctrine\DBAL\Migrations\AbstractMigration
{
  public function up(\Doctrine\DBAL\Schema\Schema $schema)
  {
    ini_set('memory_limit', -1);
    $sql = 'SELECT id,eShortString from element_answers WHERE element_id IN ' .
      '(SELECT id from elements where type_id IN ' .
      '(SELECT id from element_types where ' .
      'class="\\\Jazzee\\\Element\\\TextInput"' .
      '))';
    $fixShortString = $this->connection->prepare('UPDATE element_answers SET eShortString=:newString WHERE id=:id');
    $stmt = $this->connection->executeQuery($sql);
    $updated = 0;
    while ($row = $stmt->fetch()) {
      $newString = htmlentities ($row['eShortString'], \ENT_COMPAT, 'UTF-8', false);
      if($row['eShortString'] != $newString){
        $fixShortString->bindParam(':id', $row['id']);
        $fixShortString->bindParam(':newString', $newString);
        $fixShortString->execute();
        $updated++;
      }
      
    }
    if($updated > 0){
      $this->write("<info>Modified {$updated} TextInput elements for applicants to encode their answers with HTML entities.</info>");
    }
    
    $sql = 'SELECT id,eText from element_answers WHERE element_id IN ' .
      '(SELECT id from elements where type_id IN ' .
      '(SELECT id from element_types where ' .
      'class="\\\Jazzee\\\Element\\\Textarea"' .
      '))';
    $fixText = $this->connection->prepare('UPDATE element_answers SET eText=:newString WHERE id=:id');
    $stmt = $this->connection->executeQuery($sql);
    $updated = 0;
    while ($row = $stmt->fetch()) {
      $newString = htmlentities ($row['eText'], \ENT_COMPAT, 'UTF-8', false);
      if($row['eText'] != $newString){
        $fixText->bindParam(':id', $row['id']);
        $fixText->bindParam(':newString', $newString);
        $fixText->execute();
        $updated++;
      }
      
    }
    if($updated > 0){
      $this->write("<info>Modified {$updated} TextArea elements for applicants to encode their answers with HTML entities.</info>");
    }
    
  }
  
  public function down(\Doctrine\DBAL\Schema\Schema $schema)
  {
    ini_set('memory_limit', -1);
    $doubleEncoded = array("<"=>"&lt;", ">"=>"&gt;");
    $sql = 'SELECT id,eShortString from element_answers WHERE element_id IN ' .
      '(SELECT id from elements where type_id IN ' .
      '(SELECT id from element_types where ' .
      'class="\\\Jazzee\\\Element\\\TextInput"' .
      '))';
    $fixShortString = $this->connection->prepare('UPDATE element_answers SET eShortString=:newString WHERE id=:id');
    $stmt = $this->connection->executeQuery($sql);
    $updated = 0;
    
    while ($row = $stmt->fetch()) {
      $newString = str_replace(
        array_keys($doubleEncoded),
        array_values($doubleEncoded),
        html_entity_decode ($row['eShortString']));
      if($row['eShortString'] != $newString){
        $fixShortString->bindParam(':id', $row['id']);
        $fixShortString->bindParam(':newString', $newString);
        $fixShortString->execute();
        $updated++;
      }
      
    }
    if($updated > 0){
      $this->write("<info>Modified {$updated} TextInput elements for applicants to only escape <> in their answers.</info>");
    }
    
    $sql = 'SELECT id,eText from element_answers WHERE element_id IN ' .
      '(SELECT id from elements where type_id IN ' .
      '(SELECT id from element_types where ' .
      'class="\\\Jazzee\\\Element\\\Textarea"' .
      '))';
    $fixText = $this->connection->prepare('UPDATE element_answers SET eText=:newString WHERE id=:id');
    $stmt = $this->connection->executeQuery($sql);
    $updated = 0;
    while ($row = $stmt->fetch()) {
      $newString = str_replace(
        array_keys($doubleEncoded),
        array_values($doubleEncoded),
        html_entity_decode ($row['eText']));
      if($row['eText'] != $newString){
        $fixText->bindParam(':id', $row['id']);
        $fixText->bindParam(':newString', $newString);
        $fixText->execute();
        $updated++;
      }
      
    }
    if($updated > 0){
      $this->write("<info>Modified {$updated} Textarea elements for applicants to only escape <> in their answers.</info>");
    }
  }
}
