<?php
/**
 * Payment
 * @property integer $applicantID
 * @property integer $paymentTypeID
 * @property double $amount
 * @property enum $status
 * @property Applicant $Applicant
 * @property PaymentType $PaymentType
 * @property Doctrine_Collection $Variables
 * 
 * @package    jazzee
 * @subpackage orm
 */
class Payment extends Doctrine_Record{
  /**
   * @see Doctrine_Record_Abstract::setTableDefinition()
   */
  public function setTableDefinition(){
    $this->setTableName('payment');
    $this->hasColumn('applicantID', 'integer', null, array(
      'type' => 'integer',
     ));
    $this->hasColumn('paymentTypeID', 'integer', null, array(
      'type' => 'integer',
     ));
    $this->hasColumn('amount', 'double', null, array(
      'type' => 'double',
     ));
    $this->hasColumn('status', 'enum', null, array(
      'type' => 'enum',
      'values' => array(
        0 => 'pending',
        1 => 'settled',
        2 => 'rejected',
        3 => 'refunded',
      ),
     ));
  }

  /**
   * @see Doctrine_Record::setUp()
   */
  public function setUp(){
    parent::setUp();
    $this->hasOne('Applicant', array(
      'local' => 'applicantID',
      'foreign' => 'id',
      'onUpdate' => 'CASCADE')
    );

    $this->hasOne('PaymentType', array(
      'local' => 'paymentTypeID',
      'foreign' => 'id',
      'onUpdate' => 'CASCADE')
    );

    $this->hasMany('PaymentVariable as Variables', array(
      'local' => 'id',
      'foreign' => 'paymentID')
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
  
  /**
   * Set a payment as pending
   */
  public function pending(){
    $this->status = 'pending';
  }
  
  /**
   * Set a payment as rejected
   */
  public function rejected(){
    $this->status = 'rejected';
  }
}