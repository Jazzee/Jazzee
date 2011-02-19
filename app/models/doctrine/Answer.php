<?php 
/**
 * Answer
 * 
 * @property integer $applicantID
 * @property integer $pageID
 * @property integer $parentID
 * @property integer $publicStatus
 * @property integer $privateStatus
 * @property blob $attachment
 * @property string $uniqueID
 * @property bool $locked
 * @property timestamp $updatedAt
 * @property Applicant $Applicant
 * @property Page $Page
 * @property StatusType $PublicStatus
 * @property StatusType $PrivateStatus
 * @property Answer $Parent
 * @property Doctrine_Collection $Children
 * @property Doctrine_Collection $Elements
 * @property Score $Score
 * 
 * @package    jazzee
 * @subpackage orm
 */
class Answer extends Doctrine_Record{
  
  /**
   * @see Doctrine_Record_Abstract::setTableDefinition()
   */
  public function setTableDefinition(){
    $this->setTableName('answer');
    $this->hasColumn('applicantID', 'integer', null, array(
      'type' => 'integer',
     ));
    $this->hasColumn('pageID', 'integer', null, array(
      'type' => 'integer',
     ));
    $this->hasColumn('parentID', 'integer', null, array(
      'type' => 'integer',
     ));
    $this->hasColumn('publicStatus', 'integer', null, array(
      'type' => 'integer',
     ));
    $this->hasColumn('privateStatus', 'integer', null, array(
      'type' => 'integer',
     ));
    $this->hasColumn('attachment', 'blob', null, array(
      'type' => 'blob',
     ));
    $this->hasColumn('uniqueID', 'string', 200, array(
      'type' => 'string',
      'length' => 200,
     ));
    $this->hasColumn('locked', 'bool', null, array(
      'type' => 'bool',
     ));
    $this->hasColumn('updatedAt', 'timestamp', null, array(
      'type' => 'timestamp',
     ));

    $this->index('uniqueID', array(
      'fields' => array(
        'uniqueID' => array(
          'length' => 200,
        ),
      ),
      'type' => 'unique',
     ));
  }

  public function setUp(){
    parent::setUp();
    $this->hasOne('Applicant', array(
      'local' => 'applicantID',
      'foreign' => 'id',
      'onDelete' => 'CASCADE',
      'onUpdate' => 'CASCADE')
    );

    $this->hasOne('Page', array(
      'local' => 'pageID',
      'foreign' => 'id',
      'onDelete' => 'CASCADE',
      'onUpdate' => 'CASCADE')
    );

    $this->hasOne('StatusType as PublicStatus', array(
      'local' => 'publicStatus',
      'foreign' => 'id')
    );

    $this->hasOne('StatusType as PrivateStatus', array(
      'local' => 'privateStatus',
      'foreign' => 'id')
    );

    $this->hasOne('Answer as Parent', array(
      'local' => 'parentID',
      'foreign' => 'id',
      'onDelete' => 'CASCADE',
      'onUpdate' => 'CASCADE')
    );

    $this->hasMany('Answer as Children', array(
      'local' => 'id',
      'foreign' => 'parentID')
    );

    $this->hasMany('ElementAnswer as Elements', array(
      'local' => 'id',
      'foreign' => 'answerID')
    );

    $this->hasOne('Score', array(
      'local' => 'id',
      'foreign' => 'answerID')
    );
  }
  
  /**
   * Whenever we are saved update the timestamp
   * @param $event Doctrine_Event
   */
  public function preSave(Doctrine_Event $event){
    if(!$this->isModified() AND !$this->Elements->isModified()) return;
    $modifiedFields = $this->getModified();
    if ( ! array_key_exists('updatedAt',$modifiedFields)) {
      $this->updatedAt = date('Y-m-d H:i:s', time());
    }
  }
  
  
  /**
   * Find child answer by pageID
   * @param integer $id
   * @return Answer || NULL
   */
  public function getChildByPageId($id){
    foreach($this->Children as $child){
      if($child->pageID == $id) return $child;
    }
    return false;
  }
}