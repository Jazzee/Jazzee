<?php

/**
 * Score
 * 
 * @property integer $answerID
 * @property enum $scoreType
 * @property integer $scoreID
 * @property string $registrationNumber
 * @property integer $testMonth
 * @property integer $testYear
 * @property Answer $Answer
 * @property GREScore|TOEFLScore $Score
 * @package    jazzee
 * @subpackage orm
 * @author     Jon Johnson <jon.johnson@ucsf.edu>
 */
class Score extends Doctrine_Record{
  public $Score;
  /**
   * @see Doctrine_Record_Abstract::setTableDefinition()
   */
  public function setTableDefinition(){
    $this->setTableName('score');
    $this->hasColumn('answerID', 'integer', null, array(
      'type' => 'integer',
     ));
    $this->hasColumn('scoreType', 'enum', null, array(
      'type' => 'enum',
      'values' => array(
        0 => 'gre',
        1 => 'toefl',
      ),
     ));
    $this->hasColumn('scoreID', 'integer', null, array(
      'type' => 'integer',
     ));
    $this->hasColumn('registrationNumber', 'string', 255, array(
      'type' => 'string',
      'length' => '255',
     ));
    $this->hasColumn('testMonth', 'integer', null, array(
      'type' => 'integer',
     ));
    $this->hasColumn('testYear', 'integer', null, array(
      'type' => 'integer',
     ));


    $this->index('applicant_score', array(
      'fields' => array(
        0 => 'answerID',
        1 => 'scoreType',
        2 => 'scoreID',
      ),
      'type' => 'unique',
    ));
    $this->index('regnumber', array(
      'fields' =>  array(
        0 => 'scoreType',
        1 => 'registrationNumber',
        2 => 'testMonth',
        3 => 'testYear',
      ),
      'type' => 'unique',
    ));
  }
  
  /**
   * @see Doctrine_Record::setUp()
   */
  public function setUp(){
    parent::setUp();
    $this->hasOne('Answer', array(
      'local' => 'answerID',
      'foreign' => 'id',
      'onDelete' => 'CASCADE',
      'onUpdate' => 'CASCADE'));
  }
    
  /**
   * Since we can't get to the constructor this is a proxy
   */
  public function construct(){
    $this->loadMatchedScore();
  }
  
  /**
   * Load the matched score after every save
   */
  public function postSave($event){
    $this->loadMatchedScore();
  }
  
  /**
   * Try and load the matched score into $Score
   */
  protected function loadMatchedScore(){
    $this->Score = null;
    if($this->scoreID){
      switch($this->scoreType){
        case 'gre':
          $this->Score = Doctrine::getTable('GREScore')->find($this->scoreID);
          break;
        case 'toefl':
          $this->Score = Doctrine::getTable('TOEFLScore')->find($this->scoreID);
          break;
      }
    }
  }
  
  /**
   * Search for any scores that match the registration number
   */
  public function makeMatch(){
    if(empty($this->scoreID)){
      switch($this->scoreType){
        case 'gre':
          $q = Doctrine_Query::create()
            ->select('id')
            ->from('GREScore')
            ->where('RegistrationNumber = ?', $this->registrationNumber)
            ->andWhere('TestMonth = ?', $this->testMonth)
            ->andWhere('TestYear = ?', $this->testYear);
          break;
        case 'toefl':
          $q = Doctrine_Query::create()
            ->select('id')
            ->from('TOEFLScore')
            ->where('RegistrationNumber = ?', $this->registrationNumber)
            ->andWhere('TestMonth = ?', $this->testMonth)
            ->andWhere('TestYear = ?', $this->testYear);
          break;
      }
      $scores = $q->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
      if(!empty($scores)){
        $this->scoreID = $scores[0]['id'];
      }
    }
  }
}