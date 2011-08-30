<?php 
/**
 * setup_users index view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage setup
 */
$this->renderElement('form', array('form'=>$form));?>
<?php if($results){ ?>
  <h5>Results</h5>
  <ul>
  <?php foreach($results as $result){ ?>
  <li><?php print $result['lastName'] . ', ' . $result['firstName'] . ' (' . $result['emailAddress'] . ')' ?> 
    <?php if($this->controller->checkIsAllowed('setup_users', 'new')){ ?>
    (<a href='<?php print $this->path('setup/users/new/') . $result['userName']?>'>Add User</a>)
    <?php }?>
  </li>
  <?php }?>
  </ul>
<?php } ?>

<?php if(empty($users)){?>
<p>There are no users in this program</p>
<?php } else { ?>
  <table>
    <caption>Program Users</caption>
    <thead>
      <tr>
        <th>Name</th>
        <?php foreach($roles as $role){?>
          <th><?php print $role->getName();?></th>
        <?php } ?>
        <th>Tools</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($users as $user){?>
        <tr>
          <td><?php print $user->getLastName(); ?>, <?php print $user->getFirstName();?>(<?php print $user->getUniqueName();?>)</td>
          <?php foreach($roles as $role){?>
              <td><?php if($user->hasRole($role)) print 'x'?></td>
          <?php } ?>
          <td>
            <?php if($this->controller->checkIsAllowed('setup_users', 'edit')) { ?>
              <a href='<?php print $this->controller->path('setup/users/edit/' . $user->getId());?>'>Edit Program Roles</a> | 
            <?php } ?>
            <?php if($this->controller->checkIsAllowed('setup_users', 'remove')) { ?>
              <a href='<?php print $this->controller->path('setup/users/remove/' . $user->getId());?>'>Remove from program</a>
            <?php } ?>
          </td>
      <?php } ?>
    </tbody>
  </table>
<?php } ?>