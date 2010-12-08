<?php

/**
 * BaseRoleAction
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $roleID
 * @property string $controller
 * @property string $action
 * @property Role $Role
 * 
 * @package    jazzee
 * @subpackage orm
 */
abstract class BaseRoleAction extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('role_action');
        $this->hasColumn('roleID', 'integer', null, array(
             'type' => 'integer',
             ));
        $this->hasColumn('controller', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('action', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Role', array(
             'local' => 'roleID',
             'foreign' => 'id',
             'onDelete' => 'CASCADE',
             'onUpdate' => 'CASCADE'));
    }
}