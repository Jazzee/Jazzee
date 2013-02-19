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
   */
  public function deleteUnreferencedFiles(){
    $query = $this->_em->createQuery('DELETE FROM Jazzee\Entity\File f WHERE f.referenceCount = 0');
    $query->execute();
  }
  
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
}
