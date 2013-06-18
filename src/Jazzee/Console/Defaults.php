<?php

namespace Jazzee\Console;

/**
 * Install a new database
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class Defaults extends \Symfony\Component\Console\Command\Command
{

  /**
   * @see Console\Command\Command
   * @SuppressWarnings(PHPMD.ExitExpression)
   */
  protected function configure()
  {
    $this
      ->setName('defaults')
      ->setDescription('Installs default components')
      ->setHelp('Create all of the necessary default pages,elements,roles,etc');
  }

  /**
   * @SuppressWarnings(PHPMD.UnusedFormalParameter)
   * @SuppressWarnings(PHPMD.ExitExpression)
   */
  protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
  {
    $entityManager = $this->getHelper('em')->getEntityManager();
    $output->write('<comment>Installing default components...</comment>' . PHP_EOL);
    $pageTypes = array(
      '\Jazzee\Page\Branching' => 'Branching',
      '\Jazzee\Page\ETSMatch' => 'ETS Score Matching',
      '\Jazzee\Page\Education' => 'Education',
      '\Jazzee\Page\ExternalId' => 'External ID',
      '\Jazzee\Page\Lock' => 'Lock Application',
      '\Jazzee\Page\Payment' => 'Payment',
      '\Jazzee\Page\QASAddress' => 'Address Verification QAS',
      '\Jazzee\Page\Recommenders' => 'Recommenders',
      '\Jazzee\Page\Standard' => 'Standard',
      '\Jazzee\Page\Text' => 'Plain Text'
    );
    foreach ($pageTypes as $class => $name) {
      $pageType = new \Jazzee\Entity\PageType();
      $pageType->setName($name);
      $pageType->setClass($class);
      $entityManager->persist($pageType);
    }
    $entityManager->flush();
    $output->write('<info>Default Page types added</info>' . PHP_EOL);

    $elementTypes = array(
      '\Jazzee\Element\CheckboxList' => 'Checkboxes',
      '\Jazzee\Element\Date' => 'Date',
      '\Jazzee\Element\EmailAddress' => 'Email Address',
      '\Jazzee\Element\EncryptedTextInput' => 'Encrypted Text Input',
      '\Jazzee\Element\PDFFileInput' => 'PDF Upload',
      '\Jazzee\Element\Phonenumber' => 'Phone Number',
      '\Jazzee\Element\RadioList' => 'Radio Buttons',
      '\Jazzee\Element\RankingList' => 'Rank Order Dropdown',
      '\Jazzee\Element\SearchList' => 'Search',
      '\Jazzee\Element\SelectList' => 'Dropdown List',
      '\Jazzee\Element\ShortDate' => 'Short Date',
      '\Jazzee\Element\TextInput' => 'Single Line Text',
      '\Jazzee\Element\Textarea' => 'Text Area',
      '\Jazzee\Element\USSocialSecurityNumber' => 'US Social Security Number',
    );
    foreach ($elementTypes as $class => $name) {
      $elementType = new \Jazzee\Entity\ElementType();
      $elementType->setName($name);
      $elementType->setClass($class);
      $entityManager->persist($elementType);
    }
    $entityManager->flush();
    $output->write('<info>Default Element types added</info>' . PHP_EOL);

    $role = new \Jazzee\Entity\Role();
    $role->makeGlobal();
    $role->setName('Administrator');
    $entityManager->persist($role);
    \Foundation\VC\Config::addControllerPath(__DIR__ . '/../../controllers/');
    foreach (array('admin', 'applicants', 'manage', 'scores', 'setup') as $path) {
      $path = \realpath(__DIR__ . '/../../controllers/' . $path);
      \Foundation\VC\Config::addControllerPath($path . '/');
      //scan the directory but drop the relative paths
      foreach (array_diff(scandir($path), array('.', '..')) as $fileName) {
        $controller = basename($fileName, '.php');
        \Foundation\VC\Config::includeController($controller);
        $class = \Foundation\VC\Config::getControllerClassName($controller);
        foreach (get_class_methods($class) as $method) {
          if (substr($method, 0, 6) == 'action') {
            $constant = 'ACTION_' . strtoupper(substr($method, 6));
            if (defined("{$class}::{$constant}")) {
              $roleAction = new \Jazzee\Entity\RoleAction();
              $roleAction->setController($controller);
              $roleAction->setAction(substr($method, 6));
              $roleAction->setRole($role);
              $entityManager->persist($roleAction);
            }
          }
        }
      }
    }
    $entityManager->flush();
    $output->write("<info>Administrator role created</info>" . PHP_EOL);
  }

}