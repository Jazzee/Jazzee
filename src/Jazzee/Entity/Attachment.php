<?php
namespace Jazzee\Entity;

/**
 * Attachment
 * Attach a file to an applicant
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="attachments")
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class Attachment
{

  /**
   * @Id
   * @Column(type="bigint")
   * @GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @OneToOne(targetEntity="Answer",inversedBy="attachment")
   * @JoinColumn(onDelete="CASCADE")
   */
  private $answer;

  /**
   * @ManyToOne(targetEntity="Applicant",inversedBy="attachments")
   * @JoinColumn(onDelete="CASCADE")
   */
  private $applicant;

  /** @Column(type="string", length=128) */
  private $attachmentHash;

  /** @Column(type="string", length=128, nullable=true) */
  private $thumbnailHash;

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
   * Set the Applicant
   * @param Entity\Applicant $applicant
   */
  public function setApplicant(Applicant $applicant)
  {
    $this->applicant = $applicant;
  }

  /**
   * Get the Applicant
   * @return Entity\Applicant $applicant
   */
  public function getApplicant()
  {
    return $this->applicant;
  }

  /**
   * Set the answer
   * @param Answer $answer
   */
  public function setAnswer(Answer $answer)
  {
    $this->answer = $answer;
  }

  /**
   * Get the answer
   * @return Answer
   */
  public function getAnswer()
  {
    return $this->answer;
  }

  /**
   * Convert the attachment to base64 and store it
   *
   * @param blob $blob
   */
  public function setAttachment($blob)
  {
    $this->attachmentHash = \Jazzee\Globals::getFileStore()->storeFile($blob);
  }

  /**
   * Get attachment
   *
   * @return text $attachment
   */
  public function getAttachment()
  {
    return \Jazzee\Globals::getFileStore()->getFileContents($this->attachmentHash);
  }

  /**
   * Convert the thumbnail to base64 and store it
   *
   * @param blob $blob
   */
  public function setThumbnail($blob)
  {
    $this->thumbnailHash = \Jazzee\Globals::getFileStore()->storeFile($blob);
  }

  /**
   * Get thumbnail
   *
   * @return blob $thumbnail
   */
  public function getThumbnail()
  {
    if ($this->thumbnailHash) {
      return \Jazzee\Globals::getFileStore()->getFileContents($this->thumbnailHash);
    }

    return false;
  }
  
  /**
   * Get the attachment hash
   * @return string
   */
  public function getAttachmentHash(){
    return $this->attachmentHash;
  }
  
  /**
   * Get the thumbnail hash
   * @return string
   */
  public function getThumbnailHash(){
    return $this->thumbnailHash;
  }
  
  /**
   * Remove any attachmetn file pointers
   * @PreRemove
   */
  public function preRemove(){
    if ($this->thumbnailHash) {
      \Jazzee\Globals::getFileStore()->removeFile($this->thumbnailHash);
    }
    \Jazzee\Globals::getFileStore()->removeFile($this->attachmentHash);
  }

}