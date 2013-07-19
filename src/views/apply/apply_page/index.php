<?php
/**
 * apply_page view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
$deadline = $applicant->getDeadline();
$layoutContentTop = "<p class='deadline";
if ($deadline->diff(new DateTime('today'))->days < 7) {
  $layoutContentTop .= ' approaching-deadline';
}
$layoutContentTop .= "'>Application Deadline: " . $deadline->format('m/d/Y g:ia T') . '</p>';

$layoutContentTop .= '<p class="links">';
$layoutContentTop .= '<a href="' . $this->controller->applyPath('account') . '">My Account</a>';
$layoutContentTop .= '<a href="' . $this->controller->applyPath('support') . '">Support</a>';
if ($count = $applicant->unreadMessageCount()) {
  $layoutContentTop .= '<sup class="count">' . $count . '</sup>';
}
$layoutContentTop .= '<a href="' . $this->controller->applyPath('applicant/logout') . '">Log Out</a></p>';
$this->controller->setLayoutVar('layoutContentTop', $layoutContentTop);
$class = $page->getPage()->getType()->getClass();
$this->renderElement($class::applyPageElement(), array_merge($this->data, array('page' => $page)));
