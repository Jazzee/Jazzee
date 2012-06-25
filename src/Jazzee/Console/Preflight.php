<?php

namespace Jazzee\Console;

/**
 * Preflight Check
 * Check for all dependancies and configuration options
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class Preflight extends \Symfony\Component\Console\Command\Command
{

  protected function configure()
  {
    $this->setName('preflight')
            ->setDescription('Preflight Check that everythig is installed and configured');
  }

  /**
   *
   * @SuppressWarnings(PHPMD.UnusedFormalParameter)
   * @param \Symfony\Component\Console\Input\InputInterface $input
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   */
  protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
  {
    $error = false;
    $jazzeeConfiguration = new \Jazzee\Configuration;
    $entityManager = $this->getHelper('em')->getEntityManager();
    $stub = new AdminStub;
    $stub->em = $entityManager;
    $stub->config = $jazzeeConfiguration;

    $requiredExtensions = array(
      'ldap',
      'apc',
      'curl',
      'json',
      'imagick',
      'mbstring',
      'xml',
      'dom'
    );
    $missing = array();
    foreach ($requiredExtensions as $re) {
      if (!extension_loaded($re)) {
        $missing[] = $re;
      }
    }
    if (count($missing)) {
      $error = true;
      foreach ($missing as $m) {
        $output->write("<error>The PHP {$m} extension is required and it is not installed.</error>" . PHP_EOL);
      }
    }

    //Check that the var direcotry is working
    $path = $jazzeeConfiguration->getVarPath() ? $jazzeeConfiguration->getVarPath() : __DIR__ . '/../../../var';
    if (!$realPath = \realpath($path) or !\is_dir($realPath)) {
      if ($realPath) {
        $path = $realPath; //nicer error message if the path exists
      }
      $error = true;
      $output->write("<error>{$path} is does not exist so we cannot use it as the 'var' directory</error>" . PHP_EOL);
    }
    $perms = \substr(\sprintf('%o', \fileperms($realPath)), -4);
    $owner = \fileowner($realPath);
    $group = \filegroup($realPath);
    if (function_exists('posix_getpwuid')) {
      $arr = posix_getpwuid($owner);
      $owner = $arr['name'];
    }
    if (function_exists('posix_getgrgid')) {
      $arr = posix_getgrgid($group);
      $group = $arr['name'];
    }
    $output->write("<comment>{$realPath} is owned by {$owner}:{$group} and has permissions {$perms}.  Ensure your webserver can write to that directory</comment>" . PHP_EOL);

    //Check that the schema is correct
    $validator = new \Doctrine\ORM\Tools\SchemaValidator($entityManager);
    $errors = $validator->validateMapping();
    if ($errors) {
      $error = true;
      foreach ($errors as $className => $errorMessages) {
        $output->write("<error>The entity-class '" . $className . "' mapping is invalid:</error>" . PHP_EOL);
        foreach ($errorMessages as $errorMessage) {
          $output->write('* ' . $errorMessage . PHP_EOL);
        }
        $output->write(PHP_EOL);
      }
    }
    if (!$validator->schemaInSyncWithMetadata()) {
      $error = true;
      $output->write('<error>The database schema is not correct.  Run "setup update" to fix this.</error>' . PHP_EOL);
    }
    try {
      $entityManager->getConfiguration()->ensureProductionSettings();
      $entityManager->getConnection()->connect();
    } catch (\Exception $e) {
      $error = true;
      $output->writeln('<error>' . $e->getMessage() . '</error>');
    }

    if ($jazzeeConfiguration->getStatus() == 'DEVELOPMENT') {
      $error = true;
      $output->write("<error>Jazzee is in developer mode.</error>" . PHP_EOL);
    } else {
      //Check that the cache is setup
      if (!extension_loaded('apc')) {
        $error = true;
        $output->write("<error>APC is required for caching, but it it not installed.</error>" . PHP_EOL);
      }


      $metadatas = $entityManager->getMetadataFactory()->getAllMetadata();
      $path = $entityManager->getConfiguration()->getProxyDir();
      if (!is_dir($path)) {
        $directory = dirname($path);
        if (\realpath($directory)) {
          $directory = \realpath($directory);
        }
        $base = basename($path);
        if (!\is_writable($directory)) {
          $error = true;
          $output->write("<error>We do not have permission to create directory {$base} in {$directory}</error>" . PHP_EOL);
        } else {
          mkdir($path, 0777, true);
        }
      }
      if (!$error) {
        $proxyDir = \realpath($path);
        if (!\is_writable($proxyDir)) {
          $error = true;
          $output->write("<error>We do not have permission to write to {$proxyDir}.</error>" . PHP_EOL);
        } else {
          $entityManager->getProxyFactory()->generateProxyClasses($metadatas, $proxyDir);
        }
      }
      if (
              !apc_clear_cache() ||
              !apc_clear_cache('user') ||
              !apc_clear_cache('opcode')) {
        $output->write("<error>Error Clearning APC Cache</error>" . PHP_EOL);
      }
    }

    try {
      //Test the Authentication service
      $class = $jazzeeConfiguration->getAdminAuthenticationClass();
      $adminAuthentication = new $class($stub);
      if (!($adminAuthentication instanceof \Jazzee\Interfaces\AdminAuthentication)) {
        $error = true;
        $output->write("<error>{$class} does not implement AdminAuthentication Interface.</error>" . PHP_EOL);
      }

      //Test the directory service
      $class = $jazzeeConfiguration->getAdminDirectoryClass();
      $adminDirectory = new $class($stub);
      if (!($adminDirectory instanceof \Jazzee\Interfaces\AdminDirectory)) {
        $error = true;
        $output->write("<error>{$class} does not implement AdminDirectory Interface.</error>" . PHP_EOL);
      }
//          This isn't available until we can limit the search returned Issue #90
//          $attributes = array(
//              $jazzeeConfiguration->getLdapLastNameAttribute() => '*'
//          );
//          $results = $adminDirectory->search($attributes);
    } catch (\Jazzee\Exception $e) {
      $error = true;
      $output->writeln('<error>' . $e->getMessage() . '</error>');
    }

    if (!class_exists('HTMLPurifier')) {
      $error = true;
      $output->write("<error>HTML Purifier is required and it is not installed.</error>" . PHP_EOL);
    }
    if (!class_exists('\Doctrine\ORM\Version')) {
      $error = true;
      $output->write("<error>Doctrine ORM is required and it is not installed.</error>" . PHP_EOL);
    }
    if (!class_exists('\Monolog\Logger')) {
      $error = true;
      $output->write("<error>Monolog is required and it is not installed.</error>" . PHP_EOL);
    }

    if ($error) {
      $output->write(PHP_EOL . "<error>Preflight Check Failed</error>" . PHP_EOL);
    } else {
      $output->write(PHP_EOL . "<info>Preflight Check Passed</info>" . PHP_EOL);
    }
  }

}