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
<p>For our applicant's security this page will only display once.  If you wish to have a copy of this recommendation for your records you should make one now.</p>
<?php
if($answer){?>
  <h5>Submitted Recommendation</h5>
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
<?php }