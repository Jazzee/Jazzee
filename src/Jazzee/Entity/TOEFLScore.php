<?php
namespace Jazzee\Entity;

/**
 * TOEFLScore
 * Scores imported from ETS
 *
 * @Entity(repositoryClass="\Jazzee\Entity\TOEFLScoreRepository")
 * @Table(name="toefl_scores",uniqueConstraints={@UniqueConstraint(name="toefl_registration", columns={"registrationNumber", "testMonth", "testYear"})})
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class TOEFLScore
{

  /**
   * @Id
   * @Column(type="bigint")
   * @GeneratedValue(strategy="AUTO")
   */
  private $id;

  /** @Column(type="bigint") */
  private $registrationNumber;

  /** @Column(type="integer") */
  private $testMonth;

  /** @Column(type="integer") */
  private $testYear;

  /** @Column(type="string", length=4, nullable=true) */
  private $departmentCode;

  /** @Column(type="string", nullable=true) */
  private $firstName;

  /** @Column(type="string", nullable=true) */
  private $middleName;

  /** @Column(type="string") */
  private $lastName;

  /** @Column(type="datetime") */
  private $birthDate;

  /** @Column(type="string", length=1, nullable=true) */
  private $gender;

  /** @Column(type="string") */
  private $nativeCountry;

  /** @Column(type="string") */
  private $nativeLanguage;

  /** @Column(type="datetime") */
  private $testDate;

  /** @Column(type="string") */
  private $testType;

  /** @Column(type="integer", length=1, nullable=true) */
  private $listeningIndicator;

  /** @Column(type="integer", length=1, nullable=true) */
  private $speakingIndicator;

  /** @Column(type="integer", length=2, nullable=true, name="IBTListening") */
  private $ibtListening;

  /** @Column(type="integer", length=2, nullable=true, name="IBTWriting") */
  private $ibtWriting;

  /** @Column(type="integer", length=2, nullable=true, name="IBTSpeaking") */
  private $ibtSpeaking;

  /** @Column(type="integer", length=2, nullable=true, name="IBTReading") */
  private $ibtReading;

  /** @Column(type="integer", length=3, nullable=true, name="IBTTotal") */
  private $ibtTotal;

  /** @Column(type="integer", length=2, nullable=true, name="TSEScore") */
  private $tseScore;

  /** @Column(type="integer", length=2, nullable=true) */
  private $listening;

  /** @Column(type="integer", length=2, nullable=true) */
  private $writing;

  /** @Column(type="integer", length=2, nullable=true) */
  private $reading;

  /** @Column(type="integer", length=2, nullable=true) */
  private $essay;

  /** @Column(type="integer", length=3, nullable=true) */
  private $total;

  /** @Column(type="integer", length=3, nullable=true) */
  private $timesTaken;

  /** @Column(type="string", length=1, nullable=true) */
  private $offTopic;

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
   * Set registrationNumber
   *
   * @param string $registrationNumber
   * @param integer $testMonth
   * @param integer $testYear
   */
  public function setRegistrationNumber($registrationNumber, $testMonth, $testYear)
  {
    if ($testMonth < 1 OR $testMonth > 12) {
      throw new \Jazzee_Exception("{$testMonth} is not a valid month");
    }
    if ($testYear < 1900 OR $testMonth > 2100) {
      throw new \Jazzee_Exception("{$testYear} is not a valid year");
    }
    //remove leading 0s
    $this->registrationNumber = ltrim($registrationNumber, '0');
    $this->testMonth = $testMonth;
    $this->testYear = $testYear;
  }

  /**
   * Get registrationNumber
   *
   * @return string $registrationNumber
   */
  public function getRegistrationNumber()
  {
    return $this->registrationNumber;
  }

  /**
   * Set departmentCode
   *
   * @param string $departmentCode
   */
  public function setDepartmentCode($departmentCode)
  {
    $this->departmentCode = $departmentCode;
  }

  /**
   * Get departmentCode
   *
   * @return string $departmentCode
   */
  public function getDepartmentCode()
  {
    return $this->departmentCode;
  }

  /**
   * Set firstName
   *
   * @param string $firstName
   */
  public function setFirstName($firstName)
  {
    $this->firstName = $firstName;
  }

  /**
   * Get firstName
   *
   * @return string $firstName
   */
  public function getFirstName()
  {
    return $this->firstName;
  }

  /**
   * Set middleName
   *
   * @param string $middleName
   */
  public function setMiddleName($middleName)
  {
    $this->middleName = $middleName;
  }

  /**
   * Get middleName
   *
   * @return string $middleName
   */
  public function getMiddleName()
  {
    return $this->middleName;
  }

  /**
   * Set lastName
   *
   * @param string $lastName
   */
  public function setLastName($lastName)
  {
    $this->lastName = $lastName;
  }

  /**
   * Get lastName
   *
   * @return string $lastName
   */
  public function getLastName()
  {
    return $this->lastName;
  }

  /**
   * Set birthDate
   *
   * @param string $birthDate
   */
  public function setBirthDate($birthDate)
  {
    $this->birthDate = new \DateTime($birthDate);
  }

  /**
   * Get birthDate
   *
   * @return DateTime $birthDate
   */
  public function getBirthDate()
  {
    return $this->birthDate;
  }

  /**
   * Set gender
   *
   * @param string $gender
   */
  public function setGender($gender)
  {
    if (!in_array(strtolower($gender), array('m', 'f'))) {
      throw new \Jazzee\Exception("{$gender} is not a valid gender");
    }
    $this->gender = $gender;
  }

  /**
   * Get gender
   *
   * @return string $gender
   */
  public function getGender()
  {
    return $this->gender;
  }

  /**
   * Set nativeCountry
   *
   * @param string $nativeCountry
   */
  public function setNativeCountry($nativeCountry)
  {
    $this->nativeCountry = $nativeCountry;
  }

  /**
   * Get nativeCountry
   *
   * @return string $nativeCountry
   */
  public function getNativeCountry()
  {
    return $this->nativeCountry;
  }

  /**
   * Set nativeLanguage
   *
   * @param string $nativeLanguage
   */
  public function setNativeLanguage($nativeLanguage)
  {
    $this->nativeLanguage = $nativeLanguage;
  }

  /**
   * Get nativeLanguage
   *
   * @return string $nativeLanguage
   */
  public function getNativeLanguage()
  {
    return $this->nativeLanguage;
  }

  /**
   * Set testDate
   *
   * @param string $testDate
   */
  public function setTestDate($testDate)
  {
    $this->testDate = new \DateTime($testDate);
  }

  /**
   * Get testDate
   *
   * @return DateTime $testDate
   */
  public function getTestDate()
  {
    return $this->testDate;
  }

  /**
   * Set testType
   *
   * @param string $testType
   */
  public function setTestType($testType)
  {
    $this->testType = $testType;
  }

  /**
   * Get testType
   *
   * @return string $testType
   */
  public function getTestType()
  {
    return $this->testType;
  }

  /**
   * Set listeningIndicator
   *
   * @param integer $listeningIndicator
   */
  public function setListeningIndicator($listeningIndicator)
  {
    $this->listeningIndicator = $listeningIndicator;
  }

  /**
   * Get listeningIndicator
   *
   * @return integer $listeningIndicator
   */
  public function getListeningIndicator()
  {
    return $this->listeningIndicator;
  }

  /**
   * Set speakingIndicator
   *
   * @param integer $speakingIndicator
   */
  public function setSpeakingIndicator($speakingIndicator)
  {
    $this->speakingIndicator = $speakingIndicator;
  }

  /**
   * Get speakingIndicator
   *
   * @return integer $speakingIndicator
   */
  public function getSpeakingIndicator()
  {
    return $this->speakingIndicator;
  }

  /**
   * Set IBTListening
   *
   * @param integer $iBTListening
   */
  public function setIBTListening($ibtListening)
  {
    $this->ibtListening = $ibtListening;
  }

  /**
   * Get IBTListening
   *
   * @return integer $iBTListening
   */
  public function getIBTListening()
  {
    return $this->ibtListening;
  }

  /**
   * Set IBTWriting
   *
   * @param integer $iBTWriting
   */
  public function setIBTWriting($ibtWriting)
  {
    $this->ibtWriting = $ibtWriting;
  }

  /**
   * Get IBTWriting
   *
   * @return integer $iBTWriting
   */
  public function getIBTWriting()
  {
    return $this->ibtWriting;
  }

  /**
   * Set IBTSpeaking
   *
   * @param integer $iBTSpeaking
   */
  public function setIBTSpeaking($ibtSpeaking)
  {
    $this->ibtSpeaking = $ibtSpeaking;
  }

  /**
   * Get IBTSpeaking
   *
   * @return integer $iBTSpeaking
   */
  public function getIBTSpeaking()
  {
    return $this->ibtSpeaking;
  }

  /**
   * Set IBTReading
   *
   * @param integer $iBTReading
   */
  public function setIBTReading($ibtReading)
  {
    $this->ibtReading = $ibtReading;
  }

  /**
   * Get IBTReading
   *
   * @return integer $iBTReading
   */
  public function getIBTReading()
  {
    return $this->ibtReading;
  }

  /**
   * Set IBTTotal
   *
   * @param integer $iBTTotal
   */
  public function setIBTTotal($ibtTotal)
  {
    $this->ibtTotal = $ibtTotal;
  }

  /**
   * Get IBTTotal
   *
   * @return integer $iBTTotal
   */
  public function getIBTTotal()
  {
    return $this->ibtTotal;
  }

  /**
   * Set TSEScore
   *
   * @param integer $tSEScore
   */
  public function setTSEScore($tseScore)
  {
    $this->tseScore = $tseScore;
  }

  /**
   * Get TSEScore
   *
   * @return integer $tSEScore
   */
  public function getTSEScore()
  {
    return $this->tseScore;
  }

  /**
   * Set listening
   *
   * @param integer $listening
   */
  public function setListening($listening)
  {
    $this->listening = $listening;
  }

  /**
   * Get listening
   *
   * @return integer $listening
   */
  public function getListening()
  {
    return $this->listening;
  }

  /**
   * Set writing
   *
   * @param integer $writing
   */
  public function setWriting($writing)
  {
    $this->writing = $writing;
  }

  /**
   * Get writing
   *
   * @return integer $writing
   */
  public function getWriting()
  {
    return $this->writing;
  }

  /**
   * Set reading
   *
   * @param integer $reading
   */
  public function setReading($reading)
  {
    $this->reading = $reading;
  }

  /**
   * Get reading
   *
   * @return integer $reading
   */
  public function getReading()
  {
    return $this->reading;
  }

  /**
   * Set essay
   *
   * @param integer $essay
   */
  public function setEssay($essay)
  {
    $this->essay = $essay;
  }

  /**
   * Get essay
   *
   * @return integer $essay
   */
  public function getEssay()
  {
    return $this->essay;
  }

  /**
   * Set total
   *
   * @param integer $total
   */
  public function setTotal($total)
  {
    $this->total = $total;
  }

  /**
   * Get total
   *
   * @return integer $total
   */
  public function getTotal()
  {
    return $this->total;
  }

  /**
   * Set timesTaken
   *
   * @param integer $timesTaken
   */
  public function setTimesTaken($timesTaken)
  {
    $this->timesTaken = $timesTaken;
  }

  /**
   * Get timesTaken
   *
   * @return integer $timesTaken
   */
  public function getTimesTaken()
  {
    return $this->timesTaken;
  }

  /**
   * Set offTopic
   *
   * @param string $offTopic
   */
  public function setOffTopic($offTopic)
  {
    $this->offTopic = $offTopic;
  }

  /**
   * Get offTopic
   *
   * @return string $offTopic
   */
  public function getOffTopic()
  {
    return $this->offTopic;
  }

  /**
   * Get all the fields of the score as an array
   *
   * @return array=
   */
  public function getSummary()
  {
    $arr = array(
      'Registration Number' => $this->registrationNumber,
      'First Name' => $this->firstName,
      'Middle Name' => $this->middleName,
      'Last Name' => $this->lastName,
      'Birth Date' => $this->birthDate->format('m/d/Y'),
      'Gender' => $this->gender,
      'Native Country' => $this->nativeCountry,
      'Native Language' => $this->nativeLanguage,
      'Test Date' => $this->testDate->format('m/d/Y'),
      'Test Type' => $this->testType,
      'Listening Indicator' => $this->listeningIndicator,
      'Speaking Indicator' => $this->speakingIndicator,
      'IBT Listenting' => $this->ibtListening,
      'IBT Writing' => $this->ibtWriting,
      'IBT Speaking' => $this->ibtSpeaking,
      'IBT Reading' => $this->ibtReading,
      'IBT Total' => $this->ibtTotal,
      'TSE Score' => $this->tseScore,
      'Listening' => $this->listening,
      'Writing' => $this->writing,
      'Reading' => $this->reading,
      'Essay' => $this->essay,
      'Total' => $this->total,
      'Times Taken' => $this->timesTaken,
      'Off Topic' => $this->offTopic
    );

    return $arr;
  }

}