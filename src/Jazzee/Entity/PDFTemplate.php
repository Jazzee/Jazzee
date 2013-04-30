<?php
namespace Jazzee\Entity;

/**
 * PDFTemplate
 * Storage for the PDF templates
 *
 * @Entity
 * @Table(name="pdf_templates")
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class PDFTemplate
{
  /**
   * Page type for regular application apges
   */

  /**
   * @Id
   * @Column(type="bigint")
   * @GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @ManyToOne(targetEntity="Application", inversedBy="pdfTemplates")
   * @JoinColumn(onDelete="CASCADE")
   */
  private $application;

  /** @Column(type="string", length=200) */
  private $title;

  /** @Column(type="string", length=128) */
  private $fileHash;

  /** @Column(type="array") */
  private $blocks;

  /**
   * Constructor to create a default blank array for blocks
   */
  public function __construct(){
    $this->blocks = array();
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
   * Set application
   *
   * @param Entity\Application $application
   */
  public function setApplication(Application $application)
  {
    $this->application = $application;
  }

  /**
   * get application
   *
   * @return \Jazzee\Entity\Application $application
   */
  public function getApplication()
  {
    return $this->application;
  }

  /**
   * Get the title
   * If the title is not overridde then use the one from Page
   */
  public function getTitle()
  {
    return $this->title;
  }

  /**
   * Set the title
   * If this isn't a global page then store the title in Page and not here
   * @param string $title
   */
  public function setTitle($value)
  {
    $this->title = $value;
  }

  /**
   * Store the file
   *
   * @param blob $blob
   */
  public function setFile($blob)
  {
    $this->fileHash = \Jazzee\Globals::getFileStore()->storeFile($blob);
  }

  /**
   * Get the file blob
   *
   * @return blob
   */
  public function getFile()
  {
    return \Jazzee\Globals::getFileStore()->getFileContents($this->fileHash);
  }

  /**
   * Get the temporary path to the PDF file
   * @return string
   */
  public function getTmpFilePath()
  {
    return \Jazzee\Globals::getFileStore()->getFilePath($this->fileHash);
  }

  /**
   * Clear the blocks on a page
   */
  public function clearBlocks()
  {
    $this->blocks = array();
  }

  /**
   * Add a block to the template
   * @param string $blockName
   * @param array $blockData
   */
  public function addBlock($blockName, $blockData)
  {
    $this->blocks[$blockName] = $blockData;
  }

  /**
   * Check if a tempalte block is set
   * @param string $blockName
   * @return boolean
   */
  public function hasBlock($blockName)
  {
    return array_key_exists($blockName, $this->blocks);
  }

  /**
   * Get the data array for a block
   * @param string $blockName
   * @return int || null
   */
  public function getBlock($blockName)
  {
    if (array_key_exists($blockName, $this->blocks)) {
      return $this->blocks[$blockName];
    }

    return null;
  }

}