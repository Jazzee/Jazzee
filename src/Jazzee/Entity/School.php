<?php
namespace Jazzee\Entity;

/**
 * School
 *
 * @Entity(repositoryClass="\Jazzee\Entity\SchoolRepository")
 * @Table(name="schools")
 * @SuppressWarnings(PHPMD.ShortVariable)
 * */
class School
{

  /**
   * @Id
   * @Column(type="bigint")
   * @GeneratedValue(strategy="AUTO")
   */
  private $id;

  /** @Column(type="string", length=255, nullable=false, unique=true) */
  protected $name;

  /** @Column(type="text", nullable=true) */
  protected $searchTerms;

  /** @Column(type="string", length=64, nullable=true) */
  protected $state;

  /** @Column(type="string", length=64, nullable=true) */
  protected $city;

  /** @Column(type="string", length=64, nullable=true) */
  protected $postalCode;

  /** @Column(type="string", length=128, nullable=true) */
  protected $country;

  /** @Column(type="string", nullable=false) */
  private $code;

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
   * Set name
   *
   * @param string $name
   */
  public function setName($name)
  {
    $this->name = $name;
  }

  /**
   * Get name
   *
   * @return string $name
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Set search terms
   *
   * @param string $terms
   */
  public function setSearchTerms($terms)
  {
    $this->searchTerms = $terms;
  }

  /**
   * Get search terms
   *
   * @return string
   */
  public function getSearchTerms()
  {
    return $this->searchTerms;
  }

  /**
   * Set posttalCode
   *
   * @param string $postalCode
   */
  public function setPostalCode($postalCode)
  {
    $this->postalCode = $postalCode;
  }

  /**
   * Get the postalCode
   *
   * @return string
   */
  public function getPostalCode()
  {
    return $this->postalCode;
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
   * Set code
   *
   * @param string $code
   */
  public function setCode($code)
  {
    $this->code = $code;
  }

  /**
   * Get the code
   *
   * @return string
   */
  public function getCode()
  {
    return $this->code;
  }
  
  public function getLocationSummary(){
    $parts = array(
      $this->city,
      $this->state,
      $this->country,
      $this->postalCode
    );

    return implode(' ', $parts);
  }
}