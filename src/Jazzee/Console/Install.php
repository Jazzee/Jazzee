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
        ->setDefinition(array(
            new \Symfony\Component\Console\Input\InputOption(
                'dump-sql', null, \Symfony\Component\Console\Input\InputOption::VALUE_NONE,
                'Instead of try to apply generated SQLs into EntityManager Storage Connection, output them.'
            )
        ))
        ->setHelp(<<<EOT
'Installs a new jazzee dataabse.'
EOT
        );
    }
    protected function executeSchemaCommand(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output, \Doctrine\ORM\Tools\SchemaTool $schemaTool, array $metadatas){
        if ($input->getOption('dump-sql') === true) {
            $sqls = $schemaTool->getCreateSchemaSql($metadatas);
            $output->write(implode(';' . PHP_EOL, $sqls) . PHP_EOL);
        } else {
            $sm = $this->getHelper('em')->getEntityManager()->getConnection()->getSchemaManager();
            $tables = $sm->listTableNames();
            if(!empty($tables)){
              $output->write('<error>ATTENTION: You are attempting to create tables on a database that is not empty.  Use --dump-sql to output the sql file and override this restriction.</error>' . PHP_EOL . PHP_EOL);
              exit(1);
            }
            $output->write('Creating database schema and installing default components...' . PHP_EOL);
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
            
        }
    }
}