<?php

namespace Jazzee\Migration;

/**
 * Migrate DB to page to display element and change al the 'page' to 'element' types
 */
class Version20130630000000 extends \Doctrine\DBAL\Migrations\AbstractMigration
{
  public function up(\Doctrine\DBAL\Schema\Schema $schema)
  {
    // Create table: schools
    $table = $schema->createTable('schools');
    $table->addColumn('id', 'bigint', array('autoincrement' => true));
    $table->addColumn('name', 'string', array('length' => 255));
    $table->addColumn('searchTerms', 'text', array('notNull' => false));
    $table->addColumn('state', 'string', array(
      'length' => 64,
      'notNull' => false,
    ));
    $table->addColumn('city', 'string', array(
      'length' => 64,
      'notNull' => false,
    ));
    $table->addColumn('postalCode', 'string', array(
      'length' => 64,
      'notNull' => false,
    ));
    $table->addColumn('country', 'string', array(
      'length' => 128,
      'notNull' => false,
    ));
    $table->addColumn('code', 'string', array('length' => 255));
    $table->addUniqueIndex(array('name'), 'UNIQ_47443BD55E237E06');
    $table->setPrimaryKey(array('id'), 'primary');

    // Alter table: answers
    $table = $schema->getTable('answers');
    $table->addColumn('school_id', 'bigint', array(
      'precision' => 10,
      'notNull' => false,
    ));
    $table->addIndex(array('school_id'), 'IDX_50D0C606C32A47EE');
    $table->addForeignKeyConstraint('schools', array('school_id'), array('id'), array(), 'FK_50D0C606C32A47EE');
  }
  
  /**
   * Migrate schools from the old education apge format to the new global school list
   * 
   * @param \Doctrine\DBAL\Schema\Schema $schema
   */
  public function postUp(\Doctrine\DBAL\Schema\Schema $schema)
  {
    parent::postUp($schema);
    $sql = 'SELECT li.* FROM element_list_items AS li ' .
      'WHERE li.element_id IN ' .
      '(SELECT id FROM elements WHERE page_id IN (SELECT id from pages where type_id=(SELECT id from page_types where class="\\\Jazzee\\\Page\\\Education")))';
    $checkSchool = $this->connection->prepare('SELECT id FROM schools WHERE name=?');
    $insertSchool = $this->connection->prepare('INSERT INTO schools (name, searchTerms, code) VALUES (?,?,?)');
    $rows = $this->connection->executeQuery($sql)->fetchAll();
    $fetchCode = $this->connection->prepare('SELECT value FROM element_list_item_variables WHERE item_id=? AND name="code"');
    $fetchSearchTerms = $this->connection->prepare('SELECT value FROM element_list_item_variables WHERE item_id=? AND name="searchTerms"');
    $total = count($rows);
    $newSchools = 0;
    foreach($rows as $row){
      $fetchCode->execute(array($row['id']));
      $code = $fetchCode->fetchColumn();
      $fetchSearchTerms->execute(array($row['id']));
      $searchTerms = $fetchSearchTerms->fetchColumn();
      $checkSchool->execute(array($row['value']));
      if($checkSchool->rowCount() == 0){
        $insertSchool->execute(array($row['value'], $searchTerms, $code));
        $newSchools++;
      }
    }
    $this->write("<info>Added {$newSchools} schools from {$total} old elements.</info>");
    $sql = 'UPDATE answers SET school_id = ' .
           '(SELECT id from schools WHERE name = ' .
           '(SELECT value FROM element_list_items WHERE id = ' .
           '(SELECT eInteger FROM element_answers WHERE answer_id = answers.id AND position = 0))) ' .
      'WHERE page_id IN (SELECT id from pages where type_id=(SELECT id from page_types where class="\\\Jazzee\\\Page\\\Education"))';
    $rows = $this->connection->executeUpdate($sql);
    $this->write("<info>Added School to answer for existing applicants.  Modified {$rows} answers</info>");
    $sql = 'DELETE FROM elements WHERE page_id IN ' .
      '(SELECT id from pages where type_id= ' .
      '(SELECT id from page_types where class="\\\Jazzee\\\Page\\\Education")) ' .
      'AND elements.fixedId = ' . 2;
    $rows = $this->connection->executeUpdate($sql);
    $this->write("<info>Removed the School List Element from Education pages.  Modified {$rows} rows</info>");
    $sql = 'UPDATE elements set page_id = (SELECT parent_id FROM pages WHERE id = page_id) WHERE page_id IN ' .
      '(SELECT id from pages WHERE parent_id IN ' .
      '(SELECT id from pages where type_id= ' .
      '(SELECT id from page_types where class="\\\Jazzee\\\Page\\\Education")) ' .
      'AND pages.fixedId = 2)';
    $rows = $this->connection->executeUpdate($sql);
    $this->write("<info>Moved all the Known School page elements to the Education page's new single page form.  Modified {$rows} rows</info>");
    $sql = 'UPDATE element_answers set answer_id = (SELECT parent_id from answers where id = element_answers.answer_id) ' .
      'WHERE answer_id IN ' .
      '(SELECT id from answers WHERE page_id IN ' .
      '(SELECT id from pages WHERE parent_id IN ' .
      '(SELECT id from pages where type_id= ' .
      '(SELECT id from page_types where class="\\\Jazzee\\\Page\\\Education")) AND pages.fixedId = 2))';
    $rows = $this->connection->executeUpdate($sql);
    $this->write("<info>Migrated Applicant Known school Answers to the Education page.  Modified {$rows} rows</info>");
    
    $sql = 'SELECT parent_id from answers WHERE page_id IN ' .
      '(SELECT id from pages WHERE parent_id IN ' .
      '(SELECT id from pages where type_id= ' .
      '(SELECT id from page_types where class="\\\Jazzee\\\Page\\\Education")) AND pages.fixedId = 4)';
    $rows = $this->connection->executeQuery($sql)->fetchAll();
    $fixAnswer = $this->connection->prepare('UPDATE answers set page_id=null WHERE id = ?');
    $fixChildAnswer = $this->connection->prepare('UPDATE answers set page_id=null WHERE parent_id = ?');
    foreach($rows as $row){
      $fixAnswer->execute(array($row['parent_id']));
      $fixChildAnswer->execute(array($row['parent_id']));
    }
    
    $this->write("<info>Dis-associated all the applicant New School Answers from the Education Page.  This will result in some applicant data loss and can be manually corrected in the DB.  Modified " . count($rows) . " answers.</info>");
    $sql = 'SELECT id from pages where type_id=(SELECT id from page_types where class="\\\Jazzee\\\Page\\\Education")';
    $rows = $this->connection->executeQuery($sql)->fetchAll();
    $insertPageVars = $this->connection->prepare('INSERT INTO page_variables (page_id, name, value) VALUES (?,?,?)');
    $insertNewSchoolPage = $this->connection->prepare('INSERT INTO pages (parent_id, title, fixedId, type_id) VALUES (?,?,?,(SELECT id from page_types WHERE class=?))');
    $deleteChildPages = $this->connection->prepare('DELETE FROM pages WHERE parent_id=?');
    $schoolListType = base64_encode('full');
    $partialSchoolList = base64_encode('');
    foreach($rows as $row){
      $insertPageVars->execute(array($row['id'], 'schoolListType', $schoolListType));
      $insertPageVars->execute(array($row['id'], 'partialSchoolList', $partialSchoolList));
      $deleteChildPages->execute(array($row['id']));
      $insertNewSchoolPage->execute(array($row['id'], 'New School', \Jazzee\Page\Education::PAGE_FID_NEWSCHOOL, '\Jazzee\Page\Standard'));
    }
    $this->write("<info>Removed existing child pages, created new school page, set page elements for existing educaiton pages.  Modified " . count($rows) . " pages.</info>");
    $sql = 'SELECT id from pages WHERE fixedId = 4 AND parent_id IN (SELECT id from pages where type_id=(SELECT id from page_types where class="\\\Jazzee\\\Page\\\Education"))';
    $rows = $this->connection->executeQuery($sql)->fetchAll();
    $insertPageElement = $this->connection->prepare('INSERT INTO elements (page_id, weight, fixedId, title, max, required, type_id) VALUES (?,?,?,?,?,?,(SELECT id from element_types WHERE class=?))');
    foreach($rows as $row){
      $tie = '\Jazzee\Element\TextInput';
      $count = 1;
      $insertPageElement->execute(array($row['id'], $count++, \Jazzee\Page\Education::ELEMENT_FID_NAME, 'School Name', 255, 1, $tie));
      $insertPageElement->execute(array($row['id'], $count++, \Jazzee\Page\Education::ELEMENT_FID_CITY, 'City', 64, 1, $tie));
      $insertPageElement->execute(array($row['id'], $count++, \Jazzee\Page\Education::ELEMENT_FID_STATE, 'State or Province', 64, 1, $tie));
      $insertPageElement->execute(array($row['id'], $count++, \Jazzee\Page\Education::ELEMENT_FID_COUNTRY, 'Country', 64, 1, $tie));
      $insertPageElement->execute(array($row['id'], $count++, \Jazzee\Page\Education::ELEMENT_FID_POSTALCODE, 'Postal Code', 10, 1, $tie));
    }
    $this->write("<info>Added elements to all new school pages.  For " . count($rows) . " pages.</info>");
  }

  public function down(\Doctrine\DBAL\Schema\Schema $schema)
  {
    // Alter table: answers
    $table = $schema->getTable('answers');
    $table->dropColumn('school_id');
    $table->dropIndex('idx_50d0c606c32a47ee');
    $table->removeForeignKey('FK_50D0C606C32A47EE');

    // Drop table: schools
    $schema->dropTable('schools');
    
  }
  
  public function preDown(\Doctrine\DBAL\Schema\Schema $schema)
  {
    parent::preDown($schema);
    $this->write("<info>All school data is currently lost in a down migration.  Fix this.</info>");
  }
}
