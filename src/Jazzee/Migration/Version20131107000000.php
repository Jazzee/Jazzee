<?php

namespace Jazzee\Migration;

/**
 * Switch display roles from oneToOne to oneToMany
 */
class Version20131107000000 extends \Doctrine\DBAL\Migrations\AbstractMigration
{
    public function up(\Doctrine\DBAL\Schema\Schema $schema)
    {
        $table = $schema->getTable('displays');
        $table->dropIndex('UNIQ_54DDEC2BD60322AC');
        $table->addIndex(array('role_id'), 'IDX_54DDEC2BD60322AC');
        $table->addUniqueIndex(array('role_id', 'application_id'), 'role_application');
    }

    public function down(\Doctrine\DBAL\Schema\Schema $schema)
    {
        $table = $schema->getTable('displays');
        $table->dropIndex('role_application');
        $table->dropIndex('IDX_54DDEC2BD60322AC');
        $table->addUniqueIndex(array('role_id'), 'UNIQ_54DDEC2BD60322AC');
        
    }
}
