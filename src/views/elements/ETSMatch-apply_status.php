<?php
/**
 * StandardPage Answer Status Element
 *
 */
?>
<table>
  <thead>
    <tr>
      <th>Scores</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody><?php
    if ($answers = $page->getJazzeePage()->getAnswers()) {
      foreach ($answers as $answer) { ?>
        <tr>
          <td>
            <?php print $page->getPage()->getElementByFixedId(\Jazzee\Page\ETSMatch::FID_TEST_TYPE)->getJazzeeElement()->displayValue($answer); ?><br />
            <?php print $page->getPage()->getElementByFixedId(\Jazzee\Page\ETSMatch::FID_REGISTRATION_NUMBER)->getJazzeeElement()->displayValue($answer); ?><br />
            <?php print $page->getPage()->getElementByFixedId(\Jazzee\Page\ETSMatch::FID_TEST_DATE)->getJazzeeElement()->displayValue($answer); ?>
          </td>
          <td><?php
            if ($answer->getPublicStatus()) { ?>
              <br />Status: <?php print $answer->getPublicStatus()->getName(); ?> <br /> <?php
            } ?>
            Score Status:<?php
            if ($answer->getMatchedScore()) { ?>
              Score received for test taken on <?php print $answer->getMatchedScore()->getTestDate()->format('F jS Y') ?>.<?php
            } else { ?>
              This score has not been received from ETS.<?php
            } ?>
          </td>
        </tr><?php
      }
    }?>
  </tbody>
</table>