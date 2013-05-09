<?php

namespace Jazzee\Migration;

/**
 * Migrate DB to add external ID to applicants
 */
class Version20130509000000 extends \Doctrine\DBAL\Migrations\AbstractMigration
{
  public function up(\Doctrine\DBAL\Schema\Schema $schema)
  {
    $table = $schema->getTable('applicants');
    $table->addColumn('externalId', 'string', array(
        'length' => 255,
        'notNull' => false,
    ));
  }

  public function down(\Doctrine\DBAL\Schema\Schema $schema)
  {
    $table = $schema->getTable('applicants');
    $table->dropColumn('externalid');
  }
}
