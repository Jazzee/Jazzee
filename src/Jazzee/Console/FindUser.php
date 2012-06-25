<?php

namespace Jazzee\Console;

/**
 * Find a user in the directory
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class FindUser extends \Symfony\Component\Console\Command\Command
{

  protected function configure()
  {
    $this->setName('find-user');
    $this->setDescription('Find a user in the directory.');
    $this->setHelp('Find a user in the directory.  Search for a user in the directory so they can be added with add-user.');
    $this->addOption('firstName', 'f', \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Users first name');
    $this->addOption('lastName', 'l', \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Users last name');
  }

  protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
  {
    $jazzeeConfiguration = new \Jazzee\Configuration;
    $entityManager = $this->getHelper('em')->getEntityManager();
    $stub = new AdminStub;
    $stub->em = $entityManager;
    $stub->config = $jazzeeConfiguration;
    $class = $jazzeeConfiguration->getAdminDirectoryClass();
    $directory = new $class($stub);

    if (!$input->getOption('firstName') and !$input->getOption('lastName')) {
      $output->write('<error>You must specify at least one search term (firstName, lastName)</error>' . PHP_EOL);
    } else {
      $results = $directory->search($input->getOption('firstName'), $input->getOption('lastName'));
      $output->write('<info>Search returned ' . count($results) . ' results</info>' . PHP_EOL);
      if (count($results) > 50) {
        $output->write('<comment>Displaying the first 50 results</comment>' . PHP_EOL);
        $chunks = array_chunk($results, 50);
        $results = $chunks[0];
      }
      foreach ($results as $arr) {
        $output->write("<info>{$arr['lastName']}, {$arr['firstName']} ({$arr['emailAddress']}) has user name <comment>{$arr['userName']}</comment></info>" . PHP_EOL);
      }
    }
  }

}