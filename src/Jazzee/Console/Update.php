<?php

namespace Jazzee\Console;

/**
 * 
 * Simplified version of \Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand
 * Forces all steps
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class Update extends \Doctrine\DBAL\Migrations\Tools\Console\Command\AbstractCommand
{
  protected function configure()
  {
      $this->setName('update');
      $this->setDescription('Execute a migration to the latest available version.');
      $this->setHelp('This is a simplified version of the full migration command.  If you wish to have more control over the migration process you should run ./doctrine migration:migrate instead.');
      parent::configure();
  }

  public function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
  {
    $configuration = $this->getMigrationConfiguration($input, $output);
    $migration = new \Doctrine\DBAL\Migrations\Migration($configuration);
    $this->outputHeader($configuration, $output);

    $noInteraction = $input->getOption('no-interaction') ? true : false;

    $executedMigrations = $configuration->getMigratedVersions();
    $availableMigrations = $configuration->getAvailableVersions();
    $executedUnavailableMigrations = array_diff($executedMigrations, $availableMigrations);

    if($executedUnavailableMigrations) {
      $output->writeln(sprintf('<error>WARNING! You have %s previously executed migrations in the database that are not registered migrations.  You will need to use the migrations tool directly to fix this.</error>', count($executedUnavailableMigrations)));
      return 1;
    }
    $noInteraction = $input->getOption('no-interaction') ? true : false;
    if (!$noInteraction) {
      $confirmation = $this->getHelper('dialog')->askConfirmation($output, '<question>This migration may result in lost data, you should test it first.  Are you sure you want to continue? (y/n)</question>', false);
      if (!$confirmation) {
        $output->writeln('<error>Migration cancelled!</error>');
        return 1;
      }
    }
    $sql = $migration->migrate(null, false);
    if (!$sql) {
      $output->writeln('<comment>No migrations to execute.</comment>');
    }      
  }
}