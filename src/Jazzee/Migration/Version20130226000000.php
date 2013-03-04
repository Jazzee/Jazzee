<?php
namespace Jazzee\Migration;

/**
 * Update Answers to set public/private status to null when the answer_status_type is deleted
 */
class Version20130226000000 extends \Doctrine\DBAL\Migrations\AbstractMigration
{

  public function up(\Doctrine\DBAL\Schema\Schema $schema)
  {
    $table = $schema->getTable('answers');
    foreach($table->getForeignKeys() as $fk){
      if(strtolower($fk->getForeignTableName()) == 'answer_status_types'){
        $table->removeForeignKey($fk->getName());
      }
    }
    $table->addForeignKeyConstraint('answer_status_types', array('publicStatus_id'), array('id'), array('onDelete' => 'SET NULL'));
    $table->addForeignKeyConstraint('answer_status_types', array('privateStatus_id'), array('id'), array('onDelete' => 'SET NULL'));
  }

  public function down(\Doctrine\DBAL\Schema\Schema $schema)
  {
    $table = $schema->getTable('answers');
    foreach($table->getForeignKeys() as $fk){
      if(strtolower($fk->getForeignTableName()) == 'answer_status_types'){
        $table->removeForeignKey($fk->getName());
      }
    }
    $table->addForeignKeyConstraint('answer_status_types', array('publicStatus_id'), array('id'), array(), 'FK_50D0C606E2F2CB81');
    $table->addForeignKeyConstraint('answer_status_types', array('privateStatus_id'), array('id'), array(), 'FK_50D0C606605018D3');
  }
}
