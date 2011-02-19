<?php

/**
 * PaymentVariable
 * 
 * @property integer $paymentID
 * @property string $name
 * @property string $value
 * @property Payment $Payment
 * 
 * @package    jazzee
 * @subpackage orm
 */
class PaymentVariable extends Doctrine_Record{
  /**
   * @see Doctrine_Record_Abstract::setTableDefinition()
   */
  public function setTableDefinition(){
    $this->setTableName('payment_variable');
    $this->hasColumn('paymentID', 'integer', null, array(
      'type' => 'integer',
     ));
    $this->hasColumn('name', 'string', 255, array(
      'type' => 'string',
      'length' => '255',
     ));
    $this->hasColumn('value', 'string', 3000, array(
      'type' => 'string',
      'length' => '3000',
     ));


    $this->index('payemnt_variable', array(
      'fields' => array(
        0 => 'paymentId',
        1 => 'name',
      ),
      'type' => 'unique',
     ));
  }

  /**
   * @see Doctrine_Record::setUp()
   */
  public function setUp(){
    parent::setUp();
    $this->hasOne('Payment', array(
      'local' => 'paymentID',
      'foreign' => 'id',
      'onUpdate' => 'CASCADE')
    );
  }
}