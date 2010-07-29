<?php
/**
 * View for a menu
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage navigation
 */
?>
<?php echo $menu->title ?>
<ol>
<?php
foreach($menu->getLinks() as $link){
  echo '<li';
  if($link->current){
    echo " class='current'";
  }
  echo '>';
  $this->renderElement('link', array('link'=>$link));
  echo '</li>';
}
?>
</ol>
