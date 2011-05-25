<?php 
/**
 * manage_paymenttypes index view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage manage
 */
if($paymentTypes): ?>
  <h5>Current Payment Types:</h5>
  <ul>
  <?php foreach($paymentTypes as $arr): ?>
  <li><?php print $arr['name'] ?>
    <?php if($this->controller->checkIsAllowed('manage_paymenttypes', 'edit')): ?>
      (<a href='<?php print $this->path('manage/paymenttypes/edit/') . $arr['id']?>'>Edit</a>)
    <?php endif;?>
    
  </li>
  <?php endforeach;?>
  </ul>
<?php endif; ?>

<?php if($this->controller->checkIsAllowed('manage_paymenttypes', 'new')): ?>
  <p><a href='<?php print $this->path('manage/paymenttypes/new')?>'>Add a New Payment Type</a></p> 
<?php endif;?>