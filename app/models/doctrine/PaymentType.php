<?php
/**
 * PaymentType
 * 
 * @property string $name
 * @property string $class
 * @property Doctrine_Collection $Variables
 * @property Doctrine_Collection $Payment
 * 
 * @package    jazzee
 * @subpackage orm
 */
class PaymentType extends Doctrine_Record{
  /**
   * @see Doctrine_Record_Abstract::setTableDefinition()
   */
  public function setTableDefinition(){
    $this->setTableName('payment_type');
    $this->hasColumn('name', 'string', 255, array(
      'type' => 'string',
      'notnull' => true,
      'unique' => true,
      'length' => '255',
     ));
    $this->hasColumn('class', 'string', 255, array(
      'type' => 'string',
      'notnull' => true,
      'unique' => true,
      'length' => '255',
     ));
  }

  /**
   * @see Doctrine_Record::setUp()
   */
  public function setUp(){
    parent::setUp();
    $this->hasMany('PaymentTypeVariable as Variables', array(
      'local' => 'id',
      'foreign' => 'paymentTypeID')
    );

    $this->hasMany('Payment', array(
     'local' => 'id',
     'foreign' => 'paymentTypeID')
    );
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
   * Set Variable
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
}