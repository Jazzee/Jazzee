<?php
namespace Jazzee\Entity;

/**
 * File
 * Store a blob in the database
 *
 * @Entity(repositoryClass="\Jazzee\Entity\FileRepository")
 * @Table(name="files")
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class File
{

  /**
   * @Id
   * @Column(type="bigint")
   * @GeneratedValue(strategy="AUTO")
   */
  private $id;

  /** @Column(type="string", length=128, unique=true) */
  private $hash;

  /** @Column(type="text") */
  private $encodedBlob;

  /** 
   * Track the references to a file so we can remove it when there are none
   * @Column(type="integer") */
  private $referenceCount;

  /** @Column(type="datetime") */
  protected $lastModification;

  /** @Column(type="datetime") */
  protected $lastAccess;
  
  public function __construct($blob)
  {
    $this->setBlob($blob);  
  }

  /**
   * Get id
   *
   * @return bigint $id
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Get hash
   *
   * @return string
   */
  public function getHash()
  {
    $this->lastAccess = new \DateTime('now');
    return $this->hash;
  }

  /**
   * Convert the blob to base64 and store it
   *
   * @param blob $blob
   */
  protected function setBlob($blob)
  {
    $this->hash = \sha1($blob);
    $this->lastModification = new \DateTime('now');
    $this->lastAccess = new \DateTime('now');
    $this->encodedBlob = base64_encode($blob);
    $this->referenceCount = 1;
  }

  /**
   * Get blob
   *
   * @return blob
   */
  public function getBlob()
  {
    $this->lastAccess = new \DateTime('now');
    return base64_decode($this->encodedBlob);
  }

  /**
   * Get lastModification
   *
   * @return \DateTime
   */
  public function getLastModification()
  {
    return $this->lastModification;
  }

  /**
   * Get last access
   *
   * @return \DateTime
   */
  public function getLastAccess()
  {
    return $this->lastAccess;
  }
  
  /**
   * Increement the file reference counter
   */
  public function addReference()
  {
    $this->referenceCount++;
  }
  
  /**
   * Decrement the file reference counter
   */
  public function removeReference()
  {
    $this->referenceCount--;
  }

}