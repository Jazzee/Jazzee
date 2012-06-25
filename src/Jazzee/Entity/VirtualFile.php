<?php
namespace Jazzee\Entity;

/** 
 * Virtual File
 * 
 * Allow binary objects like images and PDFs to be stored
 * @Entity
 * @Table(name="virtual_files", 
 * uniqueConstraints={@UniqueConstraint(name="virtualfile_name",columns={"name"})})
 * @package    jazzee
 * @subpackage orm
 * @SuppressWarnings(PHPMD.ShortVariable)
 **/
class VirtualFile{
  /**
    * @Id 
    * @Column(type="bigint")
    * @GeneratedValue(strategy="AUTO")
  */
  private $id;
  
  /** @Column(type="string") */
  private $name;
  
  /** @Column(type="text") */
  private $contents;
  
  /**
   * Get id
   *
   * @return bigint $id
   */
  public function getId(){
    return $this->id;
  }

  /**
   * Set name
   *
   * @param string $name
   */
  public function setName($name){
    $this->name = $name;
  }

  /**
   * Get name
   *
   * @return string $name
   */
  public function getName(){
    return $this->name;
  }

  /**
   * Set contents
   *
   * @param text $contents
   */
  public function setContents($contents){
    $this->contents = base64_encode($contents);
  }

  /**
   * Get contents
   *
   * @return text $contents
   */
  public function getContents(){
    return base64_decode($this->contents);
  }
}