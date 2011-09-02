<?php 
/**
 * StandardPage Answer Element
 * @package jazzee
 * @subpackage apply
 */
$answers = $page->getjazzeePage()->getAnswers();
$totalAnswers = count($answers);
$completedAnswers = 0;
foreach($answers as $answer)if($answer->isLocked()) $completedAnswers++;
?>
<p>
<?php 
if(is_null($page->getMax())){
  if($totalAnswers >= $page->getMin()) print 'You may add as many additional recommenders as you wish to this page, but it is not required.';
  else print 'You have added ' . $totalAnswers . ' of the ' . $page->getMin() . ' required recommenders on this page.';
} else {
  if($page->getMax() - $totalAnswers == 0) print 'You cannot add any more recommenders to this page.';
  else if($totalAnswers >= $page->getMin()) print 'You may add an additional ' . ($page->getMax() - $totalAnswers) . ' recommenders on this page, but it is not required.';
  else print 'You have added ' . $totalAnswers . ' of the ' . $page->getMin() . ' required recommenders on this page.';
}
?>
<?php 
if($completedAnswers < $page->getMin() and $completedAnswers < $totalAnswers) print '&nbsp;' . ($page->getMin() - $completedAnswers) . ' recommenders(s) still must be invited to submit their recommendations in order for your application to be complete.';
else if($completedAnswers < $totalAnswers) print '&nbsp;You have not invited ' . ($totalAnswers - $completedAnswers) . ' of your recommenders.';
?>
</p>