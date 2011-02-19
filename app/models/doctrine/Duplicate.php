<?php

/**
 * Duplicate
 * 
 * @property integer $applicantID
 * @property integer $duplicateID
 * @property boolean $disergard
 * @property Applicant $OriginalApplicant
 * @property Applicant $DuplicateApplicant
 * 
 * @package    jazzee
 * @subpackage orm
 */
class Duplicate extends Doctrine_Record{
  
  /**
   * @see Doctrine_Record_Abstract::setTableDefinition()
   */
  public function setTableDefinition(){
    $this->setTableName('duplicate');
    $this->hasColumn('applicantID', 'integer', null, array(
      'type' => 'integer',
     ));
    $this->hasColumn('duplicateID', 'integer', null, array(
      'type' => 'integer',
     ));
    $this->hasColumn('disergard', 'boolean', null, array(
      'type' => 'boolean',
     ));
  }

  /**
   * @see Doctrine_Record::setUp()
   */
  public function setUp(){
    parent::setUp();
    $this->hasOne('Applicant as OriginalApplicant', array(
      'local' => 'applicantID',
      'foreign' => 'id',
      'onDelete' => 'CASCADE',
      'onUpdate' => 'CASCADE')
    );

    $this->hasOne('Applicant as DuplicateApplicant', array(
      'local' => 'duplicateID',
      'foreign' => 'id',
      'onDelete' => 'CASCADE',
      'onUpdate' => 'CASCADE')
    );
  }
}