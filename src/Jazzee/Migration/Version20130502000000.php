<?php

namespace Jazzee\Migration;

/**
 * Migration to add the metadata coulumn to list items
 */
class Version20130502000000 extends \Doctrine\DBAL\Migrations\AbstractMigration
{
    public function up(\Doctrine\DBAL\Schema\Schema  $schema)
    {
        $table = $schema->getTable('element_list_items');
        $table->addColumn('metadata', 'array', array());
        
    }

    public function down(\Doctrine\DBAL\Schema\Schema  $schema)
    {
        $table = $schema->getTable('element_list_items');
        $table->dropColumn('metadata');   
    }
  
  public function postUp(\Doctrine\DBAL\Schema\Schema $schema) {
    parent::postUp($schema);
    $this->connection->executeQuery('UPDATE element_list_items SET metadata="a:0:{}" WHERE metadata=""');
  }
}
