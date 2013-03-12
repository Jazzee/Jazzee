<?php
namespace Jazzee\Migration;

/**
 * Update GREScores to use decimals for storing scores
 */
class Version20130225000000 extends \Doctrine\DBAL\Migrations\AbstractMigration
{

  public function up(\Doctrine\DBAL\Schema\Schema $schema)
  {
    $table = $schema->getTable('gre_scores');
    $columns = array('score1converted','score2converted','score3converted','score4converted');
    foreach($columns as $name){
      $column = $table->getColumn($name);
      $column->setType(\Doctrine\DBAL\Types\Type::getType(\Doctrine\DBAL\Types\Type::DECIMAL));
      $column->setPrecision(10);
      $column->setScale(1);
    }
  }

  public function down(\Doctrine\DBAL\Schema\Schema $schema)
  {
    $table = $schema->getTable('gre_scores');
    $columns = array('score1converted','score2converted','score3converted','score4converted');
    foreach($columns as $name){
      $column = $table->getColumn($name);
      $column->setType(\Doctrine\DBAL\Types\Type::getType(\Doctrine\DBAL\Types\Type::INTEGER));
    }
  }
}
