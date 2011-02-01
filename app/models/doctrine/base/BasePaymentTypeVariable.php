<?php

/**
 * BasePaymentTypeVariable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $paymentTypeId
 * @property string $name
 * @property string $value
 * @property PaymentType $PaymentType
 * 
 * @package    jazzee
 * @subpackage orm
 */
abstract class BasePaymentTypeVariable extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('payment_type_variable');
        $this->hasColumn('paymentTypeId', 'integer', null, array(
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


        $this->index('payemntType_variable', array(
             'fields' => 
             array(
              0 => 'paymentTypeId',
              1 => 'name',
             ),
             'type' => 'unique',
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('PaymentType', array(
             'local' => 'paymentTypeID',
             'foreign' => 'id'));
    }
}