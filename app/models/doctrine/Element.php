<?php

/**
 * Element
 * 
 * @property integer $pageID
 * @property integer $weight
 * @property integer $fixedID
 * @property integer $elementType
 * @property string $title
 * @property string $format
 * @property string $instructions
 * @property string $defaultValue
 * @property boolean $required
 * @property double $min
 * @property double $max
 * @property Page $Page
 * @property ElementType $ElementType
 * @property Doctrine_Collection $ListItems
 * @property Doctrine_Collection $ElementAnswer
 * @package    jazzee
 * @subpackage orm
 * @author     Jon Johnson <jon.johnson@ucsf.edu>
 */
class Element extends Doctrine_Record{
  /**
   * @see BaseElement::setTableDefinition()
   */
  public function setTableDefinition(){
    $this->setTableName('element');
    $this->hasColumn('pageID', 'integer', null, array(
      'type' => 'integer',
     ));
    $this->hasColumn('weight', 'integer', null, array(
      'type' => 'integer',
      'default' => 0,
     ));
    $this->hasColumn('fixedID', 'integer', null, array(
      'type' => 'integer',
     ));
    $this->hasColumn('elementType', 'integer', null, array(
      'type' => 'integer',
     ));
    $this->hasColumn('title', 'string', 255, array(
      'type' => 'string',
      'length' => '255',
     ));
    $this->hasColumn('format', 'string', 255, array(
      'type' => 'string',
      'length' => '255',
     ));
    $this->hasColumn('instructions', 'string', 3000, array(
      'type' => 'string',
      'length' => '3000',
     ));
    $this->hasColumn('defaultValue', 'string', 255, array(
      'type' => 'string',
      'length' => '255',
     ));
    $this->hasColumn('required', 'boolean', null, array(
      'type' => 'boolean',
     ));
    $this->hasColumn('min', 'double', null, array(
      'type' => 'double',
     ));
    $this->hasColumn('max', 'double', null, array(
      'type' => 'double',
     ));


    $this->index('UniqueFixedID', array(
      'fields' => array(
        0 => 'fixedID',
        1 => 'pageID',
      ),
      'type' => 'unique',
     ));
  }

  /**
   * (non-PHPdoc)
   * @see BaseElement::setUp()
   */
  public function setUp(){
    parent::setUp();
    $this->hasOne('Page', array(
      'local' => 'pageID',
      'foreign' => 'id',
      'onDelete' => 'CASCADE',
      'onUpdate' => 'CASCADE')
    );

    $this->hasOne('ElementType', array(
      'local' => 'elementType',
      'foreign' => 'id')
    );

    $this->hasMany('ElementListItem as ListItems', array(
      'local' => 'id',
      'foreign' => 'elementID')
    );

    $this->hasMany('ElementAnswer', array(
      'local' => 'id',
      'foreign' => 'elementID')
    );
  }
  
  /**
   * Find ListItems by weight
   * @returns Doctrine_Collection
   */
  public function findListItemsByWeight(){
    $q = Doctrine_Query::create()
      ->select('*')
      ->from('ElementListItem')
      ->where('elementID = ?', $this->id)
      ->orderBy('weight asc');
    return $q->execute();
  }
  
  /**
  * Get ListItem
  * @param integer $itemId
  * @return ListItem
  */
  public function getItemById($itemId){
    $key = array_search($itemId, $this->ListItems->getPrimaryKeys());
    if($key !== false){ //use === becuase 0 is returned often
      return $this->ListItems->get($key);
    }
    return false;
  }
  
  /**
   * After we save the Element make sure all of its Items
   * At some point doctrine is unable to follow the relationships deep enough
   * This method explicitly saves the members of collections with the correct id
   */
  public function postSave(){
    foreach($this->ListItems as $item){
      if($item->isModified(true)){
        $item->elementID = $this->id;
        $item->save();
      }
    }
  }
}