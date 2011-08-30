<?php 
/**
 * manage_users index view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage manage
 */
$this->renderElement('form', array('form'=>$form));?>
<?php if($results){ ?>
  <h5>Results</h5>
  <ul>
  <?php foreach($results as $result){ ?>
  <li><?php print $result['lastName'] . ', ' . $result['firstName'] . ' (' . $result['emailAddress'] . ')' ?> 
    <?php if($this->controller->checkIsAllowed('manage_users', 'new')){ ?>
    (<a href='<?php print $this->path('manage/users/new/') . $result['userName']?>'>Add User</a>)
    <?php }?>
  </li>
  <?php }?>
  </ul>
<?php } ?>

<?php if(empty($users)){?>
<p>There are no users in the system</p>
<?php } else { ?>
  <table>
    <caption>System Users</caption>
    <thead>
      <tr>
        <th>Name</th>
        <th>API Key</th>
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
          <td><?php print $user->getApiKey();?></td>
          <?php foreach($roles as $role){?>
              <td><?php if($user->hasRole($role)) print 'x'?></td>
          <?php } ?>
          <td>
            <?php if($this->controller->checkIsAllowed('manage_users', 'edit')) { ?>
              <a href='<?php print $this->controller->path('manage/users/edit/' . $user->getId());?>'>Edit Roles</a> | 
            <?php } ?>
            <?php if($this->controller->checkIsAllowed('manage_users', 'refreshUser')) { ?>
              <a href='<?php print $this->controller->path('manage/users/refreshUser/' . $user->getId());?>'>Refresh Directory</a> | 
            <?php } ?>
            <?php if($this->controller->checkIsAllowed('manage_users', 'resetApiKey')) { ?>
              <a href='<?php print $this->controller->path('manage/users/resetApiKey/' . $user->getId());?>'>Reset API Key</a> | 
            <?php } ?>
            <?php if($this->controller->checkIsAllowed('manage_users', 'remove')) { ?>
              <a href='<?php print $this->controller->path('manage/users/remove/' . $user->getId());?>'>Remove</a>
            <?php } ?>
          </td>
      <?php } ?>
    </tbody>
  </table>
<?php } ?>