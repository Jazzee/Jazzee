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
            $output->write('Creating database schema...' . PHP_EOL);
            $schemaTool->createSchema($metadatas);
            $output->write('Database schema created successfully!' . PHP_EOL);
        }
    }
}