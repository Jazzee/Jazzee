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

  /** @Column(type="text") */
  private $attachment;

  /** @Column(type="text") */
  private $thumbnail;

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
   * @param blob $attachment
   */
  public function setAttachment($blob)
  {
    $this->attachment = base64_encode($blob);
    try {
      $imagick = new \imagick;
      $imagick->readimageblob($blob);
      $imagick->setiteratorindex(0);
      $imagick->setImageFormat("png");
      $imagick->scaleimage(100, 0);
    } catch (\ImagickException $e) {
      $imagick = new \imagick;
      $imagick->readimage(realpath(__DIR__ . '/../../../lib/foundation/src/media/default_pdf_logo.png'));
      $imagick->scaleimage(100, 0);
    }
    $this->thumbnail = base64_encode($imagick->getimageblob());
  }

  /**
   * Get attachment
   *
   * @return text $attachment
   */
  public function getAttachment()
  {
    return base64_decode($this->attachment);
  }

  /**
   * Get thumbnail
   *
   * @return blob $thumbnail
   */
  public function getThumbnail()
  {
    if ($this->thumbnail) {
      return base64_decode($this->thumbnail);
    }

    return false;
  }

  /**
   * Mark the lastUpdate automatically
   * @PrePersist @PreUpdate
   */
  public function markLastUpdate()
  {
    $this->applicant->markLastUpdate();
  }

}