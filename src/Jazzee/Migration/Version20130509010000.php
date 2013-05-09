<?php

namespace Jazzee\Migration;

/**
 * Migrate DB to add byInvitationOnly to applications
 */
class Version20130509010000 extends \Doctrine\DBAL\Migrations\AbstractMigration
{
  public function up(\Doctrine\DBAL\Schema\Schema $schema)
  {
    $table = $schema->getTable('applications');
    $table->addColumn('byInvitationOnly', 'boolean', array());
  }

  public function down(\Doctrine\DBAL\Schema\Schema $schema)
  {
    $table = $schema->getTable('applications');
    $table->dropColumn('byinvitationonly');
  }
}
