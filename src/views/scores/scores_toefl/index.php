<?php
/**
 * scores_toefl index view
 *
 */
$this->renderElement('form', array('form' => $form));
if (isset($results)) {?>
  <h4>Search Results</h4>
  <table>
    <caption>Your search returned (<?php print count($results); ?>) results</caption>
    <thead>
      <tr><th>Name</th><th>Birth Date</th><th>Test Date</th><th>Registration Number</th><th>Native Language</th><th>Test Type</th><th>Listening</th><th>Reading</th><th>Speaking</th><th>Writing</th><th>Essay</th><th>Total</th></tr>
    </thead>
    <tbody><?php
      foreach ($results as $toeflScore) { ?>
        <tr>
          <td><?php print $toeflScore->getLastName(); ?>, <?php print $toeflScore->getFirstName(); ?></td>
          <td><?php print $toeflScore->getBirthDate()->format('m/d/Y'); ?></td>
          <td><?php print $toeflScore->getTestDate()->format('m/d/Y'); ?></td>
          <td><?php print $toeflScore->getRegistrationNumber(); ?></td>
          <td><?php print $toeflScore->getNativeLanguage(); ?></td><?php
          if ($toeflScore->getTestType() == 'C' or $toeflScore->getTestType() == 'P') { ?>
            <td><?php print ($toeflScore->getTestType() == 'C') ? 'CBT' : 'P/B' ?></td>
            <td><?php print $toeflScore->getListening(); ?></td>
            <td><?php print $toeflScore->getReading(); ?></td>
            <td></td>
            <td><?php print $toeflScore->getWriting(); ?></td>
            <td><?php print $toeflScore->getEssay(); ?></td>
            <td><?php print $toeflScore->getTotal(); ?></td><?php
          } else if ($toeflScore->getTestType() == 'I') { ?>
            <td>IBT</td>
            <td><?php print $toeflScore->getIBTListening(); ?></td>
            <td><?php print $toeflScore->getIBTReading(); ?></td>
            <td><?php print $toeflScore->getIBTSpeaking(); ?></td>
            <td><?php print $toeflScore->getIBTWriting(); ?></td>
            <td></td>
            <td><?php print $toeflScore->getIBTTotal(); ?></td><?php
          } ?>
        </tr><?php
      } //end foreach results ?>
    </tbody>
  </table><?php
} //if results