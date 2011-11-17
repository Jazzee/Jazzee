<?php
require_once __dir__ . '/../lib/foundation/src/foundation.php';

$classLoader = new \Doctrine\Common\ClassLoader('Doctrine');
$classLoader->register();

$classLoader = new \Doctrine\Common\ClassLoader('Symfony', 'Doctrine');
$classLoader->register();

$classLoader = new Doctrine\Common\ClassLoader('Jazzee', __DIR__ . '/../src/');
$classLoader->register();

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
  'driver' => $jazzeeConfiguration->getDbDriver(),
  'charset' => 'utf8'
);

$em = \Doctrine\ORM\EntityManager::create($connectionParams, $doctrineConfig);
$em->getConnection()->setCharset('utf8');
$helpers = array(
    'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
    'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em)
);
