<?php
namespace Jazzee\Entity;

/**
 * GREScore
 * Scores imported from ETS
 *
 * @Entity(repositoryClass="\Jazzee\Entity\GREScoreRepository")
 * @Table(name="gre_scores",uniqueConstraints={@UniqueConstraint(name="gre_registration", columns={"registrationNumber", "testMonth", "testYear"})})
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class GREScore
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
  private $departmentName;

  /** @Column(type="string") */
  private $firstName;

  /** @Column(type="string", length=1, nullable=true) */
  private $middleInitial;

  /** @Column(type="string") */
  private $lastName;

  /** @Column(type="datetime") */
  private $birthDate;

  /** @Column(type="string", length=1, nullable=true) */
  private $gender;

  /** @Column(type="datetime") */
  private $testDate;

  /** @Column(type="string", length=2) */
  private $testCode;

  /** @Column(type="string") */
  private $testName;

  /** @Column(type="string", length=1) */
  private $score1Type;

  /** @Column(type="decimal", precision=10, scale=1) */
  private $score1Converted;

  /** @Column(type="decimal", length=3) */
  private $score1Percentile;

  /** @Column(type="string", length=1, nullable=true) */
  private $score2Type;

  /** @Column(type="decimal", precision=10, scale=1, nullable=true) */
  private $score2Converted;

  /** @Column(type="decimal", length=3, nullable=true) */
  private $score2Percentile;

  /** @Column(type="string", length=1, nullable=true) */
  private $score3Type;

  /** @Column(type="decimal", precision=10, scale=1, nullable=true) */
  private $score3Converted;

  /** @Column(type="decimal", length=3, nullable=true) */
  private $score3Percentile;

  /** @Column(type="string", length=1, nullable=true) */
  private $score4Type;

  /** @Column(type="decimal", precision=10, scale=1, nullable=true) */
  private $score4Converted;

  /** @Column(type="decimal", length=3, nullable=true) */
  private $score4Percentile;

  /** @Column(type="integer", length=4) */
  private $sequenceNumber;

  /** @Column(type="integer", length=2) */
  private $recordSerialNumber;

  /** @Column(type="integer", length=4) */
  private $cycleNumber;

  /** @Column(type="datetime") */
  private $processDate;

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
      throw new Jazzee_Exception("{$testMonth} is not a valid month");
    }
    if ($testYear < 1900 OR $testMonth > 2100) {
      throw new Jazzee_Exception("{$testYear} is not a valid year");
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
   * Set departmentName
   *
   * @param string $departmentName
   */
  public function setDepartmentName($departmentName)
  {
    $this->departmentName = $departmentName;
  }

  /**
   * Get departmentName
   *
   * @return string $departmentName
   */
  public function getDepartmentName()
  {
    return $this->departmentName;
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
   * Set middleInitial
   *
   * @param string $middleInitial
   */
  public function setMiddleInitial($middleInitial)
  {
    $this->middleInitial = $middleInitial;
  }

  /**
   * Get middleInitial
   *
   * @return string $middleInitial
   */
  public function getMiddleInitial()
  {
    return $this->middleInitial;
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
   * @return datetime $birthDate
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
    if (is_null($gender)) {
      return;
    }
    if (!in_array(strtolower($gender), array('m', 'f'))) {
      throw new \Jazzee_Exception("'{$gender}' is not a valid gender");
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
   * Set testCode
   *
   * @param string $testCode
   */
  public function setTestCode($testCode)
  {
    $this->testCode = $testCode;
  }

  /**
   * Get testCode
   *
   * @return string $testCode
   */
  public function getTestCode()
  {
    return $this->testCode;
  }

  /**
   * Set testName
   *
   * @param string $testName
   */
  public function setTestName($testName)
  {
    $this->testName = $testName;
  }

  /**
   * Get testName
   *
   * @return string $testName
   */
  public function getTestName()
  {
    return $this->testName;
  }

  /**
   * Set score1Type
   *
   * @param string $score1Type
   */
  public function setScore1Type($score1Type)
  {
    $this->score1Type = $score1Type;
  }

  /**
   * Get score1Type
   *
   * @return string $score1Type
   */
  public function getScore1Type()
  {
    return $this->score1Type;
  }

  /**
   * Set score1Converted
   *
   * @param integer $score1Converted
   */
  public function setScore1Converted($score1Converted)
  {
    $this->score1Converted = $score1Converted;
  }

  /**
   * Get score1Converted
   *
   * @return integer $score1Converted
   */
  public function getScore1Converted()
  {
    return $this->score1Converted;
  }

  /**
   * Set score1Percentile
   *
   * @param decimal $score1Percentile
   */
  public function setScore1Percentile($score1Percentile)
  {
    $this->score1Percentile = $score1Percentile;
  }

  /**
   * Get score1Percentile
   *
   * @return decimal $score1Percentile
   */
  public function getScore1Percentile()
  {
    return $this->score1Percentile;
  }

  /**
   * Set score2Type
   *
   * @param string $score2Type
   */
  public function setscore2Type($score2Type)
  {
    $this->score2Type = $score2Type;
  }

  /**
   * Get score2Type
   *
   * @return string $score2Type
   */
  public function getscore2Type()
  {
    return $this->score2Type;
  }

  /**
   * Set score2Converted
   *
   * @param integer $score2Converted
   */
  public function setscore2Converted($score2Converted)
  {
    $this->score2Converted = $score2Converted;
  }

  /**
   * Get score2Converted
   *
   * @return integer $score2Converted
   */
  public function getscore2Converted()
  {
    return $this->score2Converted;
  }

  /**
   * Set score2Percentile
   *
   * @param decimal $score2Percentile
   */
  public function setscore2Percentile($score2Percentile)
  {
    $this->score2Percentile = $score2Percentile;
  }

  /**
   * Get score2Percentile
   *
   * @return decimal $score2Percentile
   */
  public function getscore2Percentile()
  {
    return $this->score2Percentile;
  }

  /**
   * Set score3Type
   *
   * @param string $score3Type
   */
  public function setscore3Type($score3Type)
  {
    $this->score3Type = $score3Type;
  }

  /**
   * Get score3Type
   *
   * @return string $score3Type
   */
  public function getscore3Type()
  {
    return $this->score3Type;
  }

  /**
   * Set score3Converted
   *
   * @param integer $score3Converted
   */
  public function setscore3Converted($score3Converted)
  {
    $this->score3Converted = $score3Converted;
  }

  /**
   * Get score3Converted
   *
   * @return integer $score3Converted
   */
  public function getscore3Converted()
  {
    return $this->score3Converted;
  }

  /**
   * Set score3Percentile
   *
   * @param decimal $score3Percentile
   */
  public function setscore3Percentile($score3Percentile)
  {
    $this->score3Percentile = $score3Percentile;
  }

  /**
   * Get score3Percentile
   *
   * @return decimal $score3Percentile
   */
  public function getscore3Percentile()
  {
    return $this->score3Percentile;
  }

  /**
   * Set score4Type
   *
   * @param string $score4Type
   */
  public function setscore4Type($score4Type)
  {
    $this->score4Type = $score4Type;
  }

  /**
   * Get score4Type
   *
   * @return string $score4Type
   */
  public function getscore4Type()
  {
    return $this->score4Type;
  }

  /**
   * Set score4Converted
   *
   * @param integer $score4Converted
   */
  public function setscore4Converted($score4Converted)
  {
    $this->score4Converted = $score4Converted;
  }

  /**
   * Get score4Converted
   *
   * @return integer $score4Converted
   */
  public function getscore4Converted()
  {
    return $this->score4Converted;
  }

  /**
   * Set score4Percentile
   *
   * @param decimal $score4Percentile
   */
  public function setscore4Percentile($score4Percentile)
  {
    $this->score4Percentile = $score4Percentile;
  }

  /**
   * Get score4Percentile
   *
   * @return decimal $score4Percentile
   */
  public function getscore4Percentile()
  {
    return $this->score4Percentile;
  }

  /**
   * Set sequenceNumber
   *
   * @param integer $sequenceNumber
   */
  public function setSequenceNumber($sequenceNumber)
  {
    $this->sequenceNumber = $sequenceNumber;
  }

  /**
   * Get sequenceNumber
   *
   * @return integer $sequenceNumber
   */
  public function getSequenceNumber()
  {
    return $this->sequenceNumber;
  }

  /**
   * Set recordSerialNumber
   *
   * @param integer $recordSerialNumber
   */
  public function setRecordSerialNumber($recordSerialNumber)
  {
    $this->recordSerialNumber = $recordSerialNumber;
  }

  /**
   * Get recordSerialNumber
   *
   * @return integer $recordSerialNumber
   */
  public function getRecordSerialNumber()
  {
    return $this->recordSerialNumber;
  }

  /**
   * Set cycleNumber
   *
   * @param integer $cycleNumber
   */
  public function setCycleNumber($cycleNumber)
  {
    $this->cycleNumber = $cycleNumber;
  }

  /**
   * Get cycleNumber
   *
   * @return integer $cycleNumber
   */
  public function getCycleNumber()
  {
    return $this->cycleNumber;
  }

  /**
   * Set processDate
   *
   * @param string $processDate
   */
  public function setProcessDate($processDate)
  {
    $this->processDate = new \DateTime($processDate);
  }

  /**
   * Get processDate
   *
   * @return DateTime $processDate
   */
  public function getProcessDate()
  {
    return $this->processDate;
  }

  /**
   * Get all the fields of the score as an array
   *
   * @return array
   */
  public function getSummary()
  {
    $arr = array(
      'Registration Number' => $this->registrationNumber,
      'Department Name' => $this->departmentName,
      'First Name' => $this->firstName,
      'Middle Initial' => $this->middleInitial,
      'Last Name' => $this->lastName,
      'Birth Date' => $this->birthDate->format('m/d/Y'),
      'Gender' => $this->gender,
      'Test Date' => $this->testDate->format('m/d/Y'),
      'Test Name' => $this->testName,
      'Score 1' => $this->score1Type . ' ' . $this->score1Converted . ' ' . $this->score1Percentile . '%',
      'Score 2' => $this->score2Type . ' ' . $this->score2Converted . ' ' . $this->score2Percentile . '%',
      'Score 3' => $this->score3Type . ' ' . $this->score3Converted . ' ' . $this->score3Percentile . '%',
      'Score 4' => $this->score4Type . ' ' . $this->score4Converted . ' ' . $this->score4Percentile . '%'
    );

    return $arr;
  }

}