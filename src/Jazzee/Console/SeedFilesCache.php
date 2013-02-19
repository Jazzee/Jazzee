<?php

namespace Jazzee\Console;

/**
 * Sets up the first user
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class SeedFilesCache extends \Symfony\Component\Console\Command\Command
{

  /**
   * @see Console\Command\Command
   */
  protected function configure()
  {
    $this->setName('seed-files-cache')->setDescription('Seed the files cache with data from the files table.  Its a good idea to run this anytime you dump yoru cache with a few months worth of files.');
    $this->addArgument('lastAccess', \Symfony\Component\Console\Input\InputArgument::REQUIRED, 'The date to start caching from');
    $this->setHelp('Seed the file cache.');
  }

  protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
  {
    $start = time();
    $jazzeeConfiguration = new \Jazzee\Configuration;
    $entityManager = $this->getHelper('em')->getEntityManager();
    $fileStore = new \Jazzee\FileStore($entityManager, $jazzeeConfiguration->getVarPath() . '/cache');
    $lastAccessedSince = new \DateTime($input->getArgument('lastAccess'));
    $total = $entityManager->getRepository('Jazzee\Entity\File')->seedFileCache($fileStore, $lastAccessedSince);
    $time = round(time() - $start);
    $output->write("<info>{$total} files cached in {$time} seconds</info>" . PHP_EOL);
  }

}