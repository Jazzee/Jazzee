<?php

namespace Jazzee\Migration;

/**
 * Correctly cascade role, program relationships
 */
class Version20130702000000 extends \Doctrine\DBAL\Migrations\AbstractMigration
{
  public function up(\Doctrine\DBAL\Schema\Schema $schema)
  {
    // Alter table: roles
    $table = $schema->getTable('roles');
    $table->addForeignKeyConstraint('programs', array('program_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_B63E2EC73EB8070A');
  }

  public function down(\Doctrine\DBAL\Schema\Schema $schema)
  {
    // Alter table: roles
    $table = $schema->getTable('roles');
    $table->removeForeignKey('FK_B63E2EC73EB8070A');
  }
}
