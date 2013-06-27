<?php
namespace Jazzee\Entity;

/**
 * Custom hydrator for applicant records that creates formatted arrays for PDF Templates
 */
class ApplicantPDFTemplateHydrator extends ApplicantArrayHydrator
{
  private static $_applications = array();
  /**
   * Hydrate applicant records
   * 
   * @param type $stmt
   * @param type $resultSetMapping
   * @param array $hints
   * @return array
   */
  public function hydrateAll($stmt, $resultSetMapping, array $hints = array()) 
  {
    $result = parent::hydrateAll($stmt, $resultSetMapping, $hints);
    foreach($result as $key => $applicant){
      $applicationId = $applicant['application_id'];
      if(!array_key_exists($applicationId, self::$_applications)){
        self::$_applications[$applicationId] = $this->_em->getRepository('Jazzee\Entity\Application')->find($applicationId);
      }
      $result[$key] = self::$_applications[$applicationId]->formatApplicantPDFTemplateArray($applicant);
    }
    
    return $result;
  }
}