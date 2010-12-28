<?php

/**
 * BaseApplicationPage
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $applicationID
 * @property integer $pageID
 * @property integer $weight
 * @property string $title
 * @property integer $min
 * @property integer $max
 * @property boolean $optional
 * @property string $instructions
 * @property string $leadingText
 * @property string $trailingText
 * @property Application $Application
 * @property Page $Page
 * 
 * @package    jazzee
 * @subpackage orm
 */
abstract class BaseApplicationPage extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('application_page');
        $this->hasColumn('applicationID', 'integer', null, array(
             'type' => 'integer',
             ));
        $this->hasColumn('pageID', 'integer', null, array(
             'type' => 'integer',
             ));
        $this->hasColumn('weight', 'integer', null, array(
             'type' => 'integer',
             'default' => 0,
             ));
        $this->hasColumn('title', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('min', 'integer', null, array(
             'type' => 'integer',
             ));
        $this->hasColumn('max', 'integer', null, array(
             'type' => 'integer',
             ));
        $this->hasColumn('optional', 'boolean', null, array(
             'type' => 'boolean',
             ));
        $this->hasColumn('instructions', 'string', 3000, array(
             'type' => 'string',
             'length' => '3000',
             ));
        $this->hasColumn('leadingText', 'string', 3000, array(
             'type' => 'string',
             'length' => '3000',
             ));
        $this->hasColumn('trailingText', 'string', 3000, array(
             'type' => 'string',
             'length' => '3000',
             ));


        $this->index('global_page', array(
             'fields' => 
             array(
              0 => 'applicationID',
              1 => 'pageID',
             ),
             'type' => 'unique',
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Application', array(
             'local' => 'applicationID',
             'foreign' => 'id',
             'onDelete' => 'CASCADE',
             'onUpdate' => 'CASCADE'));

        $this->hasOne('Page', array(
             'local' => 'pageID',
             'foreign' => 'id',
             'onDelete' => 'CASCADE',
             'onUpdate' => 'CASCADE'));
    }
}