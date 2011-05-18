<?php
namespace Entity;

/** 
 * ApplicationPage
 * Assocaites a Page with an Application.  Allows the application to override many of the page varialbes for global pages
 * @Entity @Table(name="application_pages",uniqueConstraints={@UniqueConstraint(name="application_page", columns={"application_id", "page_id"})}) 
 * @package    jazzee
 * @subpackage orm
 **/
class ApplicationPage{
  /**
    * @Id 
    * @Column(type="bigint")
    * @GeneratedValue(strategy="AUTO")
  */
  private $id;
  
  /** 
   * @ManyToOne(targetEntity="Application", inversedBy="pages")
   * @JoinColumn(onDelete="CASCADE",onUpdate="CASCADE") 
   */
  private $application;
  
  /** 
   * @ManyToOne(targetEntity="Page")
   * @JoinColumn(onDelete="CASCADE",onUpdate="CASCADE") 
   */
  private $page;
  
  /** @Column(type="integer") */
  private $weight;
  
  /** @Column(type="string") */
  private $title;
  
  /** @Column(type="integer", nullable=true) */
  private $min;
  
  /** @Column(type="integer", nullable=true) */
  private $max;
  
  /** @Column(type="boolean") */
  private $isRequired;
  
  /** @Column(type="boolean") */
  private $showAnswerStatus;
  
  /** @Column(type="text", nullable=true) */
  private $instructions;
  
  /** @Column(type="text", nullable=true) */
  private $leadingText;
  
  /** @Column(type="text", nullable=true) */
  private $trailingText;
  
  /**
   * Get the title
   * If the title is not overridde then use the one from Page
   */
  public function getTitle(){
    if(is_null($this->title)) return $this->page->getTitle();
    return $this->title;
  }
  
  /**
   * Set the title
   * If this isn't a global page then store the title in Page and not here
   * @param string $title
   */
  public function setTitle($value){
    if(!$this->page->isGlobal()) $this->page->setTitle($value);
    else $this->title = $value;
  }
  
  /**
   * Get the min
   */
  public function getMin(){
    if(is_null($this->min)) return $this->page->getMin();
    return $this->min;
  }
  
  /**
   * Set the min
   * If this isn't a global page then store the min in Page and not here
   * @param string $value
   */
  public function setMin($value){
    if(!$this->page->isGlobal()) $this->page->setMin($value);
    else $this->min = $value;
  }

  /**
   * Get the max
   */
  public function getMax(){
    if(is_null($this->max)) return $this->page->getMax();
    return $this->max;
  }
  
  /**
   * Set the max
   * If this isn't a global page then store the max in Page and not here
   * @param string $value
   */
  public function setMax($value){
    if(!$this->page->isGlobal()) $this->page->setMax($value);
    else $this->max = $value;
  }
  
  /**
   * Is this page required
   */
  public function isRequired(){
    if(is_null($this->required)) return $this->page->getRequired();
    return $this->required;
  }
  
  /**
   * Make this page as required
   * If this isn't a global page then store the required in Page and not here
   */
  public function required(){
    if(!$this->page->isGlobal()) $this->page->required();
    else $this->isRquired = true;
  }
  
  /**
   * Make this page optional
   * If this isn't a global page then store the required in Page and not here
   */
  public function optional(){
    if(!$this->page->isGlobal()) $this->page->optioal();
    else $this->isRequired = false;
  }
  
  /**
   * Get the instructions
   */
  public function getInstructions(){
    if(is_null($this->instructions)) return $this->page->getInstructions();
    return $this->instructions;
  }
  
  /**
   * Set the instructions
   * If this isn't a global page then store the instructions in Page and not here
   * @param string $value
   */
  public function setInstructions($value){
    if(!$this->page->isGlobal()) $this->page->instructions($value);
    else $this->instructions = $value;
  }
  
  /**
   * Get the leadingText
   */
  public function getLeadingText(){
    if(is_null($this->leadingText)) return $this->page->getLeadignText();
    return $this->leadingText;
  }
  
  /**
   * Set the leadingText
   * If this isn't a global page then store the title in Page and not here
   * @param string $value
   */
  public function setLeadingText($value){
    if(!$this->page->isGlobal()) $this->page->setLeadingText($value);
    else $this->leadingText = $value;
  }
  
  /**
   * Get the trailingText
   */
  public function getTrailingText(){
    if(is_null($this->trailingText)) return $this->page->getTrailingText();
    return $this->trailingText;
  }
  
  /**
   * Set the trailingText
   * If this isn't a global page then store the title in Page and not here
   * @param string $value
   */
  public function setTrailingText($value){
    if(!$this->page->isGlobal()) $this->page->setTrailingText($value);
    else $this->trailingText = $value;
  }
}