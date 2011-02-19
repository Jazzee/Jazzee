<?php

/**
 * Attachment
 * 
 * @property integer $applicantID
 * @property blob $attachment
 * @property Applicant $Applicant
 * 
 * @package    jazzee
 * @subpackage orm
 */
class Attachment extends Doctrine_Record{
  
  /**
   * @see Doctrine_Record_Abstract::setTableDefinition()
   */
  public function setTableDefinition(){
    $this->setTableName('attachment');
    $this->hasColumn('applicantID', 'integer', null, array(
      'type' => 'integer',
     ));
    $this->hasColumn('attachment', 'blob', null, array(
      'type' => 'blob',
     ));
  }

  /**
   * @see Doctrine_Record::setUp()
   */
  public function setUp(){
    parent::setUp();
    $this->hasOne('Applicant', array(
      'local' => 'applicantID',
      'foreign' => 'id',
      'onDelete' => 'CASCADE',
      'onUpdate' => 'CASCADE')
    );
  }
}