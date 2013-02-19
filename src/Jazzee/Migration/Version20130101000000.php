<?php
namespace Jazzee\Migration;

/**
 * Initial DB Setup moved into a migration and pre-dated to 1/1/2013
 */
class Version20130101000000 extends \Doctrine\DBAL\Migrations\AbstractMigration
{
    public function up(\Doctrine\DBAL\Schema\Schema $schema)
    {
        $table = $schema->createTable('answers');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('applicant_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('page_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('parent_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('pageStatus', 'integer', array('notNull' => false));
        $table->addColumn('uniqueId', 'string', array('length' => 255));
        $table->addColumn('locked', 'boolean', array());
        $table->addColumn('updatedAt', 'datetime', array());
        $table->addColumn('publicStatus_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('privateStatus_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('greScore_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('toeflScore_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addIndex(array('applicant_id'), 'IDX_50D0C60697139001');
        $table->addIndex(array('page_id'), 'IDX_50D0C606C4663E4');
        $table->addIndex(array('parent_id'), 'IDX_50D0C606727ACA70');
        $table->addIndex(array('publicStatus_id'), 'IDX_50D0C606E2F2CB81');
        $table->addIndex(array('privateStatus_id'), 'IDX_50D0C606605018D3');
        $table->addIndex(array('greScore_id'), 'IDX_50D0C606789F1477');
        $table->addIndex(array('toeflScore_id'), 'IDX_50D0C606E66D287A');
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addUniqueIndex(array('uniqueId'), 'answer_uniqueId');
        $table->addForeignKeyConstraint('applicants', array('applicant_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_50D0C60697139001');
        $table->addForeignKeyConstraint('pages', array('page_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_50D0C606C4663E4');
        $table->addForeignKeyConstraint('answers', array('parent_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_50D0C606727ACA70');
        $table->addForeignKeyConstraint('answer_status_types', array('publicStatus_id'), array('id'), array(), 'FK_50D0C606E2F2CB81');
        $table->addForeignKeyConstraint('answer_status_types', array('privateStatus_id'), array('id'), array(), 'FK_50D0C606605018D3');
        $table->addForeignKeyConstraint('gre_scores', array('greScore_id'), array('id'), array(), 'FK_50D0C606789F1477');
        $table->addForeignKeyConstraint('toefl_scores', array('toeflScore_id'), array('id'), array(), 'FK_50D0C606E66D287A');
        
        // Create table: answer_status_types
        $table = $schema->createTable('answer_status_types');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('name', 'string', array('length' => 255));
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addUniqueIndex(array('name'), 'answerstatustype_name');
        
        // Create table: applicants
        $table = $schema->createTable('applicants');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('application_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('uniqueId', 'string', array(
            'length' => 255,
            'notNull' => false,
        ));
        $table->addColumn('email', 'string', array('length' => 255));
        $table->addColumn('password', 'string', array('length' => 255));
        $table->addColumn('isLocked', 'boolean', array());
        $table->addColumn('firstName', 'string', array('length' => 255));
        $table->addColumn('middleName', 'string', array(
            'length' => 255,
            'notNull' => false,
        ));
        $table->addColumn('lastName', 'string', array('length' => 255));
        $table->addColumn('suffix', 'string', array(
            'length' => 255,
            'notNull' => false,
        ));
        $table->addColumn('deadlineExtension', 'datetime', array('notNull' => false));
        $table->addColumn('lastLogin', 'datetime', array('notNull' => false));
        $table->addColumn('lastLoginIp', 'string', array(
            'length' => 15,
            'notNull' => false,
        ));
        $table->addColumn('lastFailedLoginIp', 'string', array(
            'length' => 15,
            'notNull' => false,
        ));
        $table->addColumn('failedLoginAttempts', 'integer', array('notNull' => false));
        $table->addColumn('createdAt', 'datetime', array('notNull' => false));
        $table->addColumn('updatedAt', 'datetime', array('notNull' => false));
        $table->addColumn('percentComplete', 'float', array());
        $table->addColumn('hasPaid', 'boolean', array());
        $table->addColumn('deactivated', 'boolean', array());
        $table->addIndex(array('application_id'), 'IDX_7FAFCADB3E030ACD');
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addIndex(array('email'), 'applicant_email');
        $table->addUniqueIndex(array(
            'application_id',
            'email',
        ), 'application_email');
        $table->addUniqueIndex(array('uniqueId'), 'applicant_uniqueId');
        $table->addForeignKeyConstraint('applications', array('application_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_7FAFCADB3E030ACD');
        
        // Create table: applicant_tags
        $table = $schema->createTable('applicant_tags');
        $table->addColumn('applicant_id', 'bigint', array('precision' => 10));
        $table->addColumn('tag_id', 'bigint', array('precision' => 10));
        $table->addIndex(array('applicant_id'), 'IDX_EFC5B7A697139001');
        $table->addIndex(array('tag_id'), 'IDX_EFC5B7A6BAD26311');
        $table->setPrimaryKey(array(
            'applicant_id',
            'tag_id',
        ), 'primary');
        $table->addForeignKeyConstraint('applicants', array('applicant_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_EFC5B7A697139001');
        $table->addForeignKeyConstraint('tags', array('tag_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_EFC5B7A6BAD26311');
        
        // Create table: applications
        $table = $schema->createTable('applications');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('program_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('cycle_id', 'integer', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('contactName', 'string', array(
            'length' => 255,
            'notNull' => false,
        ));
        $table->addColumn('contactEmail', 'string', array(
            'length' => 255,
            'notNull' => false,
        ));
        $table->addColumn('welcome', 'text', array('notNull' => false));
        $table->addColumn('open', 'datetime', array('notNull' => false));
        $table->addColumn('close', 'datetime', array('notNull' => false));
        $table->addColumn('begin', 'datetime', array('notNull' => false));
        $table->addColumn('published', 'boolean', array());
        $table->addColumn('visible', 'boolean', array());
        $table->addColumn('admitLetter', 'text', array('notNull' => false));
        $table->addColumn('denyLetter', 'text', array('notNull' => false));
        $table->addColumn('statusIncompleteText', 'text', array('notNull' => false));
        $table->addColumn('statusNoDecisionText', 'text', array('notNull' => false));
        $table->addColumn('statusAdmitText', 'text', array('notNull' => false));
        $table->addColumn('statusDenyText', 'text', array('notNull' => false));
        $table->addColumn('statusAcceptText', 'text', array('notNull' => false));
        $table->addColumn('statusDeclineText', 'text', array('notNull' => false));
        $table->addColumn('statusDeactivatedText', 'text', array('notNull' => false));
        $table->addIndex(array('program_id'), 'IDX_F7C966F03EB8070A');
        $table->addIndex(array('cycle_id'), 'IDX_F7C966F05EC1162');
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addUniqueIndex(array(
            'program_id',
            'cycle_id',
        ), 'program_cycle');
        $table->addForeignKeyConstraint('programs', array('program_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_F7C966F03EB8070A');
        $table->addForeignKeyConstraint('cycles', array('cycle_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_F7C966F05EC1162');
        
        // Create table: application_pages
        $table = $schema->createTable('application_pages');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('application_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('page_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('weight', 'integer', array());
        $table->addColumn('kind', 'integer', array());
        $table->addColumn('title', 'string', array(
            'length' => 255,
            'notNull' => false,
        ));
        $table->addColumn('name', 'string', array(
            'length' => 255,
            'notNull' => false,
        ));
        $table->addColumn('min', 'integer', array('notNull' => false));
        $table->addColumn('max', 'integer', array('notNull' => false));
        $table->addColumn('isRequired', 'boolean', array('notNull' => false));
        $table->addColumn('answerStatusDisplay', 'boolean', array('notNull' => false));
        $table->addColumn('instructions', 'text', array('notNull' => false));
        $table->addColumn('leadingText', 'text', array('notNull' => false));
        $table->addColumn('trailingText', 'text', array('notNull' => false));
        $table->addIndex(array('application_id'), 'IDX_C3E0020F3E030ACD');
        $table->addIndex(array('page_id'), 'IDX_C3E0020FC4663E4');
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addUniqueIndex(array(
            'application_id',
            'page_id',
        ), 'application_page');
        $table->addUniqueIndex(array(
            'application_id',
            'name',
        ), 'applicationpage_name');
        $table->addForeignKeyConstraint('applications', array('application_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_C3E0020F3E030ACD');
        $table->addForeignKeyConstraint('pages', array('page_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_C3E0020FC4663E4');
        
        // Create table: attachments
        $table = $schema->createTable('attachments');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('answer_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('applicant_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('attachment', 'text', array());
        $table->addColumn('thumbnail', 'text', array('notNull' => false));
        $table->addUniqueIndex(array('answer_id'), 'UNIQ_47C4FAD6AA334807');
        $table->addIndex(array('applicant_id'), 'IDX_47C4FAD697139001');
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addForeignKeyConstraint('answers', array('answer_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_47C4FAD6AA334807');
        $table->addForeignKeyConstraint('applicants', array('applicant_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_47C4FAD697139001');
        
        // Create table: audit_log
        $table = $schema->createTable('audit_log');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('user_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('applicant_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('createdAt', 'datetime', array());
        $table->addColumn('text', 'text', array());
        $table->addIndex(array('user_id'), 'IDX_F6E1C0F5A76ED395');
        $table->addIndex(array('applicant_id'), 'IDX_F6E1C0F597139001');
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addForeignKeyConstraint('users', array('user_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_F6E1C0F5A76ED395');
        $table->addForeignKeyConstraint('applicants', array('applicant_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_F6E1C0F597139001');
        
        // Create table: cron_variables
        $table = $schema->createTable('cron_variables');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('name', 'string', array('length' => 255));
        $table->addColumn('value', 'text', array());
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addUniqueIndex(array('name'), 'cronvariable_name');
        
        // Create table: cycles
        $table = $schema->createTable('cycles');
        $table->addColumn('id', 'integer', array('autoincrement' => true));
        $table->addColumn('name', 'string', array('length' => 32));
        $table->addColumn('start', 'datetime', array('notNull' => false));
        $table->addColumn('end', 'datetime', array('notNull' => false));
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addUniqueIndex(array('name'), 'cycle_name_unique');
        
        // Create table: cycle_page
        $table = $schema->createTable('cycle_page');
        $table->addColumn('cycle_id', 'integer', array('precision' => 10));
        $table->addColumn('page_id', 'bigint', array('precision' => 10));
        $table->addIndex(array('cycle_id'), 'IDX_401C8C8D5EC1162');
        $table->addIndex(array('page_id'), 'IDX_401C8C8DC4663E4');
        $table->setPrimaryKey(array(
            'cycle_id',
            'page_id',
        ), 'primary');
        $table->addForeignKeyConstraint('cycles', array('cycle_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_401C8C8D5EC1162');
        $table->addForeignKeyConstraint('pages', array('page_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_401C8C8DC4663E4');
        
        // Create table: decisions
        $table = $schema->createTable('decisions');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('applicant_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('nominateAdmit', 'datetime', array('notNull' => false));
        $table->addColumn('nominateDeny', 'datetime', array('notNull' => false));
        $table->addColumn('finalAdmit', 'datetime', array('notNull' => false));
        $table->addColumn('finalDeny', 'datetime', array('notNull' => false));
        $table->addColumn('offerResponseDeadline', 'datetime', array('notNull' => false));
        $table->addColumn('decisionViewed', 'datetime', array('notNull' => false));
        $table->addColumn('acceptOffer', 'datetime', array('notNull' => false));
        $table->addColumn('declineOffer', 'datetime', array('notNull' => false));
        $table->addUniqueIndex(array('applicant_id'), 'UNIQ_638DAA1797139001');
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addForeignKeyConstraint('applicants', array('applicant_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_638DAA1797139001');
        
        // Create table: displays
        $table = $schema->createTable('displays');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('user_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('application_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('attributes', 'array', array());
        $table->addColumn('name', 'string', array('length' => 255));
        $table->addColumn('isFirstNameDislayed', 'boolean', array());
        $table->addColumn('isLastNameDislayed', 'boolean', array());
        $table->addColumn('isEmailDislayed', 'boolean', array());
        $table->addColumn('isCreatedAtDislayed', 'boolean', array());
        $table->addColumn('isUpdatedAtDislayed', 'boolean', array());
        $table->addColumn('isLastLoginDislayed', 'boolean', array());
        $table->addColumn('isPercentCompleteDislayed', 'boolean', array());
        $table->addColumn('isHasPaidDislayed', 'boolean', array());
        $table->addIndex(array('user_id'), 'IDX_54DDEC2BA76ED395');
        $table->addIndex(array('application_id'), 'IDX_54DDEC2B3E030ACD');
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addForeignKeyConstraint('users', array('user_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_54DDEC2BA76ED395');
        $table->addForeignKeyConstraint('applications', array('application_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_54DDEC2B3E030ACD');
        
        // Create table: display_elements
        $table = $schema->createTable('display_elements');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('page_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('element_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('attributes', 'array', array());
        $table->addIndex(array('page_id'), 'IDX_23A0A273C4663E4');
        $table->addIndex(array('element_id'), 'IDX_23A0A2731F1F2A24');
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addForeignKeyConstraint('display_pages', array('page_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_23A0A273C4663E4');
        $table->addForeignKeyConstraint('elements', array('element_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_23A0A2731F1F2A24');
        
        // Create table: display_pages
        $table = $schema->createTable('display_pages');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('display_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('attributes', 'array', array());
        $table->addColumn('applicationPage_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addIndex(array('display_id'), 'IDX_2124FE9E51A2DF33');
        $table->addIndex(array('applicationPage_id'), 'IDX_2124FE9E8AFBB13E');
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addForeignKeyConstraint('displays', array('display_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_2124FE9E51A2DF33');
        $table->addForeignKeyConstraint('application_pages', array('applicationPage_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_2124FE9E8AFBB13E');
        
        // Create table: duplicates
        $table = $schema->createTable('duplicates');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('applicant_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('duplicate_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('isIgnored', 'boolean', array());
        $table->addIndex(array('applicant_id'), 'IDX_9179416697139001');
        $table->addIndex(array('duplicate_id'), 'IDX_91794166BC12F48A');
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addUniqueIndex(array(
            'applicant_id',
            'duplicate_id',
        ), 'duplicate_applicant');
        $table->addForeignKeyConstraint('applicants', array('applicant_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_9179416697139001');
        $table->addForeignKeyConstraint('applicants', array('duplicate_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_91794166BC12F48A');
        
        // Create table: elements
        $table = $schema->createTable('elements');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('type_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('page_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('weight', 'integer', array());
        $table->addColumn('fixedId', 'integer', array('notNull' => false));
        $table->addColumn('title', 'string', array('length' => 255));
        $table->addColumn('name', 'string', array(
            'length' => 255,
            'notNull' => false,
        ));
        $table->addColumn('format', 'string', array(
            'length' => 255,
            'notNull' => false,
        ));
        $table->addColumn('min', 'decimal', array('notNull' => false));
        $table->addColumn('max', 'decimal', array('notNull' => false));
        $table->addColumn('required', 'boolean', array());
        $table->addColumn('instructions', 'text', array('notNull' => false));
        $table->addColumn('defaultValue', 'string', array(
            'length' => 255,
            'notNull' => false,
        ));
        $table->addIndex(array('type_id'), 'IDX_444A075DC54C8C93');
        $table->addIndex(array('page_id'), 'IDX_444A075DC4663E4');
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addUniqueIndex(array(
            'page_id',
            'fixedId',
        ), 'element_fixedId');
        $table->addUniqueIndex(array(
            'page_id',
            'name',
        ), 'element_name');
        $table->addForeignKeyConstraint('element_types', array('type_id'), array('id'), array(), 'FK_444A075DC54C8C93');
        $table->addForeignKeyConstraint('pages', array('page_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_444A075DC4663E4');
        
        // Create table: element_answers
        $table = $schema->createTable('element_answers');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('answer_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('element_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('position', 'integer', array('notNull' => false));
        $table->addColumn('eShortString', 'string', array(
            'length' => 255,
            'notNull' => false,
        ));
        $table->addColumn('eText', 'text', array('notNull' => false));
        $table->addColumn('eDate', 'datetime', array('notNull' => false));
        $table->addColumn('eInteger', 'integer', array('notNull' => false));
        $table->addColumn('eDecimal', 'decimal', array('notNull' => false));
        $table->addColumn('eBlob', 'text', array('notNull' => false));
        $table->addIndex(array('answer_id'), 'IDX_A73B8652AA334807');
        $table->addIndex(array('element_id'), 'IDX_A73B86521F1F2A24');
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addForeignKeyConstraint('answers', array('answer_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_A73B8652AA334807');
        $table->addForeignKeyConstraint('elements', array('element_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_A73B86521F1F2A24');
        
        // Create table: element_list_items
        $table = $schema->createTable('element_list_items');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('element_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('weight', 'integer', array());
        $table->addColumn('active', 'boolean', array());
        $table->addColumn('value', 'string', array('length' => 255));
        $table->addColumn('name', 'string', array(
            'length' => 255,
            'notNull' => false,
        ));
        $table->addIndex(array('element_id'), 'IDX_94998F201F1F2A24');
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addUniqueIndex(array(
            'element_id',
            'name',
        ), 'item_name');
        $table->addForeignKeyConstraint('elements', array('element_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_94998F201F1F2A24');
        
        // Create table: element_types
        $table = $schema->createTable('element_types');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('name', 'string', array('length' => 255));
        $table->addColumn('class', 'string', array('length' => 255));
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addUniqueIndex(array('class'), 'elementtype_class');
        $table->addUniqueIndex(array('name'), 'elementtype_name');
        
        // Create table: gre_scores
        $table = $schema->createTable('gre_scores');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('registrationNumber', 'bigint', array());
        $table->addColumn('testMonth', 'integer', array());
        $table->addColumn('testYear', 'integer', array());
        $table->addColumn('departmentCode', 'string', array(
            'length' => 4,
            'notNull' => false,
        ));
        $table->addColumn('departmentName', 'string', array(
            'length' => 255,
            'notNull' => false,
        ));
        $table->addColumn('firstName', 'string', array('length' => 255));
        $table->addColumn('middleInitial', 'string', array(
            'length' => 1,
            'notNull' => false,
        ));
        $table->addColumn('lastName', 'string', array('length' => 255));
        $table->addColumn('birthDate', 'datetime', array());
        $table->addColumn('gender', 'string', array(
            'length' => 1,
            'notNull' => false,
        ));
        $table->addColumn('testDate', 'datetime', array());
        $table->addColumn('testCode', 'string', array('length' => 2));
        $table->addColumn('testName', 'string', array('length' => 255));
        $table->addColumn('score1Type', 'string', array('length' => 1));
        $table->addColumn('score1Converted', 'integer', array('length' => 3));
        $table->addColumn('score1Percentile', 'decimal', array('length' => 3));
        $table->addColumn('score2Type', 'string', array(
            'length' => 1,
            'notNull' => false,
        ));
        $table->addColumn('score2Converted', 'integer', array(
            'length' => 3,
            'notNull' => false,
        ));
        $table->addColumn('score2Percentile', 'decimal', array(
            'length' => 3,
            'notNull' => false,
        ));
        $table->addColumn('score3Type', 'string', array(
            'length' => 1,
            'notNull' => false,
        ));
        $table->addColumn('score3Converted', 'integer', array(
            'length' => 3,
            'notNull' => false,
        ));
        $table->addColumn('score3Percentile', 'decimal', array(
            'length' => 3,
            'notNull' => false,
        ));
        $table->addColumn('score4Type', 'string', array(
            'length' => 1,
            'notNull' => false,
        ));
        $table->addColumn('score4Converted', 'integer', array(
            'length' => 3,
            'notNull' => false,
        ));
        $table->addColumn('score4Percentile', 'decimal', array(
            'length' => 3,
            'notNull' => false,
        ));
        $table->addColumn('sequenceNumber', 'integer', array('length' => 4));
        $table->addColumn('recordSerialNumber', 'integer', array('length' => 2));
        $table->addColumn('cycleNumber', 'integer', array('length' => 4));
        $table->addColumn('processDate', 'datetime', array());
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addUniqueIndex(array(
            'registrationNumber',
            'testMonth',
            'testYear',
        ), 'gre_registration');
        
        // Create table: messages
        $table = $schema->createTable('messages');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('thread_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('sender', 'integer', array());
        $table->addColumn('text', 'text', array());
        $table->addColumn('createdAt', 'datetime', array());
        $table->addColumn('isRead', 'boolean', array());
        $table->addIndex(array('thread_id'), 'IDX_DB021E96E2904019');
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addForeignKeyConstraint('threads', array('thread_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_DB021E96E2904019');
        
        // Create table: pages
        $table = $schema->createTable('pages');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('type_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('parent_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('uuid', 'string', array('length' => 255));
        $table->addColumn('title', 'string', array('length' => 255));
        $table->addColumn('isGlobal', 'boolean', array());
        $table->addColumn('min', 'integer', array('notNull' => false));
        $table->addColumn('max', 'integer', array('notNull' => false));
        $table->addColumn('isRequired', 'boolean', array());
        $table->addColumn('answerStatusDisplay', 'boolean', array());
        $table->addColumn('instructions', 'text', array('notNull' => false));
        $table->addColumn('leadingText', 'text', array('notNull' => false));
        $table->addColumn('trailingText', 'text', array('notNull' => false));
        $table->addIndex(array('type_id'), 'IDX_2074E575C54C8C93');
        $table->addIndex(array('parent_id'), 'IDX_2074E575727ACA70');
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addUniqueIndex(array('uuid'), 'page_uuid');
        $table->addForeignKeyConstraint('page_types', array('type_id'), array('id'), array(), 'FK_2074E575C54C8C93');
        $table->addForeignKeyConstraint('pages', array('parent_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_2074E575727ACA70');
        
        // Create table: page_types
        $table = $schema->createTable('page_types');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('name', 'string', array('length' => 255));
        $table->addColumn('class', 'string', array('length' => 255));
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addUniqueIndex(array('class'), 'pagetype_class');
        $table->addUniqueIndex(array('name'), 'pagetype_name');
        
        // Create table: page_variables
        $table = $schema->createTable('page_variables');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('page_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('name', 'string', array('length' => 255));
        $table->addColumn('value', 'text', array());
        $table->addIndex(array('page_id'), 'IDX_B325C2DEC4663E4');
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addUniqueIndex(array(
            'page_id',
            'name',
        ), 'pagevariable_name');
        $table->addForeignKeyConstraint('pages', array('page_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_B325C2DEC4663E4');
        
        // Create table: payments
        $table = $schema->createTable('payments');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('answer_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('type_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('amount', 'decimal', array());
        $table->addColumn('status', 'string', array('length' => 255));
        $table->addUniqueIndex(array('answer_id'), 'UNIQ_65D29B32AA334807');
        $table->addIndex(array('type_id'), 'IDX_65D29B32C54C8C93');
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addForeignKeyConstraint('answers', array('answer_id'), array('id'), array('onDelete' => 'SET NULL'), 'FK_65D29B32AA334807');
        $table->addForeignKeyConstraint('payment_types', array('type_id'), array('id'), array(), 'FK_65D29B32C54C8C93');
        
        // Create table: payment_types
        $table = $schema->createTable('payment_types');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('name', 'string', array('length' => 255));
        $table->addColumn('class', 'string', array('length' => 255));
        $table->addColumn('isExpired', 'boolean', array());
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addUniqueIndex(array('class'), 'payemnttype_class');
        $table->addUniqueIndex(array('name'), 'paymenttype_name');
        
        // Create table: payment_type_variables
        $table = $schema->createTable('payment_type_variables');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('type_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('name', 'string', array('length' => 255));
        $table->addColumn('value', 'string', array('length' => 255));
        $table->addIndex(array('type_id'), 'IDX_D9258605C54C8C93');
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addUniqueIndex(array(
            'type_id',
            'name',
        ), 'payment_type_variable_name');
        $table->addForeignKeyConstraint('payment_types', array('type_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_D9258605C54C8C93');
        
        // Create table: payment_variables
        $table = $schema->createTable('payment_variables');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('payment_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('name', 'string', array('length' => 255));
        $table->addColumn('value', 'text', array());
        $table->addIndex(array('payment_id'), 'IDX_CBE252394C3A3BB');
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addUniqueIndex(array(
            'payment_id',
            'name',
        ), 'payment_variable_name');
        $table->addForeignKeyConstraint('payments', array('payment_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_CBE252394C3A3BB');
        
        // Create table: programs
        $table = $schema->createTable('programs');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('name', 'string', array('length' => 255));
        $table->addColumn('shortName', 'string', array('length' => 32));
        $table->addColumn('isExpired', 'boolean', array());
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addUniqueIndex(array('name'), 'program_name');
        $table->addUniqueIndex(array('shortName'), 'program_shortname');
        
        // Create table: roles
        $table = $schema->createTable('roles');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('program_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('name', 'string', array('length' => 255));
        $table->addColumn('isGlobal', 'boolean', array());
        $table->addIndex(array('program_id'), 'IDX_B63E2EC73EB8070A');
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addForeignKeyConstraint('programs', array('program_id'), array('id'), array(), 'FK_B63E2EC73EB8070A');
        
        // Create table: role_actions
        $table = $schema->createTable('role_actions');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('role_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('controller', 'string', array('length' => 255));
        $table->addColumn('action', 'string', array('length' => 255));
        $table->addIndex(array('role_id'), 'IDX_B9855414D60322AC');
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addForeignKeyConstraint('roles', array('role_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_B9855414D60322AC');
        
        // Create table: tags
        $table = $schema->createTable('tags');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('title', 'string', array('length' => 255));
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addUniqueIndex(array('title'), 'tag_title');
        
        // Create table: threads
        $table = $schema->createTable('threads');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('applicant_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('subject', 'string', array('length' => 255));
        $table->addColumn('createdAt', 'datetime', array());
        $table->addIndex(array('applicant_id'), 'IDX_6F8E3DDD97139001');
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addForeignKeyConstraint('applicants', array('applicant_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_6F8E3DDD97139001');
        
        // Create table: toefl_scores
        $table = $schema->createTable('toefl_scores');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('registrationNumber', 'bigint', array());
        $table->addColumn('testMonth', 'integer', array());
        $table->addColumn('testYear', 'integer', array());
        $table->addColumn('departmentCode', 'string', array(
            'length' => 4,
            'notNull' => false,
        ));
        $table->addColumn('firstName', 'string', array(
            'length' => 255,
            'notNull' => false,
        ));
        $table->addColumn('middleName', 'string', array(
            'length' => 255,
            'notNull' => false,
        ));
        $table->addColumn('lastName', 'string', array('length' => 255));
        $table->addColumn('birthDate', 'datetime', array());
        $table->addColumn('gender', 'string', array(
            'length' => 1,
            'notNull' => false,
        ));
        $table->addColumn('nativeCountry', 'string', array('length' => 255));
        $table->addColumn('nativeLanguage', 'string', array('length' => 255));
        $table->addColumn('testDate', 'datetime', array());
        $table->addColumn('testType', 'string', array('length' => 255));
        $table->addColumn('listeningIndicator', 'integer', array(
            'length' => 1,
            'notNull' => false,
        ));
        $table->addColumn('speakingIndicator', 'integer', array(
            'length' => 1,
            'notNull' => false,
        ));
        $table->addColumn('IBTListening', 'integer', array(
            'length' => 2,
            'notNull' => false,
        ));
        $table->addColumn('IBTWriting', 'integer', array(
            'length' => 2,
            'notNull' => false,
        ));
        $table->addColumn('IBTSpeaking', 'integer', array(
            'length' => 2,
            'notNull' => false,
        ));
        $table->addColumn('IBTReading', 'integer', array(
            'length' => 2,
            'notNull' => false,
        ));
        $table->addColumn('IBTTotal', 'integer', array(
            'length' => 3,
            'notNull' => false,
        ));
        $table->addColumn('TSEScore', 'integer', array(
            'length' => 2,
            'notNull' => false,
        ));
        $table->addColumn('listening', 'integer', array(
            'length' => 2,
            'notNull' => false,
        ));
        $table->addColumn('writing', 'integer', array(
            'length' => 2,
            'notNull' => false,
        ));
        $table->addColumn('reading', 'integer', array(
            'length' => 2,
            'notNull' => false,
        ));
        $table->addColumn('essay', 'integer', array(
            'length' => 2,
            'notNull' => false,
        ));
        $table->addColumn('total', 'integer', array(
            'length' => 3,
            'notNull' => false,
        ));
        $table->addColumn('timesTaken', 'integer', array(
            'length' => 3,
            'notNull' => false,
        ));
        $table->addColumn('offTopic', 'string', array(
            'length' => 1,
            'notNull' => false,
        ));
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addUniqueIndex(array(
            'registrationNumber',
            'testMonth',
            'testYear',
        ), 'toefl_registration');
        
        // Create table: users
        $table = $schema->createTable('users');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('uniqueName', 'string', array('length' => 255));
        $table->addColumn('email', 'string', array(
            'length' => 255,
            'notNull' => false,
        ));
        $table->addColumn('firstName', 'string', array(
            'length' => 255,
            'notNull' => false,
        ));
        $table->addColumn('lastName', 'string', array(
            'length' => 255,
            'notNull' => false,
        ));
        $table->addColumn('apiKey', 'string', array(
            'length' => 255,
            'notNull' => false,
        ));
        $table->addColumn('isActive', 'boolean', array());
        $table->addColumn('preferences', 'array', array('notNull' => false));
        $table->addColumn('defaultProgram_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addColumn('defaultCycle_id', 'integer', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addIndex(array('defaultProgram_id'), 'IDX_1483A5E9AD1B8D60');
        $table->addIndex(array('defaultCycle_id'), 'IDX_1483A5E97A57C30C');
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addUniqueIndex(array('uniqueName'), 'user_name');
        $table->addForeignKeyConstraint('programs', array('defaultProgram_id'), array('id'), array('onDelete' => 'SET NULL'), 'FK_1483A5E9AD1B8D60');
        $table->addForeignKeyConstraint('cycles', array('defaultCycle_id'), array('id'), array('onDelete' => 'SET NULL'), 'FK_1483A5E97A57C30C');
        
        // Create table: user_roles
        $table = $schema->createTable('user_roles');
        $table->addColumn('user_id', 'bigint', array('precision' => 10));
        $table->addColumn('role_id', 'bigint', array('precision' => 10));
        $table->addIndex(array('user_id'), 'IDX_54FCD59FA76ED395');
        $table->addIndex(array('role_id'), 'IDX_54FCD59FD60322AC');
        $table->setPrimaryKey(array(
            'user_id',
            'role_id',
        ), 'primary');
        $table->addForeignKeyConstraint('users', array('user_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_54FCD59FA76ED395');
        $table->addForeignKeyConstraint('roles', array('role_id'), array('id'), array('onDelete' => 'CASCADE'), 'FK_54FCD59FD60322AC');
        
        // Create table: virtual_files
        $table = $schema->createTable('virtual_files');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('name', 'string', array('length' => 255));
        $table->addColumn('contents', 'text', array());
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addUniqueIndex(array('name'), 'virtualfile_name');
        
    }

    public function down(\Doctrine\DBAL\Schema\Schema $schema)
    {
      throw new \Doctrine\DBAL\Migrations\IrreversibleMigrationException();
    }
}
