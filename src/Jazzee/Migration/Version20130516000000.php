<?php

namespace Jazzee\Migration;

/**
 * Migrate DB to page to display element and change al the 'page' to 'element' types
 */
class Version20130516000000 extends \Doctrine\DBAL\Migrations\AbstractMigration
{
  public function up(\Doctrine\DBAL\Schema\Schema $schema)
  {
    $table = $schema->getTable('display_elements');
    $table->addColumn('page_id', 'bigint', array(
        'precision' => 10,
        'notNull' => false,
    ));
    $table->addIndex(array('page_id'), 'IDX_23A0A273C4663E4');
    $table->addForeignKeyConstraint('pages', array('page_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_23A0A273C4663E4');
  }
  
  public function postUp(\Doctrine\DBAL\Schema\Schema $schema)
  {
    parent::postUp($schema);
    $sql = 'UPDATE display_elements set type="element" WHERE type="page"';
    $results = $this->connection->executeQuery($sql);
    $this->write("<info>Updated {$results->rowCount()} display elements from type 'page' to type 'element'.</info>");
  }

  public function down(\Doctrine\DBAL\Schema\Schema $schema)
  {
    $table = $schema->getTable('display_elements');
    $table->dropColumn('page_id');
    $table->dropIndex('idx_23a0a273c4663e4');
    $table->removeForeignKey('FK_23A0A273C4663E4');
  }
  
  public function postDown(\Doctrine\DBAL\Schema\Schema $schema)
  {
    parent::postDown($schema);
    $sql = 'UPDATE display_elements set type="page" WHERE type="element"';
    $results = $this->connection->executeQuery($sql);
    $this->write("<info>Updated {$results->rowCount()} display elements from type 'element' to type 'page'.</info>");
  }
}
