<?php
/**
 * lor Standard review view
 */
?>

<fieldset>
  <legend>Submitted Recommendation</legend>
  <p>
    <?php
    $child = $answer->getChildren()->first();
    foreach ($child->getPage()->getElements() as $element) {
      $element->getJazzeeElement()->setController($this->controller);
      $value = $element->getJazzeeElement()->displayValue($child);
      if ($value) {
        print '<p><strong>' . $element->getTitle() . ':</strong>&nbsp;' . $value . '</p>';
      }
    }
    ?>
  </p>
</fieldset>