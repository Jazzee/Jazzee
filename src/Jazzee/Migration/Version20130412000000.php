<?php
namespace Jazzee\Migration;

/**
 * Add schools table
 */
class Version20130412000000 extends \Doctrine\DBAL\Migrations\AbstractMigration
{

  public function up(\Doctrine\DBAL\Schema\Schema $schema)
  {
        $table = $schema->createTable('schools');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('fullName', 'string', array('length' => 255));
        $table->addColumn('shortName', 'string', array('length' => 255));

        $table->addColumn('alias1', 'string', array('length' => 64));
        $table->addColumn('alias2', 'string', array('length' => 64));
        $table->addColumn('atp_code', 'string', array('length' => 64));

        $table->addColumn('location_code', 'string', array('length' => 32));
        $table->addColumn('district_code', 'string', array('length' => 32));
        $table->addColumn('sde_code', 'string', array('length' => 32));
        $table->addColumn('school_status', 'string', array('length' => 32));
        $table->addColumn('school_type', 'string', array('length' => 32));

        $table->addColumn('address1', 'string', array('length' => 255));
        $table->addColumn('address2', 'string', array('length' => 255));
        $table->addColumn('city', 'string', array('length' => 64));
        $table->addColumn('state', 'string', array('length' => 64));
        $table->addColumn('zip', 'string', array('length' => 64));
        $table->addColumn('country', 'string', array('length' => 128));
        $table->addIndex(array('fullName'), 'IDX_9990C606E66D287A');
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addUniqueIndex(array('fullName'), 'fullName');

    $table = $schema->getTable('element_types');

//insert into element_types (name, class) values ('School Chooser','\\Jazzee\\Element\\SchoolChooser');
  }

  public function down(\Doctrine\DBAL\Schema\Schema $schema)
  {
  }
}
