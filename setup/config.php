<?php
namespace Jazzee\Console;

try{
  //If the composer autoloader hasn't been loaded then load it here
  //We do it this way in case Jazzee has been built as a composer app into another app
  if (!class_exists('Composer\\Autoload\\ClassLoader', false)) {
      require __DIR__ . '/../vendor/autoload.php';
  }
  $jazzeeConfiguration = new \Jazzee\Configuration();

  //setup doctrine
  $doctrineConfig = new \Doctrine\ORM\Configuration();

  //We use different caching and proxy settings in Development status
  if($jazzeeConfiguration->getStatus() == 'DEVELOPMENT'){
    $doctrineConfig->setAutoGenerateProxyClasses(true);
    $doctrineConfig->setProxyDir(__DIR__ . '/../var/tmp');
  } else {
    $doctrineConfig->setAutoGenerateProxyClasses(false);
    $doctrineConfig->setProxyDir(__DIR__ . '/../src/Jazzee/Entity/Proxy');
  }
  $cache = new \Doctrine\Common\Cache\ArrayCache;
  $driver = $doctrineConfig->newDefaultAnnotationDriver(array(__DIR__."/../src/Jazzee/Entity"));
  $doctrineConfig->setMetadataDriverImpl($driver);

  $doctrineConfig->setProxyNamespace('Jazzee\Entity\Proxy');
  $doctrineConfig->setMetadataCacheImpl($cache);
  $doctrineConfig->setQueryCacheImpl($cache);

  $connectionParams = array(
    'dbname' => $jazzeeConfiguration->getDbName(),
    'user' => $jazzeeConfiguration->getDbUser(),
    'password' => $jazzeeConfiguration->getDbPassword(),
    'host' => $jazzeeConfiguration->getDbHost(),
    'port' => $jazzeeConfiguration->getDbPort(),
    'driver' => $jazzeeConfiguration->getDbDriver()
  );

  $em = \Doctrine\ORM\EntityManager::create($connectionParams, $doctrineConfig);
}catch(Exception $e){
  print $e->getMessage() . PHP_EOL;
  exit(1);
}
$helpers = array(
    'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
    'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em)
);

/**
 * We need to be able to call AdminDirectory adn AdminAuthentication functions
 * from different Console commands.  This Lets that happen
 */
class AdminStub implements \Jazzee\Interfaces\AdminController{
  public $em;
  public $config;
  static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null, \Jazzee\Entity\Application $application = null){
    return false;
  }
  static function addControllerPath($path){}
  public function getEntityManager(){
    return $this->em;
  }
  public function getConfig(){
    return $this->config;
  }
  public function getStore(){
    return new \Foundation\Session\Store(30);
  }
}