<?php
namespace Jazzee\Entity;

/**
 * ApplicationRepository
 * Special Repository methods for Application to make searchign for special conditions easier
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ApplicationRepository extends \Doctrine\ORM\EntityRepository
{

  /**
   * findOneByProgramAndCycle
   * Search for an Application using its Program and Cycle
   * @param Program $program
   * @param Cycle $cycle
   * @return Application
   */
  public function findOneByProgramAndCycle(Program $program, Cycle $cycle)
  {
    $query = $this->_em->createQuery('SELECT a FROM Jazzee\Entity\Application a WHERE a.program = :programId AND  a.cycle = :cycleId');
    $query->setParameter('programId', $program->getId());
    $query->setParameter('cycleId', $cycle->getId());
    $result = $query->getResult();
    if (count($result)) {
      return $result[0];
    }

    return false;
  }

  /**
   * Search for all the Applications belonging to a program
   * @param Program $program
   * @param boolean $onlyPublished only find publisehd applications
   * @param boolean $onlyVisible only find visible applications
   * @param array   $excludedIds any application ids to exclude
   * @return Doctrine\Common\Collections\Collection $applications
   */
  public function findByProgram(Program $program, $onlyPublished = false, $onlyVisible = false, array $excludedIds = array())
  {
    $queryBuilder = $this->_em->createQueryBuilder();
    $queryBuilder->add('select', 'a')
            ->from('Jazzee\Entity\Application', 'a');

    $queryBuilder->where('a.program = :programId');
    $queryBuilder->setParameter('programId', $program->getId());

    if (($onlyPublished)) {
      $queryBuilder->andWhere('a.published = true');
    }
    if (($onlyVisible)) {
      $queryBuilder->andWhere('a.visible = true');
    }
    if(!empty($excludedIds)){
      //force integers from the values
      $safeids = array_map('intval',$excludedIds);
      $ids = implode(',', $safeids);
      $queryBuilder->andWhere("a.id NOT IN ({$ids})");
    }

    return $queryBuilder->getQuery()->getResult();
  }

  /**
   * Get the answer counts for each page.
   * @param Application $application
   * @return array
   */
  public function getPageAnswerCounts(Application $application)
  {
    $query = $this->_em->createQuery('SELECT page.id as pageId, count(answer.id) as answers FROM Jazzee\Entity\Answer answer JOIN answer.page page JOIN answer.applicant applicant WHERE answer.applicant IN (SELECT app1.id FROM Jazzee\Entity\Applicant app1 WHERE app1.application = :applicationId) GROUP BY answer.applicant, answer.page');
    $query->setParameter('applicationId', $application->getId());
    $pageAnswers = array();
    foreach ($query->getResult() as $arr) {
      if (!array_key_exists($arr['pageId'], $pageAnswers) or $pageAnswers[$arr['pageId']] < $arr['answers']) {
        $pageAnswers[$arr['pageId']] = $arr['answers'];
      }
    }
    $pages = array();
    foreach($application->getApplicationPages() as $applicationPage){
      if(array_key_exists($applicationPage->getPage()->getId(), $pageAnswers)){
        $pages[$applicationPage->getPage()->getId()] = $pageAnswers[$applicationPage->getPage()->getId()];
      } else {
        $pages[$applicationPage->getPage()->getId()] = 0;
      }
    }

    return $pages;
  }

  /**
   * Find an application be the program short name and cycle name
   *
   * @param string $programShortName
   * @param string $cycleNamme
   * @return Application
   */
  public function findEasy($programShortName, $cycleName)
  {
    $query = $this->_em->createQuery('SELECT a FROM Jazzee\Entity\Application a WHERE a.program = (SELECT p FROM Jazzee\Entity\Program p WHERE p.shortName = :programShortName) AND  a.cycle = (SELECT c FROM \Jazzee\Entity\Cycle c WHERE c.name= :cycleName)');
    $query->setParameter('programShortName', $programShortName);
    $query->setParameter('cycleName', $cycleName);
    $result = $query->getResult();
    if (count($result)) {
      return $result[0];
    }

    return false;
  }

  /**
   * Find an application by applicant id
   *
   * @param integer applicantId
   * @return Application
   */
  public function findForApplicant($applicantId)
  {
    $query = $this->_em->createQuery('SELECT a FROM Jazzee\Entity\Application a JOIN a.applicants applicant WHERE applicant.id = :applicantId');
    $query->setParameter('applicantId', $applicantId);
    return $query->getSingleResult();
  }
  
  /**
   * Find application as an array
   * @param interger $id
   * 
   * @return array
   */
  public function findArray($id)
  {
    $cache = \Jazzee\Controller::getCache();
    $cacheId = Application::ARRAY_CACHE_PREFIX . $id;
    if($cache->contains($cacheId)){
      return $cache->fetch($cacheId);
    }
    $queryBuilder = $this->makeQuery();
    $queryBuilder->andWhere('application.id = :id');
    $queryBuilder->setParameter('id', $id);
    
    $query = $queryBuilder->getQuery();
    if($result = $query->getArrayResult()){
      $application = $result[0];
      $keys = array('title', 'min', 'max', 'isRequired', 'answerStatusDisplay', 'instructions', 'leadingText', 'trailingText');
      foreach($application['applicationPages'] as &$appPage){
        foreach($keys as $key){
          if(is_null($appPage[$key])){
            $appPage[$key] = $appPage['page'][$key];
          }
        }
      }
      $query = $this->_em->createQuery('SELECT tag from \Jazzee\Entity\Tag as tag LEFT JOIN tag.applicants applicant WHERE applicant.id IN (SELECT a.id from Jazzee\Entity\Applicant a WHERE a.application = :applicationId)');
      $query->setParameter('applicationId', $application['id']);
      $application['tags'] = $query->getArrayResult();
      $cache->save($cacheId, $application);

      return $application;
    } 

    return false;
  }
  
  /**
   * Create a QueryBuilder to use elsewhere
   * @return \Doctrine\ORM\QueryBuilder
   */
  protected function makeQuery(){
    $queryBuilder = $this->_em->createQueryBuilder();
    $queryBuilder->from('Jazzee\Entity\Application', 'application');
    $queryBuilder->add('select', 'application, applicationPages, pages, elements, elementListItems, pageType, elementType, children, childElements, childPageType, childElementListItems, childElementType, children2, childElements2, childPageType2, childElementListItems2, childElementType2, pdfTemplates');
    $queryBuilder->leftJoin('application.pdfTemplates', 'pdfTemplates');
    $queryBuilder->leftJoin('application.applicationPages', 'applicationPages');
    $queryBuilder->leftJoin('applicationPages.page', 'pages');
    $queryBuilder->leftJoin('pages.elements', 'elements');
    $queryBuilder->leftJoin('pages.type', 'pageType');
    $queryBuilder->leftJoin('elements.listItems', 'elementListItems');
    $queryBuilder->leftJoin('elements.type', 'elementType');
    $queryBuilder->leftJoin('pages.children', 'children');
    $queryBuilder->leftJoin('children.elements', 'childElements');
    $queryBuilder->leftJoin('children.type', 'childPageType');
    $queryBuilder->leftJoin('childElements.listItems', 'childElementListItems');
    $queryBuilder->leftJoin('childElements.type', 'childElementType');
    $queryBuilder->leftJoin('children.children', 'children2');
    $queryBuilder->leftJoin('children2.elements', 'childElements2');
    $queryBuilder->leftJoin('children2.type', 'childPageType2');
    $queryBuilder->leftJoin('childElements2.listItems', 'childElementListItems2');
    $queryBuilder->leftJoin('childElements2.type', 'childElementType2');
    
    return $queryBuilder;
  }

}