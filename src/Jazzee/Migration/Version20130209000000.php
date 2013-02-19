<?php
namespace Jazzee\Migration;

/**
 * Migration for display manager improvments 
 * (aa72204bc127841e211232403994a2aba51407b3 - 532dff32f465160b7b215f55e04d36eb71f932c3)
 */
class Version20130209000000 extends \Doctrine\DBAL\Migrations\AbstractMigration
{
    public function up(\Doctrine\DBAL\Schema\Schema $schema)
    {   // Drop table: display_pages
        $schema->dropTable('display_pages');
        
        // Alter table: displays
        $table = $schema->getTable('displays');
        $table->dropColumn('attributes');
        $table->dropColumn('isfirstnamedislayed');
        $table->dropColumn('islastnamedislayed');
        $table->dropColumn('isemaildislayed');
        $table->dropColumn('iscreatedatdislayed');
        $table->dropColumn('isupdatedatdislayed');
        $table->dropColumn('islastlogindislayed');
        $table->dropColumn('ispercentcompletedislayed');
        $table->dropColumn('ishaspaiddislayed');
        
        // Alter table: display_elements
        $table = $schema->getTable('display_elements');
        $table->addColumn('type', 'string', array('length' => 255));
        $table->addColumn('title', 'string', array('length' => 255));
        $table->addColumn('name', 'string', array(
            'length' => 255,
            'notNull' => false,
        ));
        $table->addColumn('weight', 'integer', array());
        $table->dropColumn('attributes');
        $table->dropColumn('page_id');
        $table->addColumn('display_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addIndex(array('display_id'), 'IDX_23A0A27351A2DF33');
        $table->dropIndex('idx_23a0a273c4663e4');
        $table->addForeignKeyConstraint('displays', array('display_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_23A0A27351A2DF33');
        
    }

    public function down(\Doctrine\DBAL\Schema\Schema $schema)
    {
        // Create table: display_pages
        $table = $schema->createTable('display_pages');
        $table->addColumn('id', 'bigint', array(
            'precision' => 10,
            'autoincrement' => true,
            'comment' => '',
        ));
        $table->addColumn('display_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
            'comment' => '',
        ));
        $table->addColumn('attributes', 'array', array(
            'precision' => 10,
            'comment' => '',
        ));
        $table->addColumn('applicationPage_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
            'comment' => '',
        ));
        $table->setPrimaryKey(array('id'), 'PRIMARY');
        $table->addIndex(array('display_id'), 'IDX_2124FE9E51A2DF33');
        $table->addIndex(array('applicationPage_id'), 'IDX_2124FE9E8AFBB13E');
        $table->addForeignKeyConstraint('application_pages', array('applicationPage_id'), array('id'), array(
            'onDelete' => 'CASCADE',
            'onUpdate' => NULL,
        ), 'FK_2124FE9E8AFBB13E');
        $table->addForeignKeyConstraint('displays', array('display_id'), array('id'), array(
            'onDelete' => 'CASCADE',
            'onUpdate' => NULL,
        ), 'FK_2124FE9E51A2DF33');
        
        // Alter table: display_elements
        $table = $schema->getTable('display_elements');
        $table->addColumn('attributes', 'array', array(
            'precision' => 10,
            'comment' => '',
        ));
        $table->dropColumn('type');
        $table->dropColumn('title');
        $table->dropColumn('name');
        $table->dropColumn('weight');
        $table->dropColumn('display_id');
        $table->addColumn('page_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
            'comment' => '',
        ));
        $table->addIndex(array('page_id'), 'IDX_23A0A273C4663E4');
        $table->dropIndex('idx_23a0a27351a2df33');
        $table->addForeignKeyConstraint('display_pages', array('page_id'), array('id'), array(
            'onDelete' => 'CASCADE',
            'onUpdate' => NULL,
        ), 'FK_23A0A273C4663E4');
        $table->removeForeignKey('FK_23A0A27351A2DF33');
        
        // Alter table: displays
        $table = $schema->getTable('displays');
        $table->addColumn('attributes', 'array', array(
            'precision' => 10,
            'comment' => '',
        ));
        $table->addColumn('isFirstNameDislayed', 'boolean', array(
            'precision' => 10,
            'comment' => '',
        ));
        $table->addColumn('isLastNameDislayed', 'boolean', array(
            'precision' => 10,
            'comment' => '',
        ));
        $table->addColumn('isEmailDislayed', 'boolean', array(
            'precision' => 10,
            'comment' => '',
        ));
        $table->addColumn('isCreatedAtDislayed', 'boolean', array(
            'precision' => 10,
            'comment' => '',
        ));
        $table->addColumn('isUpdatedAtDislayed', 'boolean', array(
            'precision' => 10,
            'comment' => '',
        ));
        $table->addColumn('isLastLoginDislayed', 'boolean', array(
            'precision' => 10,
            'comment' => '',
        ));
        $table->addColumn('isPercentCompleteDislayed', 'boolean', array(
            'precision' => 10,
            'comment' => '',
        ));
        $table->addColumn('isHasPaidDislayed', 'boolean', array(
            'precision' => 10,
            'comment' => '',
        ));
    }
}
