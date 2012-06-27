<?php

namespace Jazzee\Console;

/**
 * Update the database to match the schema
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class Update extends \Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand
{

  protected function configure()
  {
    $this
            ->setName('update')
            ->setDescription('Check for updates to the database schema and provide options for updating.')
            ->setDefinition(array(
              new \Symfony\Component\Console\Input\InputOption(
                  'complete', null, \Symfony\Component\Console\Input\InputOption::VALUE_NONE,
                  'If defined, all assets of the database which are not relevant to the current metadata will be dropped.'
              ),
              new \Symfony\Component\Console\Input\InputOption(
                  'dump-sql', null, \Symfony\Component\Console\Input\InputOption::VALUE_NONE,
                  'Instead of trying to apply differences, output them.'
              ),
              new \Symfony\Component\Console\Input\InputOption(
                  'force', null, \Symfony\Component\Console\Input\InputOption::VALUE_NONE,
                  "Force a database update.  Probably not safe for production systems."
              ),
            ))
            ->setHelp('Process the differences between the database schema and the current set of Jazzee Entities.  Developers should use --complete to drop anything'
                . 'that is no longer relevant.  In testing environments the --force command can be used to apply the changed directly.  For productions environments'
                . 'the --dump-sql option should be used to output the changes for manual consideration before they are applied.'
            );
  }

  protected function executeSchemaCommand(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output, \Doctrine\ORM\Tools\SchemaTool $schemaTool, array $metadatas)
  {
    // Defining if update is complete or not (--complete not defined means $saveMode = true)
    $saveMode = ($input->getOption('complete') !== true);

    if ($input->getOption('dump-sql') === true) {
      $sqls = $schemaTool->getUpdateSchemaSql($metadatas, $saveMode);
      $output->write(implode(';' . PHP_EOL, $sqls) . PHP_EOL);
    } else if ($input->getOption('force') === true) {
      $output->write('Updating database schema...' . PHP_EOL);
      $schemaTool->updateSchema($metadatas, $saveMode);
      $output->write('Database schema updated successfully!' . PHP_EOL);
    } else {
      $output->write('ATTENTION: This operation should not be executed in a production environment.' . PHP_EOL);
      $output->write('Use the incremental update to detect changes during development and use' . PHP_EOL);
      $output->write('this SQL DDL to manually update your database in production.' . PHP_EOL . PHP_EOL);

      $sqls = $schemaTool->getUpdateSchemaSql($metadatas, $saveMode);

      if (count($sqls)) {
        $output->write('Schema-Tool would execute ' . count($sqls) . ' queries to update the database.' . PHP_EOL);
        $output->write('Please run the operation with --force to execute these queries or use --dump-sql to see them.' . PHP_EOL);
      } else {
        $output->write('Nothing to update. The database is in sync with the current entity metadata.' . PHP_EOL);
      }
    }
  }

}