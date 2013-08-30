<?php
namespace Jazzee\Page;

/**
 * The education page has two branches - one if the school is on the list
 * and another if it is not.  Then non-matched school can be matched by administrators 
 * if necessary
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class Education extends Standard
{
  
  /**
   * Fixed pages for the school
   */
  const PAGE_FID_NEWSCHOOL = 4;
  const ELEMENT_FID_NAME = 2;
  const ELEMENT_FID_CITY = 4;
  const ELEMENT_FID_STATE = 8;
  const ELEMENT_FID_COUNTRY = 16;
  const ELEMENT_FID_POSTALCODE = 32;
  
  /**
   * Initial form is for school search
   */
  protected function makeForm()
  {
    $form = new \Foundation\Form;
    $form->setAction($this->_controller->getActionPath());
    $field = $form->newField();
    $field->setLegend($this->_applicationPage->getTitle());
    $field->setInstructions($this->_applicationPage->getInstructions());
    $element = $field->newElement('TextInput', 'schoolSearch');
    $element->setLabel('Search for School');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $form->newButton('submit', 'Search');
    
    $form->newHiddenElement('level', 'search');
    $form->getElementByName('submit')->setValue('Search');
    return $form;
  }

  /**
   * Create the new school Form
   * 
   * @param int $schoolId
   */
  protected function newSchoolForm()
  {
    $page = $this->_applicationPage->getPage()->getChildByFixedId(self::PAGE_FID_NEWSCHOOL);
    $form = new \Foundation\Form;
    $field = $form->newField();
    $field->setLegend($page->getTitle());
    $field->setInstructions($page->getInstructions());

    foreach ($page->getElements() as $element) {
      $element->getJazzeeElement()->setController($this->_controller);
      $element->getJazzeeElement()->addToField($field);
    }
    $form->newHiddenElement('level', 'new');
    $form->newButton('submit', 'Next');
    $this->_form = $form;
  }

  /**
   * Branching Page Form
   * Replaces the form with the correct branch
   * @param array $input
   */
  protected function pageForm($input)
  {
    $form = new \Foundation\Form;
    $field = $form->newField();
    $field->setLegend($this->_applicationPage->getTitle());
    $field->setInstructions($this->_applicationPage->getInstructions());

    foreach ($this->_applicationPage->getPage()->getElements() as $element) {
      $element->getJazzeeElement()->setController($this->_controller);
      $element->getJazzeeElement()->addToField($field);
    }
    $form->newHiddenElement('level', 'complete');
    if(array_key_exists('schoolId', $input)){
      $form->newHiddenElement('schoolId', $input['schoolId']);
    }
    $arr = array(self::ELEMENT_FID_NAME,self::ELEMENT_FID_CITY,self::ELEMENT_FID_STATE,self::ELEMENT_FID_COUNTRY,self::ELEMENT_FID_POSTALCODE);
    $newSchoolPage = $this->_applicationPage->getPage()->getChildByFixedId(self::PAGE_FID_NEWSCHOOL);
    foreach($arr as $fid){
      $element = $newSchoolPage->getElementByFixedId($fid);
      $eid = 'el'.$element->getId();
      if(array_key_exists($eid, $input)){
        $form->newHiddenElement($eid, $input[$eid]);
      }
    }
    $form->newButton('submit', 'Save');
    $this->_form = $form;
  }

  /**
   * Create a form to choose a school
   * @param array $choices
   */
  protected function pickSchoolForm($choices)
  {
    $form = new \Foundation\Form;
    $field = $form->newField();
    $field->setLegend($this->_applicationPage->getTitle());
    $field->setInstructions($this->_applicationPage->getInstructions());

    $element = $field->newElement('RadioList', 'pickSchoolId');
    $element->setLabel('Choose School');
    $element->newItem(null, 'Enter a new School');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    asort($choices);
    foreach ($choices as $id => $value) {
      $element->newItem($id, $value);
    }
    $form->newHiddenElement('level', 'pick');
    $form->newButton('submit', 'Next');
    $this->_form = $form;
  }

  public function validateInput($input)
  {
    switch($input['level']){
      case 'search':
        if ($input = $this->getForm()->processInput($input)) {
          $choices = array();
          $searchTerms = $input->get('schoolSearch');
          $resultsCount = $this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\School')->getSearchCount($searchTerms);
          if($resultsCount > 50){
            $this->_form->getElementByName('schoolSearch')->addMessage('Your search returned too many results, please try again with more detail.');
          } else {
            if($resultsCount == 0){
              $this->_controller->addMessage('info', 'We were not able to find any schools that matched your search, you can search again or add a new school to our system.');
            } else {
              $schools = $this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\School')->search($searchTerms);
              foreach($schools as $school){
                $choices[$school->getId()] = $school->getName();
              }
            }
            $this->pickSchoolForm($choices);
          }

          return false;
        } else {
          $this->_controller->addMessage('error', self::ERROR_MESSAGE);
          return false;
        }
        break;
      case 'pick':
        if(!empty($input['pickSchoolId']) and $selectedSchool = $this->getSchoolById($input[ 'pickSchoolId'])){
          $this->_controller->setVar('schoolName', $selectedSchool->getName());
          $this->pageForm($input);
          $this->_form->newHiddenElement('schoolId', $selectedSchool->getId());
        } else {
          $this->newSchoolForm();
        }
        return false;
        break;
      case 'new':
        $this->newSchoolForm();
        if ($this->getForm()->processInput($input)) {
          $this->pageForm($input);
        } else {
          $this->_controller->addMessage('error', self::ERROR_MESSAGE);
        }
        return false;
        break;
      case 'complete':
        if(array_key_exists('schoolId', $input)){
          if(!$this->getSchoolById($input[ 'schoolId'])){
            $this->_form = $this->makeForm();
            $this->_controller->addMessage('error', 'There was a problem with your school selection.  You will need to search again.');
            return false;
          }
        } else {
          $this->newSchoolForm();
          if (!$this->getForm()->processInput($input)) {
            $this->_controller->addMessage('error', self::ERROR_MESSAGE);
            return false;
          }
        }
        $this->pageForm($input);
        return parent::validateInput($input);
        break;
    }
  }

  public function newAnswer($input)
  {
    if (is_null($this->_applicationPage->getMax()) or count($this->getAnswers()) < $this->_applicationPage->getMax()) {
      $answer = new \Jazzee\Entity\Answer();
      $answer->setPage($this->_applicationPage->getPage());
      foreach ($this->_applicationPage->getPage()->getElements() as $element) {
        foreach ($element->getJazzeeElement()->getElementAnswers($input->get('el' . $element->getId())) as $elementAnswer) {
          $answer->addElementAnswer($elementAnswer);
        }
      }
      $this->_applicant->addAnswer($answer);
      $schoolId = $input->get('schoolId');
      if(!is_null($schoolId) and $school = $this->getSchoolById($schoolId)){
        $answer->setSchool($school);
      } else {
        $childPage = $this->_applicationPage->getPage()->getChildByFixedId(self::PAGE_FID_NEWSCHOOL);
        $childAnswer = new \Jazzee\Entity\Answer;
        $childAnswer->setPage($childPage);
        $answer->addChild($childAnswer);

        foreach ($childPage->getElements() as $element) {
          foreach ($element->getJazzeeElement()->getElementAnswers($input->get('el' . $element->getId())) as $elementAnswer) {
            $childAnswer->addElementAnswer($elementAnswer);
          }
        }
        $this->_controller->getEntityManager()->persist($childAnswer);
      }

      $this->_form = $this->makeForm();
      $this->_form->applyDefaultValues();
      $this->_controller->getEntityManager()->persist($answer);
      
      $this->_controller->addMessage('success', 'Answer Saved Successfully');
      //flush here so the answerId will be correct when we view
      $this->_controller->getEntityManager()->flush();
    }
  }

  public function updateAnswer($input, $answerId)
  {
    if ($answer = $this->_applicant->findAnswerById($answerId)) {
      foreach ($answer->getElementAnswers() as $ea) {
        $this->_controller->getEntityManager()->remove($ea);
        $answer->getElementAnswers()->removeElement($ea);
      }
      foreach ($answer->getChildren() as $childAnswer) {
        $this->_controller->getEntityManager()->remove($childAnswer);
        $answer->getChildren()->removeElement($childAnswer);
      }
      foreach ($this->_applicationPage->getPage()->getElements() as $element) {
        foreach ($element->getJazzeeElement()->getElementAnswers($input->get('el' . $element->getId())) as $elementAnswer) {
          $answer->addElementAnswer($elementAnswer);
        }
      }
      $schoolId = $input->get('schoolId');
      if(!is_null($schoolId) and $school = $this->getSchoolById($schoolId)){
        $answer->setSchool($school);
      } else {
        $childAnswer = new \Jazzee\Entity\Answer;
        $schoolPage = $this->_applicationPage->getPage()->getChildByFixedId(self::PAGE_FID_NEWSCHOOL);
        $childAnswer->setPage($schoolPage);
        $answer->addChild($childAnswer);
        foreach ($schoolPage->getElements() as $element) {
          foreach ($element->getJazzeeElement()->getElementAnswers($input->get('el' . $element->getId())) as $elementAnswer) {
            $childAnswer->addElementAnswer($elementAnswer);
          }
        }
        $this->_controller->getEntityManager()->persist($childAnswer);
      }

      $this->_form = null;
      $this->_controller->getEntityManager()->persist($answer);
      $this->_controller->addMessage('success', 'Answer Updated Successfully');
    }
  }

  /**
   * Lets an administrator change the school selected
   * We have do duplicate the do_changeSchool method because the admin page
   * handles success and the form action path differently
   * @param integer $answerID
   * @param array $input
   */
  public function do_adminChangeSchool($answerId, array $input)
  {
    $this->checkIsAdmin();
    if ($answer = $this->_applicant->findAnswerById($answerId)) {
      $this->_form = $this->makeForm();
      if(!array_key_exists('level', $input)){
        $input['level'] = null;
      }
      switch($input['level']){
        case 'search':
          if ($input = $this->getForm()->processInput($input)) {
            $choices = array();
            $searchTerms = $input->get('schoolSearch');
            $resultsCount = $this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\School')->getSearchCount($searchTerms);
            if($resultsCount > 50){
              $this->_form->getElementByName('schoolSearch')->addMessage('Your search returned too many results, please try again with more detail.');
            } else {
              if($resultsCount == 0){
                $this->_controller->addMessage('info', 'We were not able to find any schools that matched your search, you can search again or add a new school to our system.');
              } else {
                $schools = $this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\School')->search($searchTerms);
                foreach($schools as $school){
                  $choices[$school->getId()] = $school->getName();
                }
              }
              $this->pickSchoolForm($choices);
            }
          } else {
            $this->_controller->setLayoutVar('status', 'error');
            $this->_controller->addMessage('error', self::ERROR_MESSAGE);
          }
          break;
        case 'pick':
          if(!empty($input['pickSchoolId']) and $selectedSchool = $this->getSchoolById($input[ 'pickSchoolId'])){
            $answer->setSchool($selectedSchool);
            foreach ($answer->getChildren() as $childAnswer) {
              $this->_controller->getEntityManager()->remove($childAnswer);
              $answer->getChildren()->removeElement($childAnswer);
            }
            $this->_controller->getEntityManager()->persist($answer);
            $this->_controller->addMessage('success', 'Answer Saved Successfully');
            $this->_controller->setLayoutVar('status', 'success');
          } else {
            $this->newSchoolForm();
            $this->_form->getElementByName('submit')->setValue('Save');
            if($child = $answer->getChildren()->first()){
              foreach ($child->getPage()->getElements() as $element) {
                $element->getJazzeeElement()->setController($this->_controller);
                $value = $element->getJazzeeElement()->formValue($child);
                if ($value) {
                  $this->_form->getElementByName('el' . $element->getId())->setValue($value);
                }
              }
            }
          }
          break;
        case 'new':
          $this->newSchoolForm();
          if ($filteredInput = $this->getForm()->processInput($input)) {
            $answer->removeSchool();
            $childPage = $this->_applicationPage->getPage()->getChildByFixedId(self::PAGE_FID_NEWSCHOOL);
            if($childAnswer = $answer->getChildren()->first()){
              foreach ($childAnswer->getElementAnswers() as $ea) {
                $this->_controller->getEntityManager()->remove($ea);
                $childAnswer->getElementAnswers()->removeElement($ea);
              }
            } else {
              $childAnswer = new \Jazzee\Entity\Answer;
              $childAnswer->setPage($childPage);
              $answer->addChild($childAnswer);
            }
            foreach ($childPage->getElements() as $element) {
              foreach ($element->getJazzeeElement()->getElementAnswers($filteredInput->get('el' . $element->getId())) as $elementAnswer) {
                $childAnswer->addElementAnswer($elementAnswer);
              }
            }
            $this->_controller->getEntityManager()->persist($childAnswer);
            $this->_controller->addMessage('success', 'Answer Saved Successfully');
            $this->_controller->setLayoutVar('status', 'success');
          } else {
            $this->_controller->setLayoutVar('status', 'error');
            $this->_controller->addMessage('error', self::ERROR_MESSAGE);
          }
          break;
      }
      //use getForm here to set teh antiCsrf token
      return $this->getForm();
    }
    $this->_controller->setLayoutVar('status', 'error');
  }

  /**
   * Change the school for an answer
   * @param integer $answerID
   * @param array $input
   */
  public function do_changeSchool($answerId, array $input)
  {
    if ($answer = $this->_applicant->findAnswerById($answerId)) {
      $this->_form = $this->makeForm();
      if(!array_key_exists('level', $input)){
        $input['level'] = null;
      }
      switch($input['level']){
        case 'search':
          if ($input = $this->getForm()->processInput($input)) {
            $choices = array();
            $searchTerms = $input->get('schoolSearch');
            $resultsCount = $this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\School')->getSearchCount($searchTerms);
            if($resultsCount > 50){
              $this->_form->getElementByName('schoolSearch')->addMessage('Your search returned too many results, please try again with more detail.');
            } else {
              if($resultsCount == 0){
                $this->_controller->addMessage('info', 'We were not able to find any schools that matched your search, you can search again or add a new school to our system.');
              } else {
                $schools = $this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\School')->search($searchTerms);
                foreach($schools as $school){
                  $choices[$school->getId()] = $school->getName();
                }
              }
              $this->pickSchoolForm($choices);
            }
          } else {
            $this->_controller->addMessage('error', self::ERROR_MESSAGE);
          }
          break;
        case 'pick':
          if(!empty($input['pickSchoolId']) and $selectedSchool = $this->getSchoolById($input[ 'pickSchoolId'])){
            $answer->setSchool($selectedSchool);
            foreach ($answer->getChildren() as $childAnswer) {
              $this->_controller->getEntityManager()->remove($childAnswer);
              $answer->getChildren()->removeElement($childAnswer);
            }
            $this->_controller->getEntityManager()->persist($answer);
            $this->_controller->addMessage('success', 'Answer Saved Successfully');
            $this->_controller->redirectUrl($this->_controller->getActionPath());
          } else {
            $this->newSchoolForm();
            $this->_form->getElementByName('submit')->setValue('Save');
            if($child = $answer->getChildren()->first()){
              foreach ($child->getPage()->getElements() as $element) {
                $element->getJazzeeElement()->setController($this->_controller);
                $value = $element->getJazzeeElement()->formValue($child);
                if ($value) {
                  $this->_form->getElementByName('el' . $element->getId())->setValue($value);
                }
              }
            }
          }
          break;
        case 'new':
          $this->newSchoolForm();
          if ($filteredInput = $this->getForm()->processInput($input)) {
            $answer->removeSchool();
            $childPage = $this->_applicationPage->getPage()->getChildByFixedId(self::PAGE_FID_NEWSCHOOL);
            if($childAnswer = $answer->getChildren()->first()){
              foreach ($childAnswer->getElementAnswers() as $ea) {
                $this->_controller->getEntityManager()->remove($ea);
                $childAnswer->getElementAnswers()->removeElement($ea);
              }
            } else {
              $childAnswer = new \Jazzee\Entity\Answer;
              $childAnswer->setPage($childPage);
              $answer->addChild($childAnswer);
            }
            foreach ($childPage->getElements() as $element) {
              foreach ($element->getJazzeeElement()->getElementAnswers($filteredInput->get('el' . $element->getId())) as $elementAnswer) {
                $childAnswer->addElementAnswer($elementAnswer);
              }
            }
            $this->_controller->getEntityManager()->persist($childAnswer);
            $this->_controller->addMessage('success', 'Answer Saved Successfully');
            $this->_controller->redirectUrl($this->_controller->getActionPath());
          } else {
            $this->_controller->addMessage('error', self::ERROR_MESSAGE);
          }
          break;
      }
      $this->_form->setAction($this->_controller->getActionPath() . "/do/changeSchool/{$answerId}");
    }
  }

  public function fill($answerId)
  {
    $input = array();
    if ($answer = $this->_applicant->findAnswerById($answerId)) {
      if($school = $answer->getSchool()){
        $this->_controller->setVar('schoolName', $school->getName());
        $input['schoolId'] = $school->getId();
      }

      if($child = $answer->getChildren()->first()){
        foreach ($child->getPage()->getElements() as $element) {
          $element->getJazzeeElement()->setController($this->_controller);
          $value = $element->getJazzeeElement()->formValue($child);
          if ($value) {
            $input['el' . $element->getId()] = $value;
          }
        }
      }
      $this->pageForm($input);
      foreach ($this->_applicationPage->getPage()->getElements() as $element) {
        $element->getJazzeeElement()->setController($this->_controller);
        $value = $element->getJazzeeElement()->formValue($answer);
        if ($value) {
          $this->getForm()->getElementByName('el' . $element->getId())->setValue($value);
        }
      }

      $this->getForm()->setAction($this->_controller->getActionPath() . "/edit/{$answerId}");
    }
  }

  /**
   * Create the school choice form
   */
  public function setupNewPage()
  {
    $entityManager = $this->_controller->getEntityManager();
    $types = $entityManager->getRepository('Jazzee\Entity\ElementType')->findAll();
    $elementTypes = array();
    foreach ($types as $type) {
      $elementTypes[$type->getClass()] = $type;
    };
    $standardPageType = $entityManager->getRepository('\Jazzee\Entity\PageType')->findOneBy(array('class'=>'\\Jazzee\Page\Standard'));
    $newSchool = new \Jazzee\Entity\Page();
    $newSchool->setType($standardPageType);
    $newSchool->setFixedId(self::PAGE_FID_NEWSCHOOL);
    $newSchool->setTitle('New School');
    $this->_applicationPage->getPage()->addChild($newSchool);
    $entityManager->persist($newSchool);
    
    $elements = array(
      array('fid' => self::ELEMENT_FID_NAME, 'title' => 'School Name', 'max' => 255, 'required' => true),
      array('fid' => self::ELEMENT_FID_CITY, 'title' => 'City', 'max' => 64, 'required' => true),
      array('fid' => self::ELEMENT_FID_STATE, 'title' => 'State or Province', 'max' => 64, 'required' => false),
      array('fid' => self::ELEMENT_FID_COUNTRY, 'title' => 'Country', 'max' => 64, 'required' => true),
      array('fid' => self::ELEMENT_FID_POSTALCODE, 'title' => 'Postal Code', 'max' => 10, 'required' => false)
    );
    $count = 1;
    foreach($elements as $arr){
      $element = new \Jazzee\Entity\Element;
      $element->setType($elementTypes['\Jazzee\Element\TextInput']);
      $element->setTitle($arr['title']);
      if($arr['required']){
        $element->required();
      } else {
        $element->optional();
      }
      $element->setWeight($count++);
      $element->setMax($arr['max']);
      $element->setFixedId($arr['fid']);
      $newSchool->addElement($element);
      $entityManager->persist($element);
    }

    $defaultVars = array(
      'schoolListType' => 'full',
      'partialSchoolList' => ''
    );
    foreach ($defaultVars as $name => $value) {
      $var = $this->_applicationPage->getPage()->setVar($name, $value);
      $entityManager->persist($var);
    }
    
  }

  /**
   * Check variables before they are set
   * @param string $name
   * @param string $value
   * @throws \Jazzee\Exception
   */
  public function setVar($name, $value)
  {
    switch ($name) {
      case 'schoolListType':
        if(!in_array($value, array('full', 'partial'))){
          throw new \Jazzee\Exception("{$value} is not a valid option for schoolListType");
        }
        break;
      case 'partialSchoolList':
        $value = preg_replace("/[^0-9,]+/", "", $value);
        break;
    }
    parent::setVar($name, $value);
  }

  /**
   * Format an answer array
   * @param \array $answer
   * @param \Jazzee\Entity\Page $page
   * 
   * @return array
   */
  protected function arrayAnswer(array $answer, \Jazzee\Entity\Page $page)
  {
    $elements = $answer['elements'];
    $answer['elements'] = array();
    foreach ($elements as $elementId => $elementAnswers) {
      $element = $page->getElementById($elementId);
      $answer['elements'][] = $element->getJazzeeElement()->formatApplicantArray($elementAnswers);
    }

    if(!is_null($answer['attachment'])){
      $answer['attachment'] = $this->arrayAnswerAttachment($answer['attachment'], $page);
    }

    if(count($answer['children'])){
      $child = $answer['children'][0];
      $childPage = $page->getChildById($child['page_id']);
      $childElements = $child['elements'];
      $values = array();
      foreach ($childElements as $elementId => $elementAnswers) {
        $element = $childPage->getElementById($elementId);
        $arr = $element->getJazzeeElement()->formatApplicantArray($elementAnswers);
        $values[$element->getFixedId()] = $arr['displayValue'];
      }
      $schoolName = $values[\Jazzee\Page\Education::ELEMENT_FID_NAME];
      $schoolType = 'New';
      $parts = array();
      foreach(array(self::ELEMENT_FID_CITY, self::ELEMENT_FID_STATE, self::ELEMENT_FID_COUNTRY, self::ELEMENT_FID_POSTALCODE) as $fid){
        if(array_key_exists($fid, $values)){
          $parts[] = $values[$fid];
        }
      }
      $schoolLocation = implode(' ', $parts);
    } else {
      $schoolName = $answer['school']['name'];
      $parts = array(
        $answer['school']['city'],
        $answer['school']['state'],
        $answer['school']['country'],
        $answer['school']['postalCode']
      );
      $schoolLocation = implode(' ', $parts);;
      $schoolType = 'Known';
    }
    $answer['elements'][] = array(
      'id' => 'locationSummary',
      'title' => 'School Location',
      'type' => null,
      'name' => null,
      'weight' => 0,
      'values' => array(
        array('value' => $schoolLocation, 'name' => null, 'id'=>null)
      ),
      'displayValue' => $schoolLocation
    );
    $answer['elements'][] = array(
      'id' => 'schoolName',
      'title' => 'Schoo Namel',
      'type' => null,
      'name' => null,
      'weight' => 0,
      'values' => array(
        array('value' => $schoolName, 'name' => null, 'id'=>null)
      ),
      'displayValue' => $schoolName
    );
    $answer['elements'][] = array(
      'id' => 'schoolType',
      'title' => 'School Type',
      'type' => null,
      'name' => null,
      'weight' => 0,
      'values' => array(
        array('value' => $schoolType, 'name' => null, 'id'=>null)
      ),
      'displayValue' => $schoolType
    );

    return $answer;
  }

  /**
   * Convienence method for getting the school so the entity manager does not
   * get used everywhere and if it becomed necesary this can be modified in a signle place
   * 
   * @param integer $schoolId
   * @return boolean
   */
  public function getSchoolById($schoolId)
  {
    if($schoolId != null and $school = $this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\School')->find($schoolId)){
      return $school;
    }

    return false;
  }

  /**
   * Education pages list the children of each branch
   * 
   * @return array
   */
  public function listDisplayElements()
  {
    $elements = parent::listDisplayElements();
    $weight = count($elements);
    $elements[] = new \Jazzee\Display\Element('page', 'School Name', $weight++, 'schoolName', $this->_applicationPage->getPage()->getId());
    $elements[] = new \Jazzee\Display\Element('page', 'School Type', $weight++, 'schoolType', $this->_applicationPage->getPage()->getId());
    $elements[] = new \Jazzee\Display\Element('page', 'School Location', $weight++, 'locationSummary', $this->_applicationPage->getPage()->getId());

    return $elements;
  }

  /**
   * Education pages get special CSV headers
   * @return array
   */
  public function getCsvHeaders()
  {
    $headers = parent::getCsvHeaders();
    $headers[] = 'School Name';
    $headers[] = 'School Type';
    $headers[] = 'School Location';

    return $headers;
  }

  /**
   * Education extract the school
   * @param array $pageArr
   * @param int $position
   * @return array
   */
  public function getCsvAnswer(array $pageArr, $position)
  {
    $arr = parent::getCsvAnswer($pageArr, $position);
    if (isset($pageArr['answers']) AND array_key_exists($position, $pageArr['answers'])) {
      $locationSummary = '';
      $schoolName = '';
      $schoolType = '';
      foreach($pageArr['answers'][$position]['elements'] as $element){
        if($element['id'] == 'locationSummary'){
          $locationSummary = $element['displayValue'];
        }
        if($element['id'] == 'schoolName'){
          $schoolName = $element['displayValue'];
        }
        if($element['id'] == 'schoolType'){
          $schoolType = $element['displayValue'];
        }
      }
      $arr[] = $schoolName;
      $arr[] = $schoolType;
      $arr[] = $locationSummary;
    }

    return $arr;
  }

  /**
   * Convert an answer to an xml element and add school information
   * Since school data is stored either in the School entity or else in a child answer
   * we combine these two
   * @param \DomDocument $dom
   * @param \Jazzee\Entity\Answer $answer
   * @param integer $version the XML version to create
   * @return \DomElement
   */
  protected function xmlAnswer(\DomDocument $dom, \Jazzee\Entity\Answer $answer, $version)
  {
    $answerXml = $dom->createElement('answer');
    $answerXml->setAttribute('answerId', $answer->getId());
    $answerXml->setAttribute('uniqueId', $answer->getUniqueId());
    $answerXml->setAttribute('updatedAt', $answer->getUpdatedAt()->format('c'));
    $answerXml->setAttribute('pageStatus', $answer->getPageStatus());
    $answerXml->setAttribute('publicStatus', ($answer->getPublicStatus() ? $answer->getPublicStatus()->getName() : ''));
    $answerXml->setAttribute('privateStatus', ($answer->getPrivateStatus() ? $answer->getPrivateStatus()->getName() : ''));
    foreach ($answer->getPage()->getElements() as $element) {
      $element->getJazzeeElement()->setController($this->_controller);
      if ($element->getJazzeeElement() instanceof \Jazzee\Interfaces\XmlElement) {
        $answerXml->appendChild($element->getJazzeeElement()->getXmlAnswer($dom, $answer, $version));
      }
    }
    $attachment = $dom->createElement('attachment');
    if ($answer->getAttachment()) {
      $attachment->appendChild($dom->createCDATASection(base64_encode($answer->getAttachment()->getAttachment())));
    }
    $answerXml->appendChild($attachment);

    $schoolXml = $dom->createElement('school');
    if ($school = $answer->getSchool()) {
      $schoolXml->setAttribute('type', 'known');
      $schoolXml->setAttribute('code', htmlentities($school->getCode(), ENT_COMPAT, 'utf-8'));
      $eXml = $dom->createElement('name');
      $eXml->appendChild($dom->createCDATASection($school->getName()));
      $schoolXml->appendChild($eXml);
      $eXml = $dom->createElement('city');
      $eXml->appendChild($dom->createCDATASection($school->getCity()));
      $schoolXml->appendChild($eXml);
      $eXml = $dom->createElement('state');
      $eXml->appendChild($dom->createCDATASection($school->getState()));
      $schoolXml->appendChild($eXml);
      $eXml = $dom->createElement('country');
      $eXml->appendChild($dom->createCDATASection($school->getCountry()));
      $schoolXml->appendChild($eXml);
      $eXml = $dom->createElement('postalCode');
      $eXml->appendChild($dom->createCDATASection($school->getPostalCode()));
      $schoolXml->appendChild($eXml);
    } else {
      $schoolXml->setAttribute('type', 'new');
      $newSchoolElements = array(
        self::ELEMENT_FID_NAME => 'name',
        self::ELEMENT_FID_CITY => 'city',
        self::ELEMENT_FID_STATE => 'state',
        self::ELEMENT_FID_COUNTRY => 'country',
        self::ELEMENT_FID_POSTALCODE => 'postalCode'
      );
      foreach($newSchoolElements as $fid => $name){
        $element = $this->_applicationPage->getPage()->getChildByFixedId(self::PAGE_FID_NEWSCHOOL)->getElementByFixedId($fid);
        $element->getJazzeeElement()->setController($this->_controller);
        if ($element->getJazzeeElement() instanceof \Jazzee\Interfaces\XmlElement) {
          $eXml = $dom->createElement($name);
          $eXml->appendChild($dom->createCDATASection($element->getJazzeeElement()->displayValue($answer->getChildren()->first())));
          $schoolXml->appendChild($eXml);
        }
      }
    }
    $answerXml->appendChild($schoolXml);

    return $answerXml;
  }

  /**
   * Add school information to the PDF
   * @param \Jazzee\ApplicantPDF $pdf
   */
  public function renderPdfSection(\Jazzee\ApplicantPDF $pdf)
  {
    $pdf->addText($this->_applicationPage->getTitle() . "\n", 'h3');
    if($this->getStatus() == \Jazzee\Interfaces\Page::SKIPPED){
      $pdf->addText("Applicant Skipped this page.\n", 'p');
    } else {
      foreach ($this->getAnswers() as $answer) {
        $type = '';
        $name = '';
        $location = '';
        
        if ($school = $answer->getSchool()) {
          $type = 'Known';
          $name = $school->getName();
          $location = $school->getLocationSummary();
        } else {
          $type = 'New';
          $element = $this->_applicationPage->getPage()->getChildByFixedId(self::PAGE_FID_NEWSCHOOL)->getElementByFixedId(self::ELEMENT_FID_NAME);
          $element->getJazzeeElement()->setController($this->_controller);
          $name = $element->getJazzeeElement()->displayValue($answer->getChildren()->first());
          $element = $this->_applicationPage->getPage()->getChildByFixedId(self::PAGE_FID_NEWSCHOOL)->getElementByFixedId(self::ELEMENT_FID_CITY);
          $element->getJazzeeElement()->setController($this->_controller);
          $location .= $element->getJazzeeElement()->displayValue($answer->getChildren()->first()) . ' ';
          $element = $this->_applicationPage->getPage()->getChildByFixedId(self::PAGE_FID_NEWSCHOOL)->getElementByFixedId(self::ELEMENT_FID_STATE);
          $element->getJazzeeElement()->setController($this->_controller);
          $location .= $element->getJazzeeElement()->displayValue($answer->getChildren()->first()) . ' ';
          $element = $this->_applicationPage->getPage()->getChildByFixedId(self::PAGE_FID_NEWSCHOOL)->getElementByFixedId(self::ELEMENT_FID_COUNTRY);
          $element->getJazzeeElement()->setController($this->_controller);
          $location .= $element->getJazzeeElement()->displayValue($answer->getChildren()->first()) . ' ';
          $element = $this->_applicationPage->getPage()->getChildByFixedId(self::PAGE_FID_NEWSCHOOL)->getElementByFixedId(self::ELEMENT_FID_POSTALCODE);
          $element->getJazzeeElement()->setController($this->_controller);
          $location .= $element->getJazzeeElement()->displayValue($answer->getChildren()->first()) . ' ';
        }
        $pdf->addText("Type: ", 'b');
        $pdf->addText("{$type}\n", 'p');
        $pdf->addText("School: ", 'b');
        $pdf->addText("{$name}\n", 'p');
        $pdf->addText("Location: ", 'b');
        $pdf->addText("{$location}\n", 'p');
        $this->renderPdfAnswer($pdf, $this->_applicationPage->getPage(), $answer);
        $pdf->addText("\n", 'p');
      }
    }
  }

  /**
   * Render a single answer in the PDF
   * @param \Jazzee\ApplicantPDF $pdf
   * @param \Jazzee\Entity\Page $page
   * @param \Jazzee\Entity\Answer $answer
   */
  protected function renderPdfAnswerFromArray(\Jazzee\Entity\Page $page, \Jazzee\ApplicantPDF $pdf, array $answerData)
  {
    $locationSummary = '';
    $schoolName = '';
    $schoolType = '';
    foreach($answerData['elements'] as $element){
      if($element['id'] == 'locationSummary'){
        $locationSummary = $element['displayValue'];
      }
      if($element['id'] == 'schoolName'){
        $schoolName = $element['displayValue'];
      }
      if($element['id'] == 'schoolType'){
        $schoolType = $element['displayValue'];
      }
    }
    $pdf->addText("Type: ", 'b');
    $pdf->addText("{$schoolType}\n", 'p');
    $pdf->addText("School: ", 'b');
    $pdf->addText("{$schoolName}\n", 'p');
    $pdf->addText("Location: ", 'b');
    $pdf->addText("{$locationSummary}\n", 'p');
    parent::renderPdfAnswerFromArray($page, $pdf, $answerData);
  }

  public static function applyPageElement()
  {
    return 'Education-apply_page';
  }

  public static function pageBuilderScriptPath()
  {
    return 'resource/scripts/page_types/JazzeePageEducation.js';
  }

  public static function applicantsSingleElement()
  {
    return 'Education-applicants_single';
  }

  public static function applyStatusElement()
  {
    return 'Education-apply_status';
  }

}