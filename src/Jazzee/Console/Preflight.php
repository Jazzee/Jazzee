<?php
namespace Jazzee\Console;

/**
 * Preflight Check 
 * Check for all dependancies and configuration options
 *
 */
class Preflight extends \Symfony\Component\Console\Command\Command
{
    protected function configure()
    {
        $this->setName('preflight')
        ->setDescription('Preflight Check that everythig is installed and configured');
    }
    protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output){
      $error = false;
      $jazzeeConfiguration = new \Jazzee\Configuration;
      $em = $this->getHelper('em')->getEntityManager();
      $stub = new AdminStub;
      $stub->em = $em;
      $stub->config = $jazzeeConfiguration;
      //Check that the var direcotry is working
      $path = $jazzeeConfiguration->getVarPath()?$jazzeeConfiguration->getVarPath():__DIR__ . '/../../../var';
      if(!$realPath = \realpath($path) or !\is_dir($realPath) or !\is_writable($realPath)){
        if($realPath) $path = $realPath; //nicer error message if the path exists
        $error = true;
        $output->write("<error>{$path} is not writable by the webserver so we cannot use it as the 'var' directory</error>" . PHP_EOL);
      }
      
      if($jazzeeConfiguration->getStatus() == 'DEVELOPMENT'){
        $error = true;
        $output->write("<error>Jazzee is in developer mode.</error>" . PHP_EOL);
      } else {
        //Check that the cache is setup
        if(!extension_loaded('apc')){
          $error = true;
          $output->write("<error>APC is required for caching, but it it not installed.</error>" . PHP_EOL);
        }

        //Check that the schema is correct
        $validator = new \Doctrine\ORM\Tools\SchemaValidator($em);
        $errors = $validator->validateMapping();
        if($errors) {
          $error = true;
          foreach ($errors AS $className => $errorMessages) {
            $output->write("<error>The entity-class '" . $className . "' mapping is invalid:</error>" . PHP_EOL);
            foreach ($errorMessages AS $errorMessage) {
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
          $em->getConfiguration()->ensureProductionSettings();
          $em->getConnection()->connect();
        } catch (\Exception $e) {
          $error = true;
          $output->writeln('<error>' . $e->getMessage() . '</error>');
        }

        $metadatas = $em->getMetadataFactory()->getAllMetadata();
        $path = $em->getConfiguration()->getProxyDir();
        if(!is_dir($path)){
          $directory = dirname($path);
          if(\realpath($directory)) $directory = \realpath($directory);
          $base = basename($path);
          if(!\is_writable($directory)){
            $error = true;
            $output->write("<error>We do not have permission to create directory {$base} in {$directory}</error>" . PHP_EOL);
          } else {
            mkdir($path, 0777, true);
          }
        }
        if(!$error){
          $proxyDir = \realpath($path);
          if(!\is_writable($proxyDir)){
            $error = true;
            $output->write("<error>We do not have permission to write to {$proxyDir}.</error>" . PHP_EOL);
          } else {
            $em->getProxyFactory()->generateProxyClasses($metadatas, $proxyDir);          
          }
        }
        try{
          //Test the Authentication service
          $class = $jazzeeConfiguration->getAdminAuthenticationClass();
          $adminAuthentication = new $class($stub);
          if(!($adminAuthentication instanceof \Jazzee\Interfaces\AdminAuthentication)){
            $error = true;
            $output->write("<error>{$class} does not implement AdminAuthentication Interface.</error>" . PHP_EOL);
          }

          //Test the directory service
          $class = $jazzeeConfiguration->getAdminDirectoryClass();
          $adminDirectory = new $class($stub);
          if(!($adminDirectory instanceof \Jazzee\Interfaces\AdminDirectory)){
            $error = true;
            $output->write("<error>{$class} does not implement AdminDirectory Interface.</error>" . PHP_EOL);
          }
//          This isn't available until we can limit the search returned Issue #90
//          $attributes = array(
//              $jazzeeConfiguration->getLdapLastNameAttribute() => '*'
//          );
//          $results = $adminDirectory->search($attributes);
        } catch (\Jazzee\Exception $e){
          $error = true;
          $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
      }
      
      if($error) $output->write(PHP_EOL . "<error>Preflight Check Failed</error>" . PHP_EOL);
      else $output->write(PHP_EOL . "<info>Preflight Check Passed</info>" . PHP_EOL);
    }
}