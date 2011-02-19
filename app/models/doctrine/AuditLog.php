<?php
/**
 * AuditLog
 * 
 * @property integer $userID
 * @property string $log
 * 
 * @package    jazzee
 * @subpackage orm
 */
class AuditLog extends Doctrine_Record{
  
  /**
   * @see Doctrine_Record_Abstract::setTableDefinition()
   */
  public function setTableDefinition(){
    $this->setTableName('audit_log');
    $this->hasColumn('userID', 'integer', 64, array(
      'type' => 'integer',
      'notnull' => true,
      'readonly' => true,
      'length' => '64',
     ));
    $this->hasColumn('log', 'string', 3000, array(
      'type' => 'string',
      'notnull' => true,
      'readonly' => true,
      'length' => '3000',
     ));
  }

  /**
   * @see Doctrine_Record::setUp()
   */
  public function setUp(){
    parent::setUp();
    $timestampable0 = new Doctrine_Template_Timestampable(array(
      'updated' => array(
        'disabled' => true,
      ),
     ));
     $this->actAs($timestampable0);
  }
}