<?php
/**
 * lor review view
 * Review the information submitted
 * 
 */
?>
<h3>Thank you   <?php print $page->getParent()->getElementByFixedId(\Jazzee\Page\Recommenders::FID_FIRST_NAME)->getJazzeeElement()->displayValue($answer); ?>&nbsp;.  We have received your recommendation.</h3>
<h5>For our applicant's security this page will only display once.  If you wish to have a copy of this recommendation for your records you should print one now.</h5>
<br/>
<h5>
This is a recommendation from <?php print $page->getParent()->getElementByFixedId(\Jazzee\Page\Recommenders::FID_FIRST_NAME)->getJazzeeElement()->displayValue($answer); ?>&nbsp;<?php print $page->getParent()->getElementByFixedId(\Jazzee\Page\Recommenders::FID_LAST_NAME)->getJazzeeElement()->displayValue($answer); ?>
 for <?php print $applicantFullName; ?>.

</h5>
<br/><br/>
<?php
$class = $page->getType()->getClass();
$this->renderElement($class::lorReviewElement(), array('page' => $page, 'answer' => $answer));