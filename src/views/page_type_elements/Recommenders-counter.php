<?php 
/**
 * StandardPage Answer Element
 * @package jazzee
 * @subpackage apply
 */
$answers = $page->getjazzeePage()->getAnswers();
$completedAnswers = 0;
foreach($answers as $answer)if($answer->isLocked()) $completedAnswers++;

if(is_null($page->getMax())){ //infinite answers page
  if($completedAnswers > $page->getMax()){?>
    <p>You may add as many additional recommenders as you wish, but it is not required.</p>
  <?php } else { ?>
    <p>You have invited <?php print $completedAnswers ?> recommenders of the <?php print $page->getMin() ?> required.</p>
  <?php }?>
<?php } else if($page->getMax() > 1){
 if($completedAnswers >= $page->getMin()){?>
    <p>You may invite an additional <?php print ($page->getMax() - $completedAnswers) ?> recommenders, but it is not required.</p>
  <?php } else { ?>
    <p>You have invited <?php print $completedAnswers ?> recommenders of the <?php print $page->getMin() ?> required.</p>
  <?php } 
}?>