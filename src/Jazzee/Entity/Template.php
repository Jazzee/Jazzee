<?php
namespace Jazzee\Entity;

/**
 * Template
 * Text template for messages or decisions some fo them are user speciifc and some are application wide
 *
 * @Entity
 * @Table(name="templates")
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class Template
{

  /**
   * @Id
   * @Column(type="bigint")
   * @GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @Column(type="string")
   */
  private $type;
  
  /**
   * Decision type constants
   */
  const DECISION_ADMIT = 'decision_admit';
  const DECISION_DENY = 'decision_deny';


  /** @Column(type="string") */
  private $title;

  /** @Column(type="text") */
  private $text;
  /**
   * @ManyToOne(targetEntity="Application")
   * @JoinColumn(onDelete="CASCADE")
   */
  protected $application;

  public function __construct($type)
  {
    if(!in_array($type, array(self::DECISION_ADMIT, self::DECISION_DENY))){
      throw new \Jazzee\Exception("{$type} is not a valid type for Template");
    }
    $this->type = $type;
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
   * Set name
   *
   * @param string $title
   */
  public function setTitle($title)
  {
    $this->title = $title;
  }

  /**
   * Get name
   *
   * @return string $title
   */
  public function getTitle()
  {
    return $this->title;
  }

  /**
   * Set text
   *
   * @param string $text
   */
  public function setText($text)
  {
    $this->text = $text;
  }

  /**
   * Get value
   *
   * @return string $text
   */
  public function getText()
  {
    return $this->text;
  }
  
  /**
   * Set the application
   * 
   * @param Application $application
   */
  public function setApplication(Application $application){
    if(!in_array($this->type, array(self::DECISION_ADMIT, self::DECISION_DENY))){
      throw new \Jazzee\Exception("You cannot set application for Templates that do not have the type 'decision_deny' or 'decision_admin'");
    }
    $this->application = $application;
  }

  /**
   * Get application
   *
   * @return Application
   */
  public function getApplication()
  {
    return $this->application;
  }
  
  /**
   * Get the type
   * 
   * @return string
   */
  public function getType()
  {
      return $this->type;
  }
  
  /**
   * Render the template text
   * @param mixed $search
   * @param mixed $replace
   * @return string
   */
  public function renderText($search, $replace)
  {
      return str_ireplace($search, $replace, $this->text);
  }

}