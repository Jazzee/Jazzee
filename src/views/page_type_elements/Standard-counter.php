<?php 
/**
 * StandardPage Answer Element
 * @package jazzee
 * @subpackage apply
 */
if(is_null($page->getMax())){ //infinite answers page
  if(count($page->getjazzeePage()->getAnswers()) > $page->getMax()){?>
    <p>You may add as many additional answers as you wish to this page, but it is not required.</p>
  <?php } else { ?>
    <p>You have completed <?php print count($page->getjazzeePage()->getAnswers()) ?> of the <?php print $page->getMin() ?> required answers on this page.</p>
  <?php }?>
<?php } else if($page->getMax() > 1){
 if(count($page->getjazzeePage()->getAnswers()) >= $page->getMin()){?>
    <p>You may complete and additional <?php print ($page->getMax() - count($page->getJazzeePage()->getAnswers())) ?> answers on this page, but it is not required.</p>
  <?php } else { ?>
    <p>You have completed <?php print count($page->getjazzeePage()->getAnswers()) ?> of the <?php print $page->getMin() ?> required answers on this page.</p>
  <?php } 
}?>