<?php

namespace Jazzee\Migration;

/**
 * Add external ID validation for application entity
 */
class Version20130712000000 extends \Doctrine\DBAL\Migrations\AbstractMigration
{
  public function up(\Doctrine\DBAL\Schema\Schema $schema)
  {
    // Alter table: applications
    $table = $schema->getTable('applications');
    $table->addColumn('externalIdValidationExpression', 'string', array(
      'length' => 255,
      'notNull' => false,
    ));
  }

  public function down(\Doctrine\DBAL\Schema\Schema $schema)
  {
    // Alter table: applications
    $table = $schema->getTable('applications');
    $table->dropColumn('externalidvalidationexpression');
  }
}
