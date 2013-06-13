<?php

namespace Jazzee\Migration;

/**
 * Migrate to seperate display types for role/user  make all the existing Displays user
 */
class Version20130611000000 extends \Doctrine\DBAL\Migrations\AbstractMigration
{
  public function up(\Doctrine\DBAL\Schema\Schema $schema)
  {
    $table = $schema->getTable('displays');
    $table->addColumn('type', 'string', array('length' => 255));
    $table->addColumn('role_id', 'bigint', array(
      'precision' => 10,
      'notNull' => false,
    ));
    $table->addUniqueIndex(array('role_id'), 'UNIQ_54DDEC2BD60322AC');
    $table->addForeignKeyConstraint('roles', array('role_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_54DDEC2BD60322AC');
  }
  
  public function postUp(\Doctrine\DBAL\Schema\Schema $schema)
  {
    parent::postUp($schema);
    $sql = 'UPDATE displays set type="user" WHERE type=""';
    $results = $this->connection->executeQuery($sql);
    $this->write("<info>Updated {$results->rowCount()} displays from type '' to type 'user'.</info>");
  }

  public function down(\Doctrine\DBAL\Schema\Schema $schema)
  {
    $table = $schema->getTable('displays');
    $table->dropColumn('type');
    $table->dropColumn('role_id');
    $table->dropIndex('uniq_54ddec2bd60322ac');
    $table->removeForeignKey('FK_54DDEC2BD60322AC');
  }
}
