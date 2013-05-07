<?php

namespace Jazzee\Migration;

/**
 * Migration to varaibles list items
 */
class Version20130506000000 extends \Doctrine\DBAL\Migrations\AbstractMigration
{
  public function up(\Doctrine\DBAL\Schema\Schema  $schema)
  {
    $table = $schema->getTable('pages');
    $table->addColumn('fixedId', 'integer', array('notNull' => false));

  }

  public function down(\Doctrine\DBAL\Schema\Schema  $schema)
  {
    $table = $schema->getTable('pages');
    $table->dropColumn('fixedid');
  }
}
