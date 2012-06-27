<?php
/**
 * scores_gre index view
 *
 */
$this->renderElement('form', array('form' => $form));
if (isset($results)) { ?>
  <h4>Search Results</h4>
  <table>
    <caption>Your search returned (<?php print count($results); ?>) results</caption>
    <thead>
      <tr><th>Name</th><th>Birth Date</th><th>Test Date</th><th>Registration Number</th><th>Test</th><th></th><th></th><th></th><th></th></tr>
    </thead>
    <tbody><?php
      foreach ($results as $greScore) { ?>
        <tr>
          <td><?php print $greScore->getLastName(); ?>, <?php print $greScore->getFirstName(); ?></td>
          <td><?php print $greScore->getBirthDate()->format('m/d/Y'); ?></td>
          <td><?php print $greScore->getTestDate()->format('m/d/Y'); ?></td>
          <td><?php print $greScore->getRegistrationNumber(); ?></td>
          <td><?php print $greScore->getTestName(); ?></td>
          <td><?php
            if ($greScore->getScore1Type()) { ?>
              <?php print $greScore->getScore1Type(); ?> : <?php print $greScore->getScore1Converted(); ?>  <?php print $greScore->getScore1Percentile(); ?>%<?php
            } ?>
          </td>
          <td><?php
            if ($greScore->getScore2Type()) { ?>
              <?php print $greScore->getScore2Type(); ?> : <?php print $greScore->getScore2Converted(); ?>  <?php print $greScore->getScore2Percentile(); ?>%<?php
            } ?>
          </td>
          <td><?php
            if ($greScore->getScore3Type()) { ?>
              <?php print $greScore->getScore3Type(); ?> : <?php print $greScore->getScore3Converted(); ?>  <?php print $greScore->getScore3Percentile(); ?>%<?php
            } ?>
          </td>
          <td><?php
            if ($greScore->getScore4Type()) { ?>
              <?php print $greScore->getScore4Type(); ?> : <?php print $greScore->getScore4Converted(); ?>  <?php print $greScore->getScore4Percentile(); ?>%<?php
            } ?>
          </td>
        </tr><?php
      } //foreach results ?>
    </tbody>
  </table>
  <?php
} //if results
