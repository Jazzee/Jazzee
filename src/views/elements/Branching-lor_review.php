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
    $branch = $child->getChildren()->first();
    print '<p><strong>' . $page->getVar('branchingElementLabel') . ':</strong>&nbsp' . $branch->getPage()->getTitle() . '</p>';

    foreach ($branch->getPage()->getElements() as $element) {
      $element->getJazzeeElement()->setController($this->controller);
      $value = $element->getJazzeeElement()->displayValue($branch);
      if ($value) {
        print '<p><strong>' . $element->getTitle() . ':</strong>&nbsp;' . $value . '</p>';
      }
    }
    ?>
  </p>
</fieldset>