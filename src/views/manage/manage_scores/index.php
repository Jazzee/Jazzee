<?php
/**
 * manage_scores index view
 *
 */
if (isset($form)) {
  $this->renderElement('form', array('form' => $form));
}
if ($this->controller->checkIsAllowed('manage_scores', 'prune')) { ?>
  <a href='<?php print $this->path('manage/scores/prune/');?>'>Remove Old Scores</a><?php
}?>
<table><caption>Score Statistics</caption>
  <thead><tr><th>Score Type</th><th>Total in the system</th><th>Matched to applicant</th><th>Entered by applicants and not in the system</th></tr></thead>
  <tbody>
    <tr><td>GRE</td><td><?php print $greCount ?></td><td><?php print $greMatchedCount ?></td><td><?php print $greUnmatchedCount ?></td></tr>
    <tr><td>TOEFL</td><td><?php print $toeflCount ?></td><td><?php print $toeflMatchedCount ?></td><td><?php print $toeflUnmatchedCount ?></td></tr>
  </tbody>
</table>

<h4>List of GRE Cycles</h4>
<ul><?php
  foreach ($greCycles as $cycle) { ?>
    <li><?php print $cycle; ?></li><?php
  } ?>
</ul>