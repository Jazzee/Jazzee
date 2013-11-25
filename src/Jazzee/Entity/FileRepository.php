<?php
namespace Jazzee\Entity;

/**
 * FIleRepository
 * Special Repository methods for files
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class FileRepository extends \Doctrine\ORM\EntityRepository
{
  
  /**
   * Remove any files which have a reference count of 0
   * We do this in two steps becuase it is way faster and doesn't lock the whole table
   */
  public function deleteUnreferencedFiles(){
    $query = $this->_em->createQuery('SELECT f.id FROM Jazzee\Entity\File f WHERE f.referenceCount = 0');
    $files = array();
    foreach($query->getArrayResult() as $arr){
        $files[] = $arr['id'];
    }
    if(count($files) > 0){
        $query = $this->_em->createQuery('DELETE FROM Jazzee\Entity\File f WHERE f.id IN (' . implode(',', $files) . ')');
        $query->execute();
    }
  }
  
  /**
   * Seed the file cache with any files accessed after a date
   * 
   * @param \Jazzee\FileStore $fileStore
   * @param \DateTime $lastAccessedSince
   * @return int
   */
  public function seedFileCache(\Jazzee\FileStore $fileStore, \DateTime $lastAccessedSince)
  {
    $query = $this->_em->createQuery('SELECT f.hash FROM Jazzee\Entity\File f WHERE f.lastAccess > :lastAccess');
    $query->setParameter('lastAccess', $lastAccessedSince);
    $cached = 0;
    foreach ($query->execute() as $arr) {
      if($fileStore->seedCache($arr['hash'])){
        $cached++;
      }
    }

    return $cached;
  }
  
  /**
   * Get the file as an array by hash
   * 
   * @param string $hash
   * @return array
   */
  public function findArrayByHash($hash)
  {
    $query = $this->_em->createQuery('SELECT f from Jazzee\Entity\File f WHERE f.hash= :hash');
    $query->setParameter('hash', $hash);
    try{
    $result = $query->getSingleResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);
    } catch(\Doctrine\ORM\NoResultException $e) {
        throw new \Jazzee\Exception('There is no file with the hash: ' . $hash . $e->getTraceAsString());
    }
    //we use a second array becuase the other properties are internal
    $arr = array();
    $arr['blob'] = base64_decode($result['encodedBlob']);
    $arr['hash'] = $result['hash'];
    $arr['lastAccess'] = $result['lastAccess'];
    $arr['lastModification'] = $result['lastModification'];
    $query = $this->_em->createQuery('UPDATE Jazzee\Entity\File f SET f.lastAccess = :lastAccess WHERE f.id= :id and f.lastAccess < :lastAccess');
    $query->setParameter('id', $result['id']);
    $query->setParameter('lastAccess', new \DateTime());
    $query->execute();
    return $arr;
  }
}
