<?php
/**
 * lor index view
 * 
 */
?>
<h4>Recommendation for: <?php print $applicantName; ?></h4>
<h5>Deadline: <?php print $deadline; ?></h5>
<p>This recommendation was requested from
  <?php print $page->getParent()->getElementByFixedId(\Jazzee\Page\Recommenders::FID_FIRST_NAME)->getJazzeeElement()->displayValue($answer); ?>&nbsp;
  <?php print $page->getParent()->getElementByFixedId(\Jazzee\Page\Recommenders::FID_LAST_NAME)->getJazzeeElement()->displayValue($answer); ?>.
  If this is not you, please do not complete this recommendation</p>
<p><?php print $applicantName . ' <em>' . ($page->getParent()->getElementByFixedId(\Jazzee\Page\Recommenders::FID_WAIVE_RIGHT)->getJazzeeElement()->displayValue($answer) == 'Yes' ? 'has' : 'has not') . '</em>'; ?> waived their right to view this letter after the application is reviewed.</p>

<?php
$class = $page->getType()->getClass();
$this->renderElement($class::lorPageElement(), array('page' => $page, 'answer' => $answer));