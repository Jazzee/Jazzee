<?php 
/**
 * lor review view
 * Review the information submitted
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage lor
 */
?>
<h3>Thank you.  We have recieved your recommendation.</h3>
<h5>For our applicant's security this page will only display once.  If you wish to have a copy of this recommendation for your records you should print one now.</h5>
<fieldset>
  <legend>Submitted Recommendation</legend>
  <?php
  if($answer){?>
    <p>
    <?php 
    foreach($answer->getPage()->getElements() as $element){
      $value = $element->getJazzeeElement()->displayValue($answer);
      if($value){
        print '<p><strong>' . $element->getTitle() . ':</strong>&nbsp;' . $value . '</p>'; 
      }
    }
    ?>
    </p>
  <?php } ?>
</fieldset>