<?php 
/**
 * lor Standard review view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage lor
 */
?>

<fieldset>
  <legend>Submitted Recommendation</legend>
  <p>
  <?php 
  $child = $answer->getChildren()->first();
  $branch = $child->getChildren()->first();
  print '<p><strong>' . $page->getVar('branchingElementLabel') . ':</strong>&nbsp' . $branch->getPage()->getTitle() . '</p>';
  
  foreach($branch->getPage()->getElements() as $element){
    $element->getJazzeeElement()->setController($this->controller);
    $value = $element->getJazzeeElement()->displayValue($branch);
    if($value){
      print '<p><strong>' . $element->getTitle() . ':</strong>&nbsp;' . $value . '</p>'; 
    }
  }
  ?>
  </p>
</fieldset>