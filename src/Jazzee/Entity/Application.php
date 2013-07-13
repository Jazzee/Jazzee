<?php
namespace Jazzee\Entity;

/**
 * Application
 * Cycle+Program=Application
 * Sets the unique preferences for a givien Cycle/Program and contains all of its Applicants
 *
 * @Entity(repositoryClass="\Jazzee\Entity\ApplicationRepository")
 * @Table(name="applications",uniqueConstraints={@UniqueConstraint(name="program_cycle", columns={"program_id", "cycle_id"})})
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class Application
{

  /**
   * The prefix to use for application caching
   * @var string
   */
  const ARRAY_CACHE_PREFIX = 'JazzeeApplicationarray-';

  /**
   * @Id
   * @Column(type="bigint")
   * @GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @ManyToOne(targetEntity="Program")
   * @JoinColumn(onDelete="CASCADE")
   */
  private $program;

  /**
   * @ManyToOne(targetEntity="Cycle", inversedBy="applications")
   * @JoinColumn(onDelete="CASCADE")
   */
  private $cycle;

  /**
   * @OneToMany(targetEntity="Applicant", mappedBy="application")
   */
  private $applicants;

  /**
   * @OneToMany(targetEntity="ApplicationPage", mappedBy="application")
   * @OrderBy({"weight" = "ASC"})
   */
  private $applicationPages;

  /**
   * @OneToMany(targetEntity="PDFTemplate", mappedBy="application")
   */
  private $pdfTemplates;

  /** @Column(type="string", nullable=true) */
  private $contactName;

  /** @Column(type="string", nullable=true) */
  private $contactEmail;

  /** @Column(type="text", nullable=true) */
  private $welcome;

  /** @Column(type="datetime", nullable=true) */
  private $open;

  /** @Column(type="datetime", nullable=true) */
  private $close;

  /** @Column(type="datetime", nullable=true) */
  private $begin;

  /** @Column(type="boolean") */
  private $published;

  /** @Column(type="boolean") */
  private $byInvitationOnly;

  /** @Column(type="boolean") */
  private $visible;

  /** @Column(type="text", nullable=true) */
  private $admitLetter;

  /** @Column(type="text", nullable=true) */
  private $denyLetter;

  /** @Column(type="text", nullable=true) */
  private $statusIncompleteText;

  /** @Column(type="text", nullable=true) */
  private $statusNoDecisionText;

  /** @Column(type="text", nullable=true) */
  private $statusAdmitText;

  /** @Column(type="text", nullable=true) */
  private $statusDenyText;

  /** @Column(type="text", nullable=true) */
  private $statusAcceptText;

  /** @Column(type="text", nullable=true) */
  private $statusDeclineText;

  /** @Column(type="text", nullable=true) */
  private $statusDeactivatedText;

  /** @Column(type="string", nullable=true) */
  private $externalIdValidationExpression;

  public function __construct()
  {
    $this->applicants = new \Doctrine\Common\Collections\ArrayCollection();
    $this->published = false;
    $this->visible = false;
    $this->byInvitationOnly = false;
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
   * Set contactName
   *
   * @param string $contactName
   */
  public function setContactName($contactName)
  {
    $this->contactName = $contactName;
  }

  /**
   * Get contactName
   *
   * @return string $contactName
   */
  public function getContactName()
  {
    return $this->contactName;
  }

  /**
   * Set contactEmail
   *
   * @param string $contactEmail
   */
  public function setContactEmail($contactEmail)
  {
    $this->contactEmail = $contactEmail;
  }

  /**
   * Get contactEmail
   *
   * @return string $contactEmail
   */
  public function getContactEmail()
  {
    return $this->contactEmail;
  }

  /**
   * Set welcome
   *
   * @param text $welcome
   */
  public function setWelcome($welcome)
  {
    $this->welcome = $welcome;
  }

  /**
   * Get welcome
   *
   * @return text $welcome
   */
  public function getWelcome()
  {
    return $this->welcome;
  }

  /**
   * Set open
   *
   * @param string $open
   */
  public function setOpen($open)
  {
    if (empty($open)) {
      $this->open = null;
    } else {
      $this->open = new \DateTime($open);
    }
  }

  /**
   * Get open
   *
   * @return \DateTime $open
   */
  public function getOpen()
  {
    return $this->open;
  }

  /**
   * Set close
   *
   * @param string $close
   */
  public function setClose($close)
  {
    if (empty($close)) {
      $this->close = null;
    } else {
      $this->close = new \DateTime($close);
    }
  }

  /**
   * Get close
   *
   * @return DateTime $close
   */
  public function getClose()
  {
    return $this->close;
  }

  /**
   * Set begin
   *
   * @param string $begin
   */
  public function setBegin($begin)
  {
    if (empty($begin)) {
      $this->begin = null;
    } else {
      $this->begin = new \DateTime($begin);
    }
  }

  /**
   * Get begin
   *
   * @return \DateTime $begin
   */
  public function getBegin()
  {
    return $this->begin;
  }

  /**
   * Publish and application
   * @param boolean $override if this is true the application willbe published without checking
   */
  public function publish($override = false)
  {
    if (!$this->canPublish() or (!$override AND !$this->shouldPublish())) {
      throw new \Jazzee\Exception('Application cannot be published, it is not ready.');
    }
    $this->published = true;
  }

  /**
   * Check if application is ready to be published
   * @return boolean
   */
  public function canPublish()
  {
    if($this->open == null){
      return false;
    }
    
    if(!$this->byInvitationOnly and $this->close == null){
      return false;
    }

    return true;
  }

  /**
   * Check if application is ready to be published or if there are non - blocking errors
   * which can be ignored
   * 
   * @return boolean
   */
  public function shouldPublish()
  {
    if(!$this->canPublish()){
      return false;
    }

    foreach ($this->cycle->getRequiredPages() as $requiredPage) {
      if (!$this->hasPage($requiredPage)) {
        return false;
      }
    }

    return true;
  }

  /**
   * Un Publish and application
   */
  public function unPublish()
  {
    $this->published = false;
  }

  /**
   * Get published status
   * @return boolean $published
   */
  public function isPublished()
  {
    return $this->published;
  }

  /**
   * Make Application Visible
   */
  public function visible()
  {
    $this->visible = true;
  }

  /**
   * Make Application InVisible
   */
  public function inVisible()
  {
    $this->visible = false;
  }

  /**
   * Get visible status
   *
   * @return boolean $visible
   */
  public function isVisible()
  {
    return $this->visible;
  }

  /**
   * Dont allow applicant to create accounts, only applicants invited to apply
   * can login
   */
  public function byInvitationOnly()
  {
    $this->byInvitationOnly = true;
  }

  /**
   * Allow anyone to create an account and apply.
   */
  public function notByInvitationOnly()
  {
    $this->byInvitationOnly = false;
  }

  /**
   * Get invitationOnly
   *
   * @return boolean
   */
  public function isByInvitationOnly()
  {
    return $this->byInvitationOnly;
  }

  /**
   * Set admitLetter
   *
   * @param text $admitLetter
   */
  public function setAdmitLetter($admitLetter)
  {
    $this->admitLetter = $admitLetter;
  }

  /**
   * Get admitLetter
   *
   * @return text $admitLetter
   */
  public function getAdmitLetter()
  {
    return $this->admitLetter;
  }

  /**
   * Set denyLetter
   *
   * @param text $denyLetter
   */
  public function setDenyLetter($denyLetter)
  {
    $this->denyLetter = $denyLetter;
  }

  /**
   * Get denyLetter
   *
   * @return text $denyLetter
   */
  public function getDenyLetter()
  {
    return $this->denyLetter;
  }

  /**
   * Set statusIncompleteText
   *
   * @param text $statusIncompleteText
   */
  public function setStatusIncompleteText($statusIncompleteText)
  {
    $this->statusIncompleteText = $statusIncompleteText;
  }

  /**
   * Get statusIncompleteText
   *
   * @return text $statusIncompleteText
   */
  public function getStatusIncompleteText()
  {
    return $this->statusIncompleteText;
  }

  /**
   * Set statusDeactivatedText
   *
   * @param string $text
   */
  public function setStatusDeactivatedText($text)
  {
    $this->statusDeactivatedText = $text;
  }

  /**
   * Get statusDeactivatedText
   *
   * @return string
   */
  public function getStatusDeactivatedText()
  {
    return $this->statusDeactivatedText;
  }

  /**
   * Set statusNoDecisionText
   *
   * @param text $statusNoDecisionText
   */
  public function setStatusNoDecisionText($statusNoDecisionText)
  {
    $this->statusNoDecisionText = $statusNoDecisionText;
  }

  /**
   * Get statusNoDecisionText
   *
   * @return text $statusNoDecisionText
   */
  public function getStatusNoDecisionText()
  {
    return $this->statusNoDecisionText;
  }

  /**
   * Set statusAdmitText
   *
   * @param text $statusAdmitText
   */
  public function setStatusAdmitText($statusAdmitText)
  {
    $this->statusAdmitText = $statusAdmitText;
  }

  /**
   * Get statusAdmitText
   *
   * @return text $statusAdmitText
   */
  public function getStatusAdmitText()
  {
    return $this->statusAdmitText;
  }

  /**
   * Set statusDenyText
   *
   * @param text $statusDenyText
   */
  public function setStatusDenyText($statusDenyText)
  {
    $this->statusDenyText = $statusDenyText;
  }

  /**
   * Get statusDenyText
   *
   * @return text $statusDenyText
   */
  public function getStatusDenyText()
  {
    return $this->statusDenyText;
  }

  /**
   * Set statusAcceptText
   *
   * @param text $statusAcceptText
   */
  public function setStatusAcceptText($statusAcceptText)
  {
    $this->statusAcceptText = $statusAcceptText;
  }

  /**
   * Get statusAcceptText
   *
   * @return text $statusAcceptText
   */
  public function getStatusAcceptText()
  {
    return $this->statusAcceptText;
  }

  /**
   * Set statusDeclineText
   *
   * @param text $statusDeclineText
   */
  public function setStatusDeclineText($statusDeclineText)
  {
    $this->statusDeclineText = $statusDeclineText;
  }

  /**
   * Get statusDeclineText
   *
   * @return text $statusDeclineText
   */
  public function getStatusDeclineText()
  {
    return $this->statusDeclineText;
  }

  /**
   * Set program
   *
   * @param Entity\Program $program
   */
  public function setProgram(Program $program)
  {
    $this->program = $program;
  }

  /**
   * Get program
   *
   * @return Entity\Program $program
   */
  public function getProgram()
  {
    return $this->program;
  }

  /**
   * Set cycle
   *
   * @param Entity\Cycle $cycle
   */
  public function setCycle(Cycle $cycle)
  {
    $this->cycle = $cycle;
  }

  /**
   * Get cycle
   *
   * @return Entity\Cycle $cycle
   */
  public function getCycle()
  {
    return $this->cycle;
  }

  /**
   * Get applicants
   *
   * @return \Doctrine\Common\Collections\Collection \Jazzee\Entity\Applicant
   */
  public function getApplicants()
  {
    return $this->applicants;
  }

  /**
   * Get pages
   * @param string $kind optionally only incude certain pages
   * @return array \Jazzee\Entity\ApplicationPage
   */
  public function getApplicationPages($kind = null)
  {
    if (!$this->applicationPages) {
      return array();
    }
    if (is_null($kind)) {
      return $this->applicationPages->toArray();
    }
    $applicationPages = array();
    foreach ($this->applicationPages as $applicationPage) {
      if ($applicationPage->getKind() == $kind) {
        $applicationPages[] = $applicationPage;
      }
    }

    return $applicationPages;
  }

  /**
   * Check if application has a page
   * @param Page $page
   * @return boolean
   */
  public function hasPage(Page $page)
  {
    if (!$this->applicationPages) {
      return false;
    }
    foreach ($this->applicationPages as $applicationPage) {
      if ($applicationPage->getPage() == $page) {
        return true;
      }
    }

    return false;
  }

  /**
   * Get an application page by its page ID
   * @param integer $id
   * @return ApplicationPage
   */
  public function getApplicationPageByPageId($id)
  {
    foreach ($this->applicationPages as $applicationPage) {
      if ($applicationPage->getPage()->getId() == $id) {
        return $applicationPage;
      }
    }

    return false;
  }

  /**
   * Get an application page by its page ID
   * @param integer $id
   * @return ApplicationPage
   */
  public function getApplicationPageById($id)
  {
    foreach ($this->applicationPages as $applicationPage) {
      if ($applicationPage->getId() == $id) {
        return $applicationPage;
      }
    }

    return false;
  }

  /**
   * Get page by tile
   * @param string $title
   * @return ApplicationPage
   */
  public function getApplicationPageByTitle($title)
  {
    foreach ($this->applicationPages as $applicationPage) {
      if ($applicationPage->getTitle() == $title) {
        return $applicationPage;
      }
    }

    return false;
  }

  /**
   * Get page by name
   *
   * @param string $name
   * @return ApplicationPage
   */
  public function getApplicationPageByName($name)
  {
    foreach ($this->applicationPages as $applicationPage) {
      if ($applicationPage->getName() == $name) {
        return $applicationPage;
      }
    }

    return false;
  }

  /**
   * Get an element by its ID
   * search all the pages to find it
   * 
   * @param integer $id
   * @return Element
   */
  public function getElementById($id)
  {
    foreach ($this->applicationPages as $applicationPage) {
      if ($element = $applicationPage->getPage()->getElementById($id)) {
        return $element;
      }
    }

    return false;
  }

  /**
   * Get an application page the id of one of its children
   * @param integer $id
   * @return ApplicationPage
   */
  public function getApplicationPageByChildPageId($id)
  {
    foreach ($this->applicationPages as $applicationPage) {
      foreach($applicationPage->getPage()->getChildren() as $child){
        if($child->getId() == $id){
          return $applicationPage;
        }
      }
    }

    return false;
  }

  /**
   * Compare two application and list the differences
   *
   * @param \Jazzee\Entity\Application $application
   * @return array
   */
  public function compareWith(Application $application)
  {
    $differences = array(
      'properties' => array(),
      'pages' => array(
        'new' => array(),
        'removed' => array(),
        'same' => array(),
        'changed' => array()
      )
    );
    $arr = array(
      'contactName' => 'Contact Name',
      'contactEmail' => 'Contact Email',
      'welcome' => 'Welcome Message',
      'admitLetter' => 'Admit Letter',
      'denyLetter' => 'Deny Letter',
      'statusIncompleteText' => 'Incomplete Status Text',
      'statusNoDecisionText' => 'No Decision Status Text',
      'statusAdmitText' => 'Admitted Status Text',
      'statusDenyText' => 'Denied Status Text',
      'statusAcceptText' => 'Accepted Status Text',
      'statusDeclineText' => 'Declined Status Text'
    );

    foreach($arr as $name => $niceName){
      $func = 'get' . ucfirst($name);
      if($this->$func() != $application->$func()){
        $differences['properties'][] = array(
          'name' => $niceName,
          'type' => 'textdiff',
          'this' => $this->$func(),
          'other' => $application->$func()
        );
      }
    }
    $arr = array(
      'open' => 'Open Date',
      'close' => 'Close Date',
      'begin' => 'Classes Begin'
    );

    foreach($arr as $name => $niceName){
      $func = 'get' . ucfirst($name);
      if($this->$func() != $application->$func()){
        $differences['properties'][] = array(
          'name' => $niceName,
          'type' => 'datediff',
          'this' => $this->$func()->format('c'),
          'other' => $application->$func()->format('c')
        );
      }
    }
    if($this->isPublished() != $application->isPublished()){
      $differences['properties'][] = array(
        'name' => 'Published',
        'type' => 'booldiff',
        'this' => $this->isPublished(),
        'other' => $application->isPublished()
      );
    }
    if($this->isVisible() != $application->isVisible()){
      $differences['properties'][] = array(
        'name' => 'Visible',
        'type' => 'booldiff',
        'this' => $this->isVisible(),
        'other' => $application->isVisible()
      );
    }
    $thisPages = array();
    foreach($this->getApplicationPages(ApplicationPage::APPLICATION) as $applicationPage){
      $thisPages[$applicationPage->getTitle()] = $applicationPage;
    }
    $otherPages = array();
    foreach($application->getApplicationPages(ApplicationPage::APPLICATION) as $applicationPage){
      $otherPages[$applicationPage->getTitle()] = $applicationPage;
    }
    foreach($thisPages as $title => $applicationPage){
      $pageTitle = $title;
      if($applicationPage->getPage()->isGlobal()){
        $pageTitle .= '(' . $applicationPage->getPage()->getTitle() . ')';
      }
      if(!array_key_exists($title, $otherPages)){
        $differences['pages']['new'][] = $pageTitle;
      } else if($applicationPage->getPage()->getType()->getId() != $otherPages[$title]->getPage()->getType()->getId()){
        $differences['pages']['new'][] = $pageTitle;
        if($otherPages[$title]->getPage()->isGlobal()){
          $pageTitle = $title . '(' . $otherPages[$title]->getPage()->getTitle() . ')';
        }
        $differences['pages']['removed'][] = $pageTitle;
      } else if($applicationPage->getPage()->isGlobal() AND $applicationPage->getPage()->getId() != $otherPages[$title]->getPage()->getId()){
        //different global pages
        $differences['pages']['new'][] = $title . '(' . $applicationPage->getPage()->getTitle() . ')';
        $differences['pages']['removed'][] = $title . '(' . $otherPages[$title]->getPage()->getTitle() . ')';
      } else {
        $pageDifferences = $applicationPage->getJazzeePage()->compareWith($otherPages[$title]);
        if($pageDifferences['different']){
          $differences['pages']['changed'][] = $pageDifferences;
        }
      }
    }
    foreach($otherPages as $title => $array){
      if(!array_key_exists($title, $thisPages)){
        $differences['pages']['removed'][] = $title;
      }
    }
    return $differences;
  }
  
  /**
   * Format applicant Array
   *
   * @param array $applicant
   * 
   * @return array
   */
  public function formatApplicantArray(array $applicant)
  {
    $answers = $applicant['answers'];
    unset($applicant['answers']);
    $applicant['pages'] = array();
    foreach($answers as $pageId => $answers){
      if($applicationPage = $this->getApplicationPageByPageId($pageId) and is_a($applicationPage->getJazzeePage(), '\Jazzee\Interfaces\DataPage')){
        $applicant['pages'][] = $applicationPage->getJazzeePage()->formatApplicantArray($answers);
      }
    }

    return $applicant;
  }
  
  /**
   * Format applicant Array removing non-display values so the resulting array 
   * is smaller
   *
   * @param array $applicant
   * 
   * @return array
   */
  public function formatApplicantDisplayArray(array $applicant)
  {
    $applicant = $this->formatApplicantArray($applicant);
    $fileStore = \Jazzee\Globals::getFileStore();
    $attachments = array();
    foreach($applicant['attachments'] as $attachment){
      $base = $applicant['fullName'] . '_attachment_' . '_' . $attachment['id'];
      //remove slashes in path to fix an apache issues with encoding slashes in redirects
      $base = str_replace(array('/', '\\'),'slash' , $base);
      $name = $base . '.pdf';
      $fileStore->createSessionFile($name, $attachment['attachmentHash']);
      $attachment['filePath'] = \Jazzee\Globals::path('file/' . \urlencode($name));
      if ($attachment['thumbnailHash'] != null) {
        $name = $base . '.png';
        $fileStore->createSessionFile($name, $attachment['thumbnailHash']);
        $attachment['thumbnailPath'] = \Jazzee\Globals::path('file/' . \urlencode($name));
      } else {
        $attachment['thumbnailPath'] = \Jazzee\Globals::path('resource/foundation/media/default_pdf_logo.png');
      }
      $attachment['displayValue'] = "<a href='{$attachment['filePath']}'><img src='{$attachment['thumbnailPath']}' /></a>";
      $attachments[] = $attachment;
    }
    $applicant['attachments'] = $attachments;

    return $applicant;
  }
  
  /**
   * Format applicant Array to use in PDF templates
   *
   * @param array $applicant
   * 
   * @return array
   */
  public function formatApplicantPDFTemplateArray(array $applicant)
  {
    $answers = $applicant['answers'];
    unset($applicant['answers']);
    $applicant['pages'] = array();
    foreach($answers as $pageId => $answers){
      if($applicationPage = $this->getApplicationPageByPageId($pageId) and is_a($applicationPage->getJazzeePage(), '\Jazzee\Interfaces\PdfPage')){
        $applicant['pages'][$applicationPage->getPage()->getId()] = $applicationPage->getJazzeePage()->formatApplicantPDFTemplateArray($answers);
      }
    }
    $applicant['attachments'] = array();

    return $applicant;
  }
  
  /**
   * Get all application templates
   * @return array
   */
  public function getTemplates(){
    return $this->pdfTemplates;
  }
  
  /**
   * Add a template to the application
   * @param \Jazzee\Entity\PDFTemplate $template
   */
  public function addTemplate(PDFTemplate $template){
    $this->pdfTemplates[] = $template;
    if($template->getApplication()->getId() != $this->id){
      $template->setApplication($this);
    }
  }
  
  /**
   * Get template by Id
   * @param integer $id
   * @return \Jazzee\Entity\PDFTemplate
   */
  public function getTemplateById($id){
    foreach($this->pdfTemplates as $template){
      if($template->getId() == $id){
        return $template;
      }
    }

    return null;
  }

  /**
   * Delete any references to the application in the cache
   * 
   */
  public function clearCache()
  {
    \Jazzee\Controller::getCache()->delete(self::ARRAY_CACHE_PREFIX . $this->id);
  }
  
  /**
   * Set the externalIdValidationExpression
   * @param type $expression
   */
  public function setExternalIdValidationExpression($expression){
    if (@preg_match($expression, null) === false){
      throw new \Jazzee\Exception("{$expression} is not a valid regular expression.");
    }

    $this->externalIdValidationExpression = $expression;
  }
  
  /**
   * Get the externalIdValidationExpression
   * @return string
   */
  public function getExternalIdValidationExpression(){
    return $this->externalIdValidationExpression;
  }
  
  /**
   * Validate an applicants external ID
   * @param string $externalId
   * @return boolean
   */
  public function validateExternalId($externalId){
    if(empty($this->externalIdValidationExpression)){
      return true;
    }

    return (preg_match($this->externalIdValidationExpression, $externalId) === 1);
  }

}