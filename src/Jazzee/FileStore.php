<?php
namespace Jazzee;

/**
 * Abstraction for handling files.
 * When first written files are put into the DB and a file id is returned which
 * can be stored by something like the answer_attachment as an integer.  This file
 * can then be refered to by this ID and not fetched from the database unless it is
 * needed.   Files are cached on the file system by thier hash to minimize reads
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class FileStore
{
  /**
   *
   * @var \Doctrine\ORM\EntityManager 
   */
  protected $_entityManager;
  
  /**
   * The file cache path
   * @var string
   */
  protected $_cachePath;
  
  /**
   * The file session store
   * @var \Foundation\Session\Store
   */
  protected $_sessionStore;
  
  /**
   * Create the file store
   * @param \Doctrine\ORM\EntityManager $entityManager
   * @param string $cachePath
   */
  public function __construct(\Doctrine\ORM\EntityManager $entityManager, $cachePath) {
    $this->_entityManager = $entityManager;
    if(!\is_dir($cachePath) or !\is_writable($cachePath)){
      throw new \Jazzee\Exception("{$cachePath} is not usable as a cache path in FileStore");
    }
    $this->_cachePath = \realpath($cachePath);
    $this->_sessionStore = null;
  }
  
  /**
   * Store a file
   * @param string $blob
   * 
   * @return string file hash
   */
  public function storeFile($blob)
  {
    $file = new \Jazzee\Entity\File($blob);
    //Don't store the same file twice, so first check if the hash already exists
    if($existingFile = $this->getFileEntity($file->getHash())){
      $file = $existingFile;
      $file->addReference();
    }
    $this->_entityManager->persist($file);
    $this->cacheBlob($file->getHash(), $file->getBlob());

    return $file->getHash();
  }
  
  /**
   * A system file gets stored by hash, but doesn't get written to the DB
   * @param string $hash
   * @param string $blob
   * 
   * @return string file hash
   */
  public function storeSystemFile($hash, $blob)
  {
    $this->cacheBlob($hash, $blob);
  }
  
  /**
   * Remove a file
   * @param string $hash
   */
  public function removeFile($hash)
  {
    if($file = $this->getFileEntity($hash)){
      $file->removeReference();
      $this->_entityManager->persist($file);
    }
  }
  
  /**
   * Seed the cache with a file
   * @param string $hash
   */
  public function seedCache($hash)
  {
    if(!$this->inCache($hash)){
      $file = $this->getFileArray($hash);
      $this->cacheBlob($file['hash'], $file['blob']);
      return true;
    }

    return false;
  }
  
  /**
   * Get the contents of a file
   * @param string $hash
   * 
   * @return string
   */
  public function getFileContents($hash)
  {
    if($this->inCache($hash)){
      return file_get_contents($this->cachePath($hash));
    }
    if($file = $this->getFileArray($hash)){
      $this->cacheBlob($file['hash'], $file['blob']);
      return $file['blob'];
    }

    return false;
  }
  
  /**
   * Get a php file handle for a file
   * @param string $hash
   * 
   * @return resource
   */
  public function getFileHandle($hash)
  {
    if($path = $this->getFilePath($hash)){
      $handle = \fopen($path, 'r');
      return $handle;
    }
    
    return false;
  }
  
  /**
   * Get a file system path for a file which can be output directly with apache
   * @param string $hash
   * 
   * @return string
   */
  public function getFilePath($hash)
  {
    if(!$this->inCache($hash)){
      if($file = $this->getFileArray($hash)){
        $this->cacheBlob($file['hash'], $file['blob']);
      } else {
        return false;
      }
    }

    return $this->cachePath($hash);
  }
  
  /**
   * Get the file entity
   * @param type $hash
   * 
   * @return \Jazzee\Entity\File
   */
  protected function getFileEntity($hash){
    return $this->_entityManager->getRepository('Jazzee\Entity\File')->findOneBy(array('hash'=>$hash));
  }
  
  /**
   * Get the file array
   * @param type $hash
   * 
   * @return array
   */
  protected function getFileArray($hash){
    return $this->_entityManager->getRepository('Jazzee\Entity\File')->findArrayByHash($hash);
  }
  
  /**
   * Create a file cache path subdirectory structure by using the hash to create
   * sub directories.  This ensures that we don't get too many files in a single 
   * directory
   * @param type $hash
   * 
   * @return string
   */
  protected function cachePath($hash){
    $path = array($this->_cachePath, substr($hash, 0, 2), substr($hash, 2, 2));
    $base = implode(DIRECTORY_SEPARATOR, $path);
    if(!is_dir($base)){
      mkdir($base, 0777, true);
    }
    return $base . DIRECTORY_SEPARATOR . $hash;
  }
  
  /**
   * Cache a file in the path
   * @param string $filename
   * @param string $blob
   * 
   * @return string
   */
  protected function cacheBlob($hash, $blob){
    if(!$this->inCache($hash)){
      file_put_contents($this->cachePath($hash), $blob);
    }
  }
  
  /**
   * Check if a file is in the cache
   * @param string $hash
   * 
   * @return boolean
   */
  protected function inCache($hash){
    return file_exists($this->cachePath($hash));
  }
  
  /**
   * Create a session key by md5ing the file name
   * @param string $filename
   * @return string
   */
  protected function sessionKeyFromFileName($filename){
    return md5($filename);
  }
  
  protected function getSessionStore(){
    if(is_null($this->_sessionStore)){
      $session = new \Foundation\Session();
      $this->_sessionStore = $session->getStore('files');
    }
    return $this->_sessionStore;
  }

  /**
   * Put a file in the users session so it can be acessed safely asynchronously
   *
   * @param string $filename
   * @param string $hash
   */
  public function createSessionFile($filename, $hash)
  {
    $sessionKey = $this->sessionKeyFromFileName($filename);
    $this->getSessionStore()->set($sessionKey, $hash);
  }

  /**
   * Get a file from a users session stored hash
   *
   * @param string $filename
   * @return \Foundation\Virtual\RealFile
   */
  public function getSessionFile($filename)
  {
    $sessionKey = $this->sessionKeyFromFileName($filename);
    if ($this->getSessionStore()->check($sessionKey) and $path = $this->getFilePath($this->_sessionStore->get($sessionKey))){
      return new \Foundation\Virtual\RealFile($filename, $path);
    }

    return false;
  }

  /**
   * Remove a stored file
   *
   * @param string $filename
   */
  public function removeSessionFile($filename)
  {
    $sessionKey = $this->sessionKeyFromFileName($filename);
    $this->getSessionStore()->remove($sessionKey);
  }

  /**
   * Delete unreferenced Files
   * @param AdminCronController $cron
   */
  public static function runCron(\AdminCronController $cron)
  {
    $cron->getEntityManager()->getRepository('Jazzee\Entity\File')->deleteUnreferencedFiles();
  }

}