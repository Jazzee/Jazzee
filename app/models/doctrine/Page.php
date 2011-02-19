<?php
/**
 * Page
 * 
 * @property string $title
 * @property integer $pageType
 * @property integer $min
 * @property integer $max
 * @property boolean $optional
 * @property boolean $showAnswerStatus
 * @property string $instructions
 * @property string $leadingText
 * @property string $trailingText
 * @property boolean $isGlobal
 * @property integer $parentID
 * @property PageType $PageType
 * @property Page $Parent
 * @property Doctrine_Collection $Children
 * @property Doctrine_Collection $Variables
 * @property Doctrine_Collection $Elements
 * @property ApplicationPage $ApplicationPage
 * @property Doctrine_Collection $Answer
 * @package    jazzee
 * @subpackage orm
 * @author     Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 */
class Page extends Doctrine_Record{
  
  /**
   * @see Doctrine_Record_Abstract::setTableDefinition()
   */
  public function setTableDefinition(){
    $this->setTableName('page');
    $this->hasColumn('title', 'string', 255, array(
      'type' => 'string',
      'length' => '255',
     ));
    $this->hasColumn('pageType', 'integer', null, array(
      'type' => 'integer',
     ));
    $this->hasColumn('min', 'integer', null, array(
      'type' => 'integer',
     ));
    $this->hasColumn('max', 'integer', null, array(
      'type' => 'integer',
     ));
    $this->hasColumn('optional', 'boolean', null, array(
      'type' => 'boolean',
     ));
    $this->hasColumn('showAnswerStatus', 'boolean', null, array(
      'type' => 'boolean',
     ));
    $this->hasColumn('instructions', 'string', 3000, array(
      'type' => 'string',
      'length' => '3000',
     ));
    $this->hasColumn('leadingText', 'string', 3000, array(
      'type' => 'string',
      'length' => '3000',
     ));
    $this->hasColumn('trailingText', 'string', 3000, array(
      'type' => 'string',
      'length' => '3000',
     ));
    $this->hasColumn('isGlobal', 'boolean', null, array(
      'type' => 'boolean',
     ));
    $this->hasColumn('parentID', 'integer', null, array(
      'type' => 'integer',
     ));
  }

  /**
   * @see Doctrine_Record::setUp()
   */
  public function setUp(){
    parent::setUp();
    $this->hasOne('PageType', array(
      'local' => 'pageType',
      'foreign' => 'id')
    );

    $this->hasOne('Page as Parent', array(
      'local' => 'parentID',
      'foreign' => 'id',
      'onDelete' => 'CASCADE',
      'onUpdate' => 'CASCADE')
    );

    $this->hasMany('Page as Children', array(
     'local' => 'id',
     'foreign' => 'parentID')
    );

    $this->hasMany('PageVariable as Variables', array(
      'local' => 'id',
      'foreign' => 'pageID')
    );

    $this->hasMany('Element as Elements', array(
      'local' => 'id',
      'foreign' => 'pageID')
    );

    $this->hasOne('ApplicationPage', array(
      'local' => 'id',
      'foreign' => 'pageID')
    );

    $this->hasMany('Answer', array(
     'local' => 'id',
     'foreign' => 'pageID')
    );
  }
  
  /**
   * Find Elements by weight
   * @returns Doctrine_Collection
   */
  public function findElementsByWeight(){
    $q = Doctrine_Query::create()
      ->select('*')
      ->from('Element')
      ->where('pageID = ?', $this->id)
      ->orderBy('weight asc');
    return $q->execute();
  }
  
  public function getElementByTitle($title){
    foreach($this['Elements'] as $element){
      if($element->title == $title){return $element;}
    }
    return self::$_null;
  }
  
  /**
   * Find an element by ID
   * @param integer $id
   */
  public function getElementById($id){
    $key = array_search($id, $this->Elements->getPrimaryKeys());
    if($key !== false){ //use === becuase 0 is returned often
      return $this->Elements->get($key);
    }
    return false;
  }
  
  /**
   * Get Variable by name
   * @param string $name
   * @return blob || NULL
   */
  public function getVar($name){
    foreach($this['Variables'] as $variable)
      if($variable->name == $name)return $variable->value;
    return self::$_null;
  }
  
  /**
   * Set page variable
   * @param string $name
   * @param string $value
   */
  public function setVar($name, $value){
    foreach($this['Variables'] as $variable)
      if($variable->name == $name)return $variable->value = $value;
    //create a new empty variable with that name
    $var = $this->Variables->get(null);
    $var->name = $name;
    $var->value = $value;
  }
  
  /**
   * Find child page by ID
   * @param integer $id
   * @return Page || NULL
   */
  public function getChildById($id){
    foreach($this->Children as $child){
      if($child->id == $id) return $child;
    }
    return false;
  }
  
  /**
   * After we save the Page make sure all of its elements and children are saved too
   * At some point doctrine is unable to follow the relationships deep enough
   * This method explicitly saves the members of collections with the correct id
   * @param mixed $event
   */
  public function postSave($event){
    foreach($this->Elements as $element){
      if($element->isModified(true)){
        $element->pageID = $this->id;
        $element->save();
      }
    }
    foreach($this->Children as $child){
      if($child->isModified(true)){
        $child->parentID = $this->id;
        $child->save();
      }
    }
  foreach($this->Variables as $variable){
      if($variable->isModified(true)){
        $variable->pageID = $this->id;
        $variable->save();
      }
    }
  }
}