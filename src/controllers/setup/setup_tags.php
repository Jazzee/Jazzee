<?php

/**
 * Manage Tags for the current cycle
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class SetupTagsController extends \Jazzee\AdminController
{

  const MENU = 'Setup';
  const TITLE = 'Tags';
  const PATH = 'setup/tags';
  const ACTION_INDEX = 'View All Tags';
  const ACTION_EDIT = 'Edit Tag';
  const ACTION_REMOVE = 'Remove Tag';

  /**
   * View the tags in a cycle
   */
  public function actionIndex()
  {
    $this->setVar('tags', $this->_em->getRepository('Jazzee\Entity\Tag')->findByApplication($this->_application));
  }

  /**
   * Remove a tag from all applicants and add a new tag in its place
   * @param $tagId
   */
  public function actionEdit($tagId)
  {
    if($tag = $this->_em->getRepository('Jazzee\Entity\Tag')->find($tagId)){
      $applicants = $this->_em->getRepository('Jazzee\Entity\Applicant')->findTaggedByApplication($this->_application, $tag);

      $form = new \Foundation\Form();
      $form->setCSRFToken($this->getCSRFToken());
      $form->setAction($this->path("setup/tags/edit/{$tagId}"));
      $field = $form->newField();
      $field->setLegend('Change "' . $tag->getTitle() . '" Tag for ' . count($applicants) . ' applicants');

      $element = $field->newElement('TextInput', 'title');
      $element->setLabel('New Tag');
      $element->setValue($tag->getTitle());
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
      $applications = $this->_em->getRepository('\Jazzee\Entity\Application')->findByProgram($this->_program);

      $form->newButton('submit', 'Change Tag');
      if ($input = $form->processInput($this->post)) {
        $newTag = $this->_em->getRepository('\Jazzee\Entity\Tag')->findOneBy(array('title' => $input->get('title')));
        if (!$newTag) {
          $newTag = new \Jazzee\Entity\Tag();
          $newTag->setTitle($input->get('title'));
          $this->_em->persist($newTag);
        }
        foreach($applicants as $applicant){
          $applicant->removeTag($tag);
          $applicant->addTag($newTag);
          $this->_em->persist($applicant);
        }
        $this->addMessage('success', 'Changed tag for ' . count($applicants) . ' applicants');
        $this->redirectPath('setup/tags');
      }
      $this->setVar('form', $form);
    }

  }

  /**
   * Remove a tag from all applicants
   * @param $tagId
   */
  public function actionRemove($tagId)
  {
    if($tag = $this->_em->getRepository('Jazzee\Entity\Tag')->find($tagId)){
      $applicants = $this->_em->getRepository('Jazzee\Entity\Applicant')->findTaggedByApplication($this->_application, $tag);
      foreach($applicants as $applicant){
        $applicant->removeTag($tag);
        $this->_em->persist($applicant);
      }
      $this->addMessage('success', 'Removed tag for ' . count($applicants) . ' applicants');
      $this->redirectPath('setup/tags');
    }
  }

}