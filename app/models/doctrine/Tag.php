<?php
/**
 * Tag
 * 
 * @property string $title
 * @property Doctrine_Collection $Applicants
 * 
 * @package    jazzee
 * @subpackage orm
 */
class Tag extends Doctrine_Record{
  /**
   * @see Doctrine_Record_Abstract::setTableDefinition()
   */
  public function setTableDefinition(){
    $this->setTableName('tag');
    $this->hasColumn('title', 'string', 255, array(
      'type' => 'string',
      'length' => '255',
     ));
     
     $this->index('tag_title', array(
       'fields' => array('title'),
       'type' => 'unique'
     ));
  }
  /**
   * @see Doctrine_Record::setUp()
   */
  public function setUp(){
    parent::setUp();
    $this->hasMany('Applicant as Applicants', array(
      'local' => 'tagID',
      'foreign' => 'applicantID',
			'refClass' => 'ApplicantTag')
    );
  }
}