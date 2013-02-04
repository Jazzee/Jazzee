<?php
namespace Jazzee\Entity;

/**
 * Applicant
 * Individual applicants are tied to an Application - but a single person can be multiple Applicants
 *
 * @Entity
 * @Table(name="decisions")
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class Decision
{

  /**
   * @Id
   * @Column(type="bigint")
   * @GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @OneToOne(targetEntity="Applicant",inversedBy="decision")
   * @JoinColumn(onDelete="CASCADE")
   */
  private $applicant;

  /** @Column(type="datetime", nullable=true) */
  private $nominateAdmit;

  /** @Column(type="datetime", nullable=true) */
  private $nominateDeny;

  /** @Column(type="datetime", nullable=true) */
  private $finalAdmit;

  /** @Column(type="datetime", nullable=true) */
  private $finalDeny;

  /** @Column(type="datetime", nullable=true) */
  private $offerResponseDeadline;

  /** @Column(type="datetime", nullable=true) */
  private $decisionViewed;

  /** @Column(type="datetime", nullable=true) */
  private $acceptOffer;

  /** @Column(type="datetime", nullable=true) */
  private $declineOffer;

  /**
   * Get Id
   * @return integer
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Set applicant
   *
   * @param Entity\Applicant $applicant
   */
  public function setApplicant(Applicant $applicant)
  {
    $this->applicant = $applicant;
  }

  /**
   * Get applicant
   *
   * @return Applicant
   */
  public function getApplicant()
  {
    return $this->applicant;
  }

  /**
   * Format a decision time stamp
   * @param string $dateString
   */
  protected function decisionStamp($dateString)
  {
    $dateString = empty($dateString) ? 'now' : $dateString;

    return new \DateTime($dateString);
  }

  /**
   * Nominate for admit decision
   * @param string $dateString
   */
  public function nominateAdmit($dateString = null)
  {
    if (!is_null($this->nominateDeny)) {
      throw new \Jazzee\Exception('Cannot record two preliminary decisions');
    }
    if (is_null($this->nominateAdmit)) {
      $this->nominateAdmit = $this->decisionStamp($dateString);
    }
  }

  /**
   * Undo nominate admit
   */
  public function undoNominateAdmit()
  {
    if (!is_null($this->finalAdmit)) {
      throw new \Jazzee\Exception('Cannot remove a preliminary decision if a final decision has been recorded');
    }
    $this->nominateAdmit = null;
  }

  /**
   * Nominate for deny decision
   * @param string $dateString
   */
  public function nominateDeny($dateString = null)
  {
    if (!is_null($this->nominateAdmit)) {
      throw new \Jazzee\Exception('Cannot record two preliminary decisions');
    }
    if (is_null($this->nominateDeny)) {
      $this->nominateDeny = $this->decisionStamp($dateString);
    }
  }

  /**
   * Undo nominate deny
   * @param string $dateString
   */
  public function undoNominateDeny()
  {
    if (!is_null($this->finalDeny)) {
      throw new \Jazzee\Exception('Cannot remove a preliminary decision if a final decision has been recorded');
    }
    $this->nominateDeny = null;
  }

  /**
   * Final Deny Decision
   * @param string $dateString
   */
  public function finalDeny($dateString = null)
  {
    if (!is_null($this->finalAdmit)) {
      throw new \Jazzee\Exception('Cannot record two final decisions');
    }
    //if we don't have a preliminary decision record it now
    if (is_null($this->nominateDeny)) {
      $this->nominateDeny($dateString);
    }
    if (is_null($this->finalDeny)) {
      $this->finalDeny = $this->decisionStamp($dateString);
    }
  }

  /**
   * Undo Final Deny Decision
   */
  public function undoFinalDeny()
  {
    $this->finalDeny = null;
    $this->decisionViewed = null;
  }

  /**
   * Set offerResponseDeadline
   * @param string $dateString
   */
  public function setOfferResponseDeadline($dateString)
  {
    $this->offerResponseDeadline = new \DateTime($dateString);
  }

  /**
   * Get offerResponseDeadline
   * @return DateTime|null
   */
  public function getOfferResponseDeadline()
  {
    return $this->offerResponseDeadline;
  }

  /**
   * Final Admit Decision
   * @param string $dateString
   */
  public function finalAdmit($dateString = null)
  {
    if (!is_null($this->finalDeny)) {
      throw new \Jazzee\Exception('Cannot record two final decisions');
    }
    //if we don't have a preliminary decision record it now
    if (is_null($this->nominateAdmit)) {
      $this->nominateAdmit($dateString);
    }
    if (is_null($this->finalAdmit)) {
      $this->finalAdmit = $this->decisionStamp($dateString);
    }
  }

  /**
   * Undo Final Deny Decision
   */
  public function undoFinalAdmit()
  {
    if (!is_null($this->acceptOffer) and !is_null($this->declineOffer)) {
      throw new \Jazzee\Exception('Cannot undo admit for an applicant with a offer response.');
    }
    $this->finalAdmit = null;
    $this->decisionViewed = null;
  }

  /**
   * Accept Offer
   * @param string $dateString
   */
  public function acceptOffer($dateString = null)
  {
    if (is_null($this->finalAdmit)) {
      throw new \Jazzee\Exception('Cannot accept offer for an applicant who has not been admitted');
    }
    if (is_null($this->acceptOffer)) {
      $this->acceptOffer = $this->decisionStamp($dateString);
    }
  }

  /**
   * Undo Accept Offer
   */
  public function undoAcceptOffer()
  {
    $this->acceptOffer = null;
  }

  /**
   * Decline Offer
   * @param string $dateString
   */
  public function declineOffer($dateString = null)
  {
    if (is_null($this->finalAdmit)) {
      throw new \Jazzee\Exception('Cannot decline offer for an applicant who has not been admitted');
    }
    if (is_null($this->declineOffer)) {
      $this->declineOffer = $this->decisionStamp($dateString);
    }
  }

  /**
   * Undo Decline Offer
   */
  public function undoDeclineOffer()
  {
    $this->declineOffer = null;
  }

  /**
   * Register a decision letter has been viewed
   * @param string $dateString
   */
  public function decisionViewed($dateString = null)
  {
    $this->decisionViewed = $this->decisionStamp($dateString);
  }

  /**
   * get finalDeny
   *
   * @return \DateTime
   */
  public function getFinalDeny()
  {
    return $this->finalDeny;
  }

  /**
   * get finalAdmit
   *
   * @return \DateTime
   */
  public function getFinalAdmit()
  {
    return $this->finalAdmit;
  }

  /**
   * get nominateDeny
   *
   * @return \DateTime
   */
  public function getNominateDeny()
  {
    return $this->nominateDeny;
  }

  /**
   * get nominateAdmit
   *
   * @return \DateTime
   */
  public function getNominateAdmit()
  {
    return $this->nominateAdmit;
  }

  /**
   * get acceptOffer
   *
   * @return \DateTime
   */
  public function getAcceptOffer()
  {
    return $this->acceptOffer;
  }

  /**
   * get decline offer
   *
   * @return \DateTime
   */
  public function getDeclineOffer()
  {
    return $this->declineOffer;
  }

  /**
   * get decisionLetterViewed
   *
   * @return \DateTime
   */
  public function getDecisionViewed()
  {
    return $this->decisionViewed;
  }

  /**
   * get decision summaray
   *
   * and array with each decision status
   * @param string $format optionally provide a format to convert status times to
   * @return array
   */
  public function summary($format = null)
  {
    $decisions = array(
      'nominateAdmit' => $this->nominateAdmit,
      'nominateDeny' => $this->nominateDeny,
      'finalAdmit' => $this->finalAdmit,
      'finalDeny' => $this->finalDeny,
      'decisionLetterSent' => $this->decisionLetterSent,
      'decisionLetterViewed' => $this->decisionLetterViewed,
      'acceptOffer' => $this->acceptOffer,
      'declineOffer' => $this->declineOffer,
    );
    if ($format) {
      foreach ($decisions as $key => $value) {
        if ($value) {
          $decisions[$key] = $value->format($format);
        }
      }
    }

    return $decisions;
  }

  /**
   * get decision status
   *
   * Look at all the status's and pick the trumping final one
   * eg (nominateAdmit, finalAdmit, declineOffer = 'declineOffer')
   * @return string
   */
  public function status()
  {
    $decisions = array(
      'nominateAdmit',
      'nominateDeny',
      'finalAdmit',
      'finalDeny',
      'acceptOffer',
      'declineOffer'
    );
    $final = '';
    foreach ($decisions as $decision) {
      if ($this->$decision) {
        $final = $decision;
      }
    }

    return $final;
  }

  /**
   * Check if a status can be set
   * @param string $status
   *
   * @return boolean
   */
  public function can($status)
  {
    switch ($status) {
      case 'nominateAdmit':
        return (is_null($this->nominateAdmit) and is_null($this->nominateDeny));
      case 'undoNominateAdmit':
        return ($this->nominateAdmit and is_null($this->finalAdmit));
      case 'nominateDeny':
        return (is_null($this->nominateAdmit) and is_null($this->nominateDeny));
      case 'undoNominateDeny':
        return (!is_null($this->nominateDeny) and is_null($this->finalDeny));
      case 'finalAdmit':
        return ($this->nominateAdmit and is_null($this->finalAdmit));
      case 'finalDeny':
        return ($this->nominateDeny and is_null($this->finalDeny));
      case 'undoFinalAdmit':
        return (!is_null($this->finalAdmit) and is_null($this->acceptOffer) and is_null($this->declineOffer));
      case 'undoFinalDeny':
        return (!is_null($this->finalDeny) and is_null($this->acceptOffer) and is_null($this->declineOffer));
      case 'acceptOffer':
      case 'declineOffer':
        return ($this->finalAdmit and is_null($this->acceptOffer) and is_null($this->declineOffer));
      case 'undoAcceptOffer':
        return !is_null($this->acceptOffer);
      case 'undoDeclineOffer':
        return !is_null($this->declineOffer);
    }
    throw new \Jazzee\Exception("{$status} is not a valid decision status type");
  }

}