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
    //create the preview image
    try {
      //use a temporary file so we can use the image magic shortcut [0]
      //to load only the first page, otherwise the whole file gets loaded into memory and takes forever
      $handle = tmpfile();
      fwrite($handle, $blob);
      $arr = stream_get_meta_data($handle);
      $imagick = new \imagick;
      $imagick->readimage($arr['uri'] . '[0]');
      $imagick->setImageFormat("png");
      $imagick->thumbnailimage(100, 150, true);
      fclose($handle);
    } catch (ImagickException $e) {
      $imagick = new \imagick;
      $imagick->readimage(realpath(\Foundation\Configuration::getSourcePath() . '/src/media/default_pdf_logo.png'));
      $imagick->thumbnailimage(100, 150, true);
    }
    $this->thumbnail = base64_encode($imagick->getimageblob());
    unset($imagick);
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