<?php
namespace Jazzee\Console;

/**
 * Install a new database
 *
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage console
 */
class Install extends \Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand
{
    /**
     * @see Console\Command\Command
     */
    protected function configure()
    {
        $this
        ->setName('install')
        ->setDescription('Install the database')
        ->setHelp(<<<EOT
'Installs a new jazzee dataabse and default components.'
EOT
        );
    }
    protected function executeSchemaCommand(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output, \Doctrine\ORM\Tools\SchemaTool $schemaTool, array $metadatas){
      $sm = $this->getHelper('em')->getEntityManager()->getConnection()->getSchemaManager();
      $tables = $sm->listTableNames();
      if(!empty($tables)){
        $output->write('<error>ATTENTION: You are attempting to install jazzee on a database that is not empty..</error>' . PHP_EOL . PHP_EOL);
        exit(1);
      }
      $output->write('<comment>Creating database schema and installing default components...</comment>' . PHP_EOL);
      $schemaTool->createSchema($metadatas);
      $output->write('<info>Database schema created successfully</info>' . PHP_EOL);
      $pageTypes = array(
          '\Jazzee\Page\Branching' => 'Branching',
          '\Jazzee\Page\ETSMatch' => 'ETS Score Matching',
          '\Jazzee\Page\Lock' => 'Lock Application',
          '\Jazzee\Page\Payment' => 'Payment',
          '\Jazzee\Page\Recommenders' => 'Recommenders',
          '\Jazzee\Page\Standard' => 'Standard',
          '\Jazzee\Page\Text' => 'Plain Text'
      );
      foreach($pageTypes as $class => $name){
        $pageType = new \Jazzee\Entity\PageType();
        $pageType->setName($name);
        $pageType->setClass($class);
        $this->getHelper('em')->getEntityManager()->persist($pageType);
      }
      $this->getHelper('em')->getEntityManager()->flush();
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
          '\Jazzee\Element\SelectList' => 'Dropdown List',
          '\Jazzee\Element\ShortDate' => 'Short Date',
          '\Jazzee\Element\TextInput' => 'Single Line Text',
          '\Jazzee\Element\Textarea' => 'Text Area'
      );
      foreach($elementTypes as $class => $name){
        $elementType = new \Jazzee\Entity\ElementType();
        $elementType->setName($name);
        $elementType->setClass($class);
        $this->getHelper('em')->getEntityManager()->persist($elementType);
      }
      $this->getHelper('em')->getEntityManager()->flush();
      $output->write('<info>Default Element types added</info>' . PHP_EOL);

      $em = $this->getHelper('em')->getEntityManager();
      $role = new \Jazzee\Entity\Role();
      $role->makeGlobal();
      $em->persist($role);
      $role->setName('Administrator');
      \Foundation\VC\Config::addControllerPath(__DIR__ . '/../../controllers/');
      foreach(array('admin','applicants','manage','scores','setup') as $path){
        $path = \realPath(__DIR__ . '/../../controllers/' . $path);
        \Foundation\VC\Config::addControllerPath($path . '/');
        //scan the directory but drop the relative paths
        foreach(array_diff(scandir($path), array('.','..')) as $fileName){
          $controller = basename($fileName, '.php');
          \Foundation\VC\Config::includeController($controller);
          $class = \Foundation\VC\Config::getControllerClassName($controller);
          $arr = array('name'=> $controller, 'actions'=>array());
          foreach(get_class_methods($class) as $method){
            if(substr($method, 0, 6) == 'action'){
              $constant = 'ACTION_' . strtoupper(substr($method, 6));
              if(defined("{$class}::{$constant}")){
                $ra = new \Jazzee\Entity\RoleAction();
                $ra->setController($controller);
                $ra->setAction(substr($method, 6));
                $ra->setRole($role);
                $em->persist($ra);
              }
            }
          }
        }
      }
      $em->flush();
      $output->write("<info>Administrator role created</info>" . PHP_EOL);
        
    }
}