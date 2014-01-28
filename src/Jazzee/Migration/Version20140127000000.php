<?php

namespace Jazzee\Migration;

/**
 * Make text in templates nullable
 */
class Version20140127000000 extends \Doctrine\DBAL\Migrations\AbstractMigration
{
    public function up(\Doctrine\DBAL\Schema\Schema $schema)
    {
        $table = $schema->getTable('templates');
        $column = $table->getColumn('text');
        $column->setNotNull(false);
    }

    public function down(\Doctrine\DBAL\Schema\Schema $schema)
    {
        $table = $schema->getTable('templates');
        $column = $table->getColumn('text');
        $column->setNotNull(true);
    }
}
