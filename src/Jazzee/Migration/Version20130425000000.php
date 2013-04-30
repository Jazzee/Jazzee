<?php

namespace Jazzee\Migration;

/**
 * Migrate DB for PDF Templates
 */
class Version20130425000000 extends \Doctrine\DBAL\Migrations\AbstractMigration
{
    public function up(\Doctrine\DBAL\Schema\Schema $schema)
    {
        // Create table: pdf_templates
        $table = $schema->createTable('pdf_templates');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('application_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('title', 'string', array('length' => 200));
        $table->addColumn('fileHash', 'string', array('length' => 128));
        $table->addColumn('blocks', 'array', array());
        $table->addIndex(array('application_id'), 'IDX_E78B85EF3E030ACD');
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addForeignKeyConstraint('applications', array('application_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_E78B85EF3E030ACD');
        
    }

    public function down(\Doctrine\DBAL\Schema\Schema $schema)
    {
        // Drop table: pdf_templates
        $schema->dropTable('pdf_templates');
        
    }
}
