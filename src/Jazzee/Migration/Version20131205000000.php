<?php

namespace Jazzee\Migration;

/**
 * Add lock stamp to decisions
 */
class Version20131205000000 extends \Doctrine\DBAL\Migrations\AbstractMigration
{
    public function up(\Doctrine\DBAL\Schema\Schema $schema)
    {
        $table = $schema->getTable('decisions');
        $table->addColumn('lockedAt', 'datetime', array('notNull' => false));
    }

    public function down(\Doctrine\DBAL\Schema\Schema $schema)
    {
        $table = $schema->getTable('decisions');
        $table->dropColumn('lockedAt');
    }
}
