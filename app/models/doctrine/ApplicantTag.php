<?php
/**
 * ApplicantTag
 * Many2Many connecter for Applicants and Tags
 * @property integer $applicantID
 * @property integer $tagID
 * @property Applicant $Applicant
 * @property Tag $Tag
 * @package    jazzee
 * @subpackage orm
 */
class ApplicantTag extends Doctrine_Record{
  /**
   * @see Doctrine_Record_Abstract::setTableDefinition()
   */
  public function setTableDefinition(){
    $this->setTableName('applicant_tag');
    $this->hasColumn(
      'applicantID', 
      'integer', 
      null, 
      array('primary' => true)
    );
    
    $this->hasColumn(
      'tagID', 
      'integer', 
      null, 
      array('primary' => true)
    );
  }
  
  /**
   * @see Doctrine_Record::setUp()
   * We have to redefine the relationship here so cascading deletes will work
   */
  public function setUp(){
    $this->hasOne('Applicant', array(
      'local' => 'applicantID',
      'foreign' => 'id',
      'onDelete' => 'CASCADE',
      'onUpdate' => 'CASCADE')
    );
    $this->hasOne('Tag', array(
      'local' => 'tagID',
      'foreign' => 'id',
      'onDelete' => 'CASCADE',
      'onUpdate' => 'CASCADE')
    );
  }
}