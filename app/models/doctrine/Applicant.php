<?php

/**
 * Applicant
 * @package    jazzee
 * @subpackage orm
 * @author     Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 */
class Applicant extends BaseApplicant{
  
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
  public function preInsert(Doctrine_Event $event){
      $modified = $event->getInvoker()->getModified();
      if ( ! array_key_exists('createdAt',$modified)) {
        $event->getInvoker()->createdAt = date('Y-m-d H:i:s', time());
      }
  }
  
  /**
   * Whenever we are saved update the timestamp
   * @param $event Doctrine_Event
   */
  public function preSave(Doctrine_Event $event){
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
}