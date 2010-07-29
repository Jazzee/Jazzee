<?php
/**
 * View for navigation
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage navigation
 */
?>
<div class='<?php echo $navigation->class ?>'>
  <ol><?php
  foreach($navigation->getMenus() as $menu){
    if($menu->hasLink()){
      echo '<li>';
      $this->renderElement('menu', array('menu'=>$menu));
      echo '</li>';
    }
  }
  ?></ol>
</div>