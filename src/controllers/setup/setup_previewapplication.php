<?php

/**
 * Preview the application
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class SetupPreviewapplicationController extends \Jazzee\AdminController
{

  const MENU = 'Setup';
  const TITLE = 'Preview Application';
  const PATH = 'setup/previewapplication';
  const ACTION_INDEX = 'View Preview Links';
  const ACTION_NEW = 'Create New Preview';
  const ACTION_DELETE = 'Delete Existing Preview';

  /**
   * Create a demo application to preview
   */
  public function actionIndex()
  {
    $pattern = $this->getPathString('*');
    $existing = array();
    foreach (glob($pattern) as $path) {
      $stats = stat($path);
      $arr = array();
      $key = $this->getKeyFromPath($path);
      $arr['key'] = $key;
      $arr['link'] = $this->applyPath('preview/start/' . $key);
      $arr['lastAccessed'] = date('m-d-Y h:i', $stats['mtime']);

      $existing[] = $arr;
    }


    $this->setVar('existing', $existing);
  }

  /**
   * Create a new preview
   */
  public function actionNew()
  {
    $prefix = substr(md5(mt_rand() * mt_rand()), rand(0, 24), rand(6, 8));
    $key = \uniqid($prefix);
    $path = $this->getPathString($this->_program->getShortName() . $this->_cycle->getName() . '-' . $key);

    $doctrineConfig = $this->_em->getConfiguration();
    $connectionParams = array(
      'driver' => 'pdo_sqlite',
      'path' => $path
    );
    $previewEntityManager = \Doctrine\ORM\EntityManager::create($connectionParams, $doctrineConfig);
    $this->buildTemporaryDatabase($previewEntityManager);
    $this->createPreviewApplication($previewEntityManager);

    $this->addMessage('success', 'Created preview');
    $this->redirectPath('setup/previewapplication');
  }

  /**
   * Create a new preview
   */
  public function actionDelete($key)
  {
    $path = $this->getPathString($key);
    if (is_writable($path)) {
      unlink($path);
      $this->addMessage('success', 'Deleted preview');
      $this->redirectPath('setup/previewapplication');
    } else {
      $this->addMessage('error', 'Could not delete preview');
      $this->redirectPath('setup/previewapplication');
    }
  }

  /**
   * Setup the temporary database with data from the real one
   * @param \Doctrine\ORM\EntityManager $em
   */
  protected function buildTemporaryDatabase(\Doctrine\ORM\EntityManager $em)
  {
    $classes = $this->_em->getMetadataFactory()->getAllMetadata();
    $tool = new \Doctrine\ORM\Tools\SchemaTool($em);
    $tool->dropDatabase();
    $tool->createSchema($classes);

    foreach ($this->_em->getRepository('\Jazzee\Entity\PageType')->findAll() as $type) {
      $newType = new \Jazzee\Entity\PageType;
      $newType->setClass($type->getClass());
      $newType->setName($type->getName());
      $em->persist($newType);
    }
    foreach ($this->_em->getRepository('\Jazzee\Entity\ElementType')->findAll() as $type) {
      $newType = new \Jazzee\Entity\ElementType;
      $newType->setClass($type->getClass());
      $newType->setName($type->getName());
      $em->persist($newType);
    }
    foreach ($this->_em->getRepository('\Jazzee\Entity\PaymentType')->findAll() as $type) {
      $newType = new \Jazzee\Entity\PaymentType;
      $newType->setClass($type->getClass());
      $newType->setName($type->getName());
      if ($type->isExpired()) {
        $newType->expire();
      }
      foreach($type->getVariables() as $var){
        $newVar = new Jazzee\Entity\PaymentTypeVariable;
        $newVar->setName($var->getName());
        $newVar->setValue($var->getValue());
        $newVar->setType($newType);
        $em->persist($newVar);
      }
      $em->persist($newType);
    }
    $em->flush();
  }

  /**
   * Create a preview application
   * @param \Doctrine\ORM\EntityManager $em
   * @return \Jazzee\Entity\Application
   */
  protected function createPreviewApplication(\Doctrine\ORM\EntityManager $em)
  {
    $newApplication = new \Jazzee\Entity\Application;
    $properties = array(
      'contactName',
      'contactEmail',
      'welcome',
      'admitLetter',
      'denyLetter',
      'statusIncompleteText',
      'statusNoDecisionText',
      'statusAdmitText',
      'statusDenyText',
      'statusAcceptText',
      'statusDeclineText'
    );
    foreach ($properties as $name) {
      $set = 'set' . ucfirst($name);
      $get = 'get' . ucfirst($name);
      $newApplication->$set($this->_application->$get());
    }
    $timeProperties = array('open', 'close', 'begin');
    foreach ($timeProperties as $name) {
      $set = 'set' . ucfirst($name);
      $get = 'get' . ucfirst($name);
      $newApplication->$set($this->_application->$get()->format('c'));
    }
    $newApplication->publish(true);
    $newApplication->visible();

    $program = new \Jazzee\Entity\Program;
    $program->setName($this->_program->getName());
    $program->setShortName($this->_program->getShortName());
    $em->persist($program);
    $newApplication->setProgram($program);

    $cycle = new \Jazzee\Entity\Cycle;
    $cycle->setName($this->_cycle->getName());
    $cycle->setStart('yesterday');
    $cycle->setEnd('next year');
    $em->persist($cycle);
    $newApplication->setCycle($cycle);


    foreach ($this->_application->getApplicationPages() as $applicationPage) {
      $newPage = $this->copyPage($em, $applicationPage->getPage());
      $newApplicationPage = new \Jazzee\Entity\ApplicationPage;
      $newApplicationPage->setApplication($newApplication);
      $newApplicationPage->setPage($newPage);
      $newApplicationPage->setWeight($applicationPage->getWeight());
      $newApplicationPage->setKind($applicationPage->getKind());
      $newApplicationPage->setTitle($applicationPage->getTitle());
      $newApplicationPage->setMin($applicationPage->getMin());
      $newApplicationPage->setMax($applicationPage->getMax());
      $newApplicationPage->setInstructions($applicationPage->getInstructions());
      $newApplicationPage->setLeadingText($applicationPage->getLeadingText());
      $newApplicationPage->setTrailingText($applicationPage->getTrailingText());
      if ($applicationPage->isRequired()) {
        $newApplicationPage->required();
      } else {
        $newApplicationPage->optional();
      }
      if ($applicationPage->showAnswerStatus()) {
        $newApplicationPage->showAnswerStatus();
      } else {
        $newApplicationPage->hideAnswerStatus();
      }
      $em->persist($newApplicationPage);
    }
    $em->persist($newApplication);
    $em->flush();
    return $newApplication;
  }

  /**
   * Copy a Page
   * @param \Doctrine\ORM\EntityManager $em
   * @param \Jazzee\Entity\Page $page
   * @return \Jazzee\Entity\Page
   */
  public function copyPage(\Doctrine\ORM\EntityManager $em, \Jazzee\Entity\Page $page)
  {
    $newPage = new \Jazzee\Entity\Page;

    $newPage->setTitle($page->getTitle());
    $newPage->setMax($page->getMax());
    $newPage->setInstructions($page->getInstructions());
    $newPage->setLeadingText($page->getLeadingText());
    $newPage->setTrailingText($page->getTrailingText());

    $newPage->setType($em->getRepository('\Jazzee\Entity\PageType')->findOneByClass($page->getType()->getClass()));
    if ($page->isGlobal()) {
      $newPage->makeGlobal();
    } else {
      $newPage->notGlobal();
    }
    if ($page->isRequired()) {
      $newPage->required();
    } else {
      $newPage->optional();
    }
    foreach ($page->getChildren() as $child) {
      $newPage->addChild($this->copyPage($em, $child));
    }
    foreach ($page->getVariables() as $variable) {
      $newPage->setVar($variable->getName(), $variable->getValue());
    }
    foreach ($newPage->getVariables() as $variable) {
      $em->persist($variable);
    }

    foreach ($page->getElements() as $element) {
      $newElement = new \Jazzee\Entity\Element;
      $newElement->setType($em->getRepository('\Jazzee\Entity\ElementType')->findOneByClass($element->getType()->getClass()));
      $newElement->setWeight($element->getWeight());
      $newElement->setFixedId($element->getFixedId());
      $newElement->setTitle($element->getTitle());
      $newElement->setMax($element->getMax());
      $newElement->setMin($element->getMin());
      $newElement->setInstructions($element->getInstructions());
      $newElement->setDefaultValue($element->getDefaultValue());
      $newElement->setFormat($element->getFormat());
      if ($element->isRequired()) {
        $newElement->required();
      } else {
        $newElement->optional();
      }
      foreach ($element->getListItems() as $item) {
        $newItem = new \Jazzee\Entity\ElementListItem;
        $newItem->setValue($item->getValue());
        $newItem->setWeight($item->getWeight());
        $newElement->addItem($newItem);
        $em->persist($newItem);
      }
      $em->persist($newElement);
      $newPage->addElement($newElement);
    }
    $em->persist($newPage);
    return $newPage;
  }

  /**
   * Get the preview path string
   * @param string $key
   * @return string
   */
  protected function getPathString($key)
  {
    $pathPrefix = $this->_config->getVarPath() . '/tmp/';
    $pathSuffix = '.previewdb.db';
    return $pathPrefix . $key . $pathSuffix;
  }

  /**
   * Get the preview path string
   * @param string $path
   * @return string
   */
  protected function getKeyFromPath($path)
  {
    $pathPrefix = $this->_config->getVarPath() . '/tmp/';
    $pathSuffix = '.previewdb.db';
    return substr($path, strlen($pathPrefix), -strlen($pathSuffix));
  }

}