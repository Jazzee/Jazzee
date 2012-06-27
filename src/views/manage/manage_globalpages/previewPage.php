<?php

/**
 * manage_globalpages previewPage view
 *
 */
$class = $page->getPage()->getType()->getClass();
$this->renderElement($class::applyPageElement(), array('page' => $page, 'currentAnswerID' => false, 'applicant' => $applicant));