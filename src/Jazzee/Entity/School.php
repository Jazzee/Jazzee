<?php
namespace Jazzee\Entity;

/**
 * School
 *
 * @Entity(repositoryClass="\Jazzee\Entity\SchoolRepository")
 * @Table(name="schools",
 * uniqueConstraints={@UniqueConstraint(name="fullName",columns={"fullName"})})
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * */
class School
{

  /**
   * @Id
   * @Column(type="bigint")
   * @GeneratedValue(strategy="AUTO")
   */
  private $id;

  /** @Column(type="string", length=255, nullable=false) */
  protected $fullName;

  /** @Column(type="string", length=255, nullable=false) */
  protected $shortName;

  /** @Column(type="string", length=255, nullable=false) */
  protected $address1;

  /** @Column(type="string", length=255, nullable=false) */
  protected $address2;

  /** @Column(type="string", length=64) */
  protected $zip;

  /** @Column(type="string", length=64) */
  protected $state;

  /** @Column(type="string", length=64) */
  protected $city;

  /** @Column(type="string", length=128) */
  protected $country;

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
   * Set fullName
   *
   * @param string $fullName
   */
  public function setFullName($fullName)
  {
    $this->fullName = $fullName;
  }

  /**
   * Get the page status
   *
   * @return string $fullName
   */
  public function getFullName()
  {
    return $this->fullName;
  }


  /**
   * Set shortName
   *
   * @param string $shortName
   */
  public function setShortName($shortName)
  {
    $this->shortName = $shortName;
  }

  /**
   * Get the page status
   *
   * @return string $shortName
   */
  public function getShortName()
  {
    return $this->shortName;
  }


  /**
   * Set zip
   *
   * @param string $zip
   */
  public function setZip($zip)
  {
    $this->zip = $zip;
  }

  /**
   * Get the zip
   *
   * @return string $zip
   */
  public function getZip()
  {
    return $this->zip;
  }

  /**
   * Set state
   *
   * @param string $state
   */
  public function setState($state)
  {
    $this->state = $state;
  }

  /**
   * Get the state
   *
   * @return string $state
   */
  public function getState()
  {
    return $this->state;
  }

  /**
   * Set city
   *
   * @param string $city
   */
  public function setCity($city)
  {
    $this->city = $city;
  }

  /**
   * Get the city
   *
   * @return string $city
   */
  public function getCity()
  {
    return $this->city;
  }


  /**
   * Set country
   *
   * @param string $country
   */
  public function setCountry($country)
  {
    $this->country = $country;
  }

  /**
   * Get the country
   *
   * @return string $country
   */
  public function getCountry()
  {
    return $this->country;
  }

  /**
   * Set address1
   *
   * @param string $address1
   */
  public function setAddress1($address1)
  {
    $this->address1 = $address1;
  }

  /**
   * Get the address1
   *
   * @return string $address1
   */
  public function getAddress1()
  {
    return $this->address1;
  }

  /**
   * Set address2
   *
   * @param string $address2
   */
  public function setAddress2($address2)
  {
    $this->address2 = $address2;
  }

  /**
   * Get the address2
   *
   * @return string $address2
   */
  public function getAddress2()
  {
    return $this->address2;
  }


}