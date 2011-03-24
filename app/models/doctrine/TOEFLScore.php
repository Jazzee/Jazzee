<?php
/**
 * TOEFLScore
 * @property string $registrationNumber
 * @property integer $testMonth
 * @property integer $testYear
 * @property string $departmentCode
 * @property string $firstName
 * @property string $middleName
 * @property string $lastName
 * @property date $birthDate
 * @property enum $sex
 * @property string $nativeCountry
 * @property string $nativeLanguage
 * @property date $testDate
 * @property string $testType
 * @property int $listeningIndicator
 * @property int $speakingIndicator
 * @property int $IBTListening
 * @property int $IBTWriting
 * @property int $IBTSpeaking
 * @property int $IBTReading
 * @property int $IBTTotal
 * @property int $TSEScore
 * @property int $listening
 * @property int $writing
 * @property int $reading
 * @property int $essay
 * @property int $total
 * @property int $timesTaken
 * @property boolean $offTopic
 * 
 * @package    jazzee
 * @subpackage orm
 */
class TOEFLScore extends Doctrine_Record{
  /**
   * @see Doctrine_Record_Abstract::setTableDefinition()
   */  
  public function setTableDefinition(){
    //If we don't set the table name the all caps creates an ugly one
    $this->setTableName('toefl_score');
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
    $this->hasColumn('departmentCode', 'string', 4, array(
      'type' => 'string',
      'length' => '4',
    ));
    $this->hasColumn('firstName', 'string', 255, array(
      'type' => 'string',
      'length' => '255',
    ));
    $this->hasColumn('middleName', 'string', 255, array(
      'type' => 'string',
      'length' => '255',
    ));
    $this->hasColumn('lastName', 'string', 255, array(
      'type' => 'string',
      'length' => '255',
    ));
    $this->hasColumn('birthDate', 'date', null, array(
      'type' => 'date',
    ));
    $this->hasColumn('sex', 'enum', null, array(
      'type' => 'enum',
      'values' => array(
        0 => 'm',
        1 => 'f',
      ),
    ));
    $this->hasColumn('nativeCountry', 'string', 255, array(
      'type' => 'string',
      'length' => '255',
    ));
    $this->hasColumn('nativeLanguage', 'string', 255, array(
      'type' => 'string',
      'length' => '255',
    ));
    $this->hasColumn('testDate', 'date', null, array(
      'type' => 'date',
    ));
    $this->hasColumn('testType', 'string', 255, array(
      'type' => 'string',
      'length' => '255',
    ));
    $this->hasColumn('listeningIndicator', 'int', 1, array(
      'type' => 'int',
      'length' => '1',
    ));
    $this->hasColumn('speakingIndicator', 'int', 1, array(
      'type' => 'int',
      'length' => '1',
    ));
    $this->hasColumn('IBTListening', 'int', 2, array(
      'type' => 'int',
      'length' => '2',
    ));
    $this->hasColumn('IBTWriting', 'int', 2, array(
      'type' => 'int',
      'length' => '2',
    ));
    $this->hasColumn('IBTSpeaking', 'int', 2, array(
      'type' => 'int',
      'length' => '2',
    ));
    $this->hasColumn('IBTReading', 'int', 2, array(
      'type' => 'int',
      'length' => '2',
    ));
    $this->hasColumn('IBTTotal', 'int', 3, array(
      'type' => 'int',
      'length' => '3',
    ));
    $this->hasColumn('TSEScore', 'int', 2, array(
      'type' => 'int',
      'length' => '2',
    ));
    $this->hasColumn('listening', 'int', 2, array(
      'type' => 'int',
      'length' => '2',
    ));
    $this->hasColumn('writing', 'int', 2, array(
      'type' => 'int',
      'length' => '2',
    ));
    $this->hasColumn('reading', 'int', 2, array(
      'type' => 'int',
      'length' => '2',
    ));
    $this->hasColumn('essay', 'int', 2, array(
      'type' => 'int',
      'length' => '2',
    ));
    $this->hasColumn('total', 'int', 3, array(
      'type' => 'int',
      'length' => '3',
    ));
    $this->hasColumn('timesTaken', 'int', 1, array(
      'type' => 'int',
      'length' => '1',
    ));
    $this->hasColumn('offTopic', 'string', 1, array(
      'type' => 'string',
      'length' => '1',
    ));
  
    $this->index('registration_number', array(
      'fields' => array(
        0 => 'registrationNumber',
        1 => 'testMonth',
        2 => 'testYear',
      ),
      'type' => 'unique',
    ));
  }
}