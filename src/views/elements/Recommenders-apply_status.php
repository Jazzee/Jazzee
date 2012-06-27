<?php
/**
 * StandardPage Answer Status Element
 */
?>
<table>
  <thead>
    <tr>
      <th>Recommender</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody><?php
    if ($answers = $page->getJazzeePage()->getAnswers()) {
      foreach ($answers as $answer) { ?>
        <tr>
          <td>
            <?php print $page->getPage()->getElementByFixedId(\Jazzee\Page\Recommenders::FID_FIRST_NAME)->getJazzeeElement()->displayValue($answer); ?>
            <?php print $page->getPage()->getElementByFixedId(\Jazzee\Page\Recommenders::FID_LAST_NAME)->getJazzeeElement()->displayValue($answer); ?><br />
            <?php print $page->getPage()->getElementByFixedId(\Jazzee\Page\Recommenders::FID_INSTITUTION)->getJazzeeElement()->displayValue($answer); ?><br />
            <?php print $page->getPage()->getElementByFixedId(\Jazzee\Page\Recommenders::FID_EMAIL)->getJazzeeElement()->displayValue($answer); ?>
          </td>
          <td><?php
            if ($child = $answer->getChildren()->first()) { ?>
              <strong>Status:</strong> This recommendation was received on <?php print $child->getUpdatedAt()->format('l F jS Y g:ia');
            } else if ($answer->isLocked()) {?>
              <strong>Status:</strong> This recommendation had not been received.<br />
              <strong>Invitation Sent:</strong> <?php print $answer->getUpdatedAt()->format('l F jS Y g:ia');
              if ($answer->getUpdatedAt()->diff(new DateTime('now'))->days < $answer->getPage()->getVar('lorWaitDays')) { ?>
                You will be able to send the invitation to your recommender again in <?php print ($answer->getPage()->getVar('lorWaitDays') - $answer->getUpdatedAt()->diff(new DateTime('now'))->days); ?> days.<?php
              } else { ?>
                <br /><a class='invite' href='<?php print $this->controller->getActionPath(); ?>/do/sendEmail/<?php print $answer->getPage()->getId() ?>/<?php print $answer->getId() ?>'>Send Reminder Email</a><?php
              }
            } else { ?>
              <strong>Status: </strong> No Invitation Sent.  You can <a class='invite' href='<?php print $this->controller->getActionPath(); ?>/do/sendEmail/<?php print $answer->getPage()->getId() ?>/<?php print $answer->getId() ?>'>Send the invitation</a><?php
            } ?>
          </td>
        </tr><?php
      }
    }?>
  </tbody>
</table>