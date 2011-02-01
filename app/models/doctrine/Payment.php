<?php

/**
 * Payment
 * @package    jazzee
 * @subpackage orm
 * @author     Jon Johnson <jon.johnson@ucsf.edu>
 */
class Payment extends BasePayment{
  public function pending(){
    $this->status = 'pending';
  }
}