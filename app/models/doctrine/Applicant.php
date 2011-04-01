<?php
/**
 * Applicant
 * 
 * @property integer $applicationID
 * @property string $email
 * @property string $password
 * @property string $activate_token
 * @property timestamp $locked
 * @property string $firstName
 * @property string $middleName
 * @property string $lastName
 * @property string $suffix
 * @property timestamp $deadlineExtension
 * @property timestamp $lastLogin
 * @property string $lastLogin_ip
 * @property string $lastFailedLogin_ip
 * @property integer $failedLoginAttempts
 * @property timestamp $createdAt
 * @property timestamp $updatedAt
 * @property Application $Application
 * @property Doctrine_Collection $Attachments
 * @property Doctrine_Collection $Duplicates
 * @property Doctrine_Collection $Duplicate
 * @property Decision $Decision
 * @property Doctrine_Collection $Communication
 * @property Doctrine_Collection $Payments
 * @property Doctrine_Collection $Answers
 * @property Doctrine_Collection $Tags
 * 
 * @package    jazzee
 * @subpackage orm
 * @author     Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 */
class Applicant extends Doctrine_Record{
  
  /**
   * @see Doctrine_Record::setTableDefinition()
   */
  public function setTableDefinition(){
    $this->setTableName('applicant');
    $this->hasColumn('applicationID', 'integer', null, array(
      'type' => 'integer',
     ));
    $this->hasColumn('email', 'string', 255, array(
      'type' => 'string',
      'notnull' => true,
      'notblank' => true,
      'length' => '255',
     ));
    $this->hasColumn('password', 'string', 60, array(
      'type' => 'string',
      'notnull' => true,
      'notblank' => true,
      'length' => '60',
     ));
    $this->hasColumn('activate_token', 'string', 40, array(
      'type' => 'string',
      'unique' => true,
      'length' => '40',
     ));
    $this->hasColumn('locked', 'timestamp', null, array(
      'type' => 'timestamp',
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
    $this->hasColumn('suffix', 'string', 255, array(
      'type' => 'string',
      'length' => '255',
     ));
    $this->hasColumn('deadlineExtension', 'timestamp', null, array(
      'type' => 'timestamp',
     ));
    $this->hasColumn('lastLogin', 'timestamp', null, array(
      'type' => 'timestamp',
     ));
    $this->hasColumn('lastLogin_ip', 'string', 15, array(
      'type' => 'string',
      'ip' => true,
      'length' => '15',
     ));
    $this->hasColumn('lastFailedLogin_ip', 'string', 15, array(
      'type' => 'string',
      'ip' => true,
      'length' => '15',
     ));
    $this->hasColumn('failedLoginAttempts', 'integer', 2, array(
      'type' => 'integer',
      'length' => '2',
     ));
    $this->hasColumn('createdAt', 'timestamp', null, array(
      'type' => 'timestamp',
     ));
    $this->hasColumn('updatedAt', 'timestamp', null, array(
      'type' => 'timestamp',
     ));


    $this->index('application_email', array(
      'fields' => array(
        0 => 'applicationID',
        1 => 'email',
      ),
      'type' => 'unique',
     ));
  }

  /**
   * @see Doctrine_Record::setUp()
   */
  public function setUp(){
    $this->hasOne('Application', array(
      'local' => 'applicationID',
      'foreign' => 'id',
      'onDelete' => 'CASCADE',
      'onUpdate' => 'CASCADE')
    );

    $this->hasMany('Attachment as Attachments', array(
      'local' => 'id',
      'foreign' => 'applicantID')
    );

    $this->hasMany('Duplicate as Duplicates', array(
      'local' => 'id',
      'foreign' => 'applicantID')
    );

    $this->hasMany('Duplicate', array(
      'local' => 'id',
      'foreign' => 'duplicateID')
    );

    $this->hasOne('Decision', array(
      'local' => 'id',
      'foreign' => 'applicantID')
    );

    $this->hasMany('Communication', array(
      'local' => 'id',
      'foreign' => 'applicantID')
    );

    $this->hasMany('Payment as Payments', array(
      'local' => 'id',
      'foreign' => 'applicantID')
    );

    $this->hasMany('Answer as Answers', array(
      'local' => 'id',
      'foreign' => 'applicantID')
    );
    
    $this->hasMany('Tag as Tags', array(
      'local' => 'applicantID',
      'foreign' => 'tagID',
			'refClass' => 'ApplicantTag')
    );
  }
  
  /**
   * Hash and then store the password 
   * @param string $value the user input password
   */
  public function setPassword($value){
    $p = new PasswordHash(8, FALSE);
    $this->_set('password',$p->HashPassword($value));
  }
  
  /**
   * Store the previously hashed version of the password
   * @param string $value the user input password
   */
  public function setHashedPassword($value){
    $this->_set('password',$value);
  }
  
    
  /**
   * Check a password against its hash
   * @param string $password
   * @param string $hashedPassword
   */
  public function checkPassword($password){
    $p = new PasswordHash(8, FALSE);
    return $p->CheckPassword($password, $this->password);
  }
  
  /**
   * Get all of the answers for a page by ID
   * @param int $pageID
   * @return array
   */
  public function getAnswersForPage($pageID){
    $q = Doctrine_Query::create()
    ->select('*')
    ->from('Answer a')
    ->where('a.PageID = ? AND a.applicantID = ?', array($pageID, $this->id));
    $answers =  $q->execute();
    //the indexby DQL specifier doesnt seem to work
    $return = array();
    foreach($answers as $answer){
      $return[$answer['id']] = $answer;
    }
    return $return;
  }
  
  /**
   * Get answer by ID
   * @param integer $answerID
   * @return Answer
   */
  public function getAnswerByID($answerID){
    $key = array_search($answerID, $this->Answers->getPrimaryKeys());
    if($key !== false){ //use === becuase 0 is returned often
      return $this->Answers->get($key);
    }
    return false;
  }
  
  /**
   * Get score by ID
   * @param integer $scoreID
   * @return ScoreMatch
   */
  public function getScoreByID($scoreID){
    $key = array_search($scoreID, $this->ScoreMatch->getPrimaryKeys());
    if($key !== false){ //use === becuase 0 is returned often
      return $this->ScoreMatch->get($key);
    }
    return false;
  }
  
  /**
   * Whenever we new applicant is inserted set the createdAt timestamp
   * @param $event Doctrine_Event
   */
  public function preInsert($event){
      $modified = $event->getInvoker()->getModified();
      if ( ! array_key_exists('createdAt',$modified)) {
        $event->getInvoker()->createdAt = date('Y-m-d H:i:s', time());
      }
  }
  
  /**
   * Whenever we are saved update the timestamp
   * @param $event Doctrine_Event
   */
  public function preSave($event){
      $modified = $event->getInvoker()->getModified();
      if ( ! array_key_exists('updatedAt',$modified)) {
        $event->getInvoker()->updatedAt = date('Y-m-d H:i:s', time());
      }
  }
  
  /**
   * Lock an application
   */
  public function lock(){
    $this->locked = date('Y-m-d H:i:s');
  }
  
  /**
   * UnLock an application
   */
  public function unlock(){
    $this->locked = null;
  }
  
  /**
   * Get all of the communication threads involving the applicant
   * @return Array of Communication
   */
  public function findCommunicationThreads(){
    $q = Doctrine_Query::create()
    ->select('*')
    ->from('Communication')
    ->where('parentID IS NULL')
    ->AndWhere('applicantID=?', array($this->id))
    ->orderBy('createdAt ASC');
    return $q->execute();
  }
  
  /**
   * Tag an applicant
   * @param string $title
   */
  public function addTag($title){
    foreach($this->Tags as $tag){
      if($tag->title == $title) return true;
    }
    $q = Doctrine_Query::create()
      ->select('*')
      ->from('Tag')
      ->where('title=?', array($title));
    $tags = $q->execute();
    if($tags->count())
      $this->Tags[] = $tags->getFirst();
    else
      $this->Tags[]->title = $title;
  }
  
/**
   * Remove a tag from an applicant
   * @param string $title
   */
  public function removeTag($title){
    foreach($this->Tags as $index => $tag){
      if($tag->title == $title) $this->Tags->remove($index);
    }
  }
}