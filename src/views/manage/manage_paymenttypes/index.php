<?php
/**
 * manage_paymenttypes index view
 *
 */
if ($paymentTypes) {?>
  <h5>Current Payment Types:</h5>
  <ul><?php
    foreach ($paymentTypes as $type) { ?>
      <li><?php print $type->getName();
        if ($this->controller->checkIsAllowed('manage_paymenttypes', 'edit')) {?>
          (<a href='<?php print $this->path('manage/paymenttypes/edit/') . $type->getId() ?>'>Edit</a>)<?php
        }
        if (!$type->isExpired() and $this->controller->checkIsAllowed('manage_paymenttypes', 'expire')) { ?>
          (<a href='<?php print $this->path('manage/paymenttypes/expire/') . $type->getId() ?>'>Expire</a>)<?php
        }
        if ($type->isExpired() and $this->controller->checkIsAllowed('manage_paymenttypes', 'unExpire')) { ?>
          (<a href='<?php print $this->path('manage/paymenttypes/unExpire/') . $type->getId() ?>'>Un-Expire</a>)<?php
        }?>
      </li><?php
    }?>
  </ul><?php
}
if ($this->controller->checkIsAllowed('manage_paymenttypes', 'new')) { ?>
  <p><a href='<?php print $this->path('manage/paymenttypes/new') ?>'>Add a New Payment Type</a></p><?php
}