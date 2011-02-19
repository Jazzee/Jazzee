<?php
/**
 * Application
 * 
 * @property integer $programID
 * @property integer $cycleID
 * @property string $contactName
 * @property string $contactEmail
 * @property string $contactPhone
 * @property string $welcome
 * @property timestamp $open
 * @property timestamp $close
 * @property timestamp $begin
 * @property decimal $feeForeign
 * @property decimal $feeDomestic
 * @property boolean $published
 * @property boolean $visible
 * @property string $admitLetter
 * @property string $denyLetter
 * @property integer $requireGRE
 * @property integer $requireGRESubject
 * @property integer $requireTOEFL
 * @property string $statusPageText
 * @property Program $Program
 * @property Cycle $Cycle
 * @property Doctrine_Collection $Pages
 * @property Doctrine_Collection $Applicants
 * 
 * @package    jazzee
 * @subpackage orm
 * @author     Jon Johnson <jon.johnson@ucsf.edu>
 */
class Application extends Doctrine_Record{  
  
  /**
   * @see Doctrine_Record::setTableDefinition()
   */
  public function setTableDefinition(){
    $this->setTableName('application');
    $this->hasColumn('programID', 'integer', null, array(
      'type' => 'integer',
      'notnull' => true,
     ));
    $this->hasColumn('cycleID', 'integer', null, array(
      'type' => 'integer',
      'notnull' => true,
     ));
    $this->hasColumn('contactName', 'string', 255, array(
      'type' => 'string',
      'length' => '255',
     ));
    $this->hasColumn('contactEmail', 'string', 255, array(
      'type' => 'string',
      'notnull' => true,
      'length' => '255',
     ));
    $this->hasColumn('contactPhone', 'string', 13, array(
      'type' => 'string',
      'length' => '13',
     ));
    $this->hasColumn('welcome', 'string', 4000, array(
      'type' => 'string',
      'length' => '4000',
     ));
    $this->hasColumn('open', 'timestamp', null, array(
      'type' => 'timestamp',
     ));
    $this->hasColumn('close', 'timestamp', null, array(
      'type' => 'timestamp',
     ));
    $this->hasColumn('begin', 'timestamp', null, array(
      'type' => 'timestamp',
     ));
    $this->hasColumn('feeForeign', 'decimal', null, array(
      'type' => 'decimal',
     ));
    $this->hasColumn('feeDomestic', 'decimal', null, array(
      'type' => 'decimal',
     ));
    $this->hasColumn('published', 'boolean', null, array(
      'type' => 'boolean',
     ));
    $this->hasColumn('visible', 'boolean', null, array(
      'type' => 'boolean',
     ));
    $this->hasColumn('admitLetter', 'string', 4000, array(
      'type' => 'string',
      'length' => '4000',
     ));
    $this->hasColumn('denyLetter', 'string', 4000, array(
      'type' => 'string',
      'length' => '4000',
     ));
    $this->hasColumn('requireGRE', 'integer', 1, array(
      'type' => 'integer',
      'length' => '1',
     ));
    $this->hasColumn('requireGRESubject', 'integer', 1, array(
      'type' => 'integer',
      'length' => '1',
     ));
    $this->hasColumn('requireTOEFL', 'integer', 1, array(
      'type' => 'integer',
      'length' => '1',
     ));
    $this->hasColumn('statusPageText', 'string', 4000, array(
      'type' => 'string',
      'length' => '4000',
     ));

    $this->index('program_cycle', array(
      'fields' => array(
        0 => 'programID',
        1 => 'cycleID',
      ),
      'type' => 'unique',
     ));
  }

  /**
   * @see Doctrine_Record::setUp()
   */
  public function setUp(){
    parent::setUp();
    $this->hasOne('Program', array(
      'local' => 'programID',
      'foreign' => 'id',
      'onDelete' => 'CASCADE',
      'onUpdate' => 'CASCADE')
    );

    $this->hasOne('Cycle', array(
      'local' => 'cycleID',
      'foreign' => 'id',
      'onDelete' => 'CASCADE',
      'onUpdate' => 'CASCADE')
    );

    $this->hasMany('ApplicationPage as Pages', array(
      'local' => 'id',
   		'foreign' => 'applicationID')
    );
    $this->hasMany('Applicant as Applicants', array(
      'local' => 'id',
      'foreign' => 'applicationID')
    );
  }
  
  /**
   * Find Pages by weight
   * @returns Doctrine_Collection
   */
  public function findPagesByWeight(){
    $q = Doctrine_Query::create()
      ->select('*')
      ->from('ApplicationPage')
      ->where('applicationID = ?', $this->id)
      ->orderBy('weight asc');
    return $q->execute();
  }
  
  /**
  * Get page by ID
  * @param integer $pageID
  * @return ApplicationPage
  */
  public function getPageByID($pageID){
    $key = array_search($pageID, $this->Pages->getPrimaryKeys());
    if($key !== false){ //use === becuase 0 is returned often
      return $this->Pages->get($key);
    }
    return false;
  }
  
 /**
  * Get page by its global ID
  * @param integer $globalPageID
  * @return ApplicationPage
  */
  public function getApplicationPageByGlobalID($globalPageID){
    foreach($this->Pages as $applicationPage){
      if($globalPageID == $applicationPage->pageID) return $applicationPage;
    }
    return false;
  }
  
  /**
   * Get applicant by ID
   * @param integer $id
   * @return Applicant
   */
  public function getApplicantByID($id){
    $key = array_search($id, $this->Applicants->getPrimaryKeys());
    if($key !== false){ //use === becuase 0 is returned often
      return $this->Applicants->get($key);
    }
    return false;
  }
  
  /**
   * Find an applicant by name
   * @param string $lastName
   * @param string $firstName
   * @param string $middleName
   */
  public function findApplicantsByName($lastName = false, $firstName = false, $middleName = false){
    $q = Doctrine_Query::create()
          ->select('*')
          ->from('Applicant')
          ->where('applicationID = ?', $this->id);
      if($lastName)
          $q->andWhere('lastName like ?', $lastName . '%');
      if($firstName)
          $q->andWhere('firstName like ?', $firstName . '%');   
      if($middleName)
          $q->andWhere('middleName = ?', $middleName);
    return $q->execute();
  }
  
  
  /**
   * Find locked applicants
   */
  public function findLockedApplicants(){
    $q = Doctrine_Query::create()
          ->select('*')
          ->from('Applicant')
          ->where('applicationID = ?', $this->id)
          ->andWhere('locked IS NOT NULL');
    return $q->execute();
  }
  
  /**
   * After we save the application make sure all of its pages are properly saved too
   * At some point doctrine is unable to follow the relationships deep enough
   * This method explicitly saves the members of collections with the correct id
   */
  public function postSave(){
    foreach($this->Pages as $page){
      if($page->isModified(true)){
        $page->applicationID = $this->id;
        $page->save();
      }
    }
  }
}