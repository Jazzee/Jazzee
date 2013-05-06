<?php

namespace Jazzee\Migration;

/**
 * Migration to varaibles list items
 */
class Version20130502000000 extends \Doctrine\DBAL\Migrations\AbstractMigration
{
    public function up(\Doctrine\DBAL\Schema\Schema  $schema)
    {
      $table = $schema->createTable('element_list_item_variables');
      $table->addColumn('id', 'bigint', array('autoincrement' => true));
      $table->addColumn('item_id', 'bigint', array(
          'precision' => 10,
          'notNull' => false,
      ));
      $table->addColumn('name', 'string', array('length' => 255));
      $table->addColumn('value', 'text', array());
      $table->addIndex(array('item_id'), 'IDX_29E7EC20126F525E');
      $table->setPrimaryKey(array('id'), 'primary');
      $table->addUniqueIndex(array(
          'item_id',
          'name',
      ), 'elementlistitemvariable_name');
      $table->addForeignKeyConstraint('element_list_items', array('item_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_29E7EC20126F525E');
        
    }

    public function down(\Doctrine\DBAL\Schema\Schema  $schema)
    {
      $schema->dropTable('element_list_item_variables');
    }
}
