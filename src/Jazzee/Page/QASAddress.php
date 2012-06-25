<?php
namespace Jazzee\Page;
require_once __DIR__ . '/../../../lib/qas/qaddress.inc';
/**
 * QAS Address Verification
 * 
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage pages
 */
class QASAddress extends Standard 
{
  
  /**
   * Element Fixed IDs 
   */
  const FID_ADDRESS1 = 2;
  const FID_ADDRESS2 = 4;
  const FID_ADDRESS3 = 6;
  const FID_CITY = 8;
  const FID_STATE = 10;
  const FID_COUNTRY = 12;
  const FID_POSTALCODE = 14;
  
  /**
   * 
   * Enter description here ...
   */
  protected function makeForm(){
    $this->_controller->setVar('confirm', false);
    $form = new \Foundation\Form;
    $form->setAction($this->_controller->getActionPath());
    $form->setCSRFToken($this->_controller->getCSRFToken());
    $field = $form->newField();
    $field->setLegend($this->_applicationPage->getTitle());
    $field->setInstructions($this->_applicationPage->getInstructions());
    
    $element = $field->newElement('TextInput','address3');
    $element->setLabel('company name, department, c/o, etc');
    
    $element = $field->newElement('TextInput','address1');
    $element->setLabel('Address 1');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $element = $field->newElement('TextInput','address2');
    $element->setLabel('Address 2');
    
    $element = $field->newElement('TextInput','city');
    $element->setLabel('City');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $element = $field->newElement('TextInput','state');
    $element->setLabel('State');
    
    $element = $field->newElement('TextInput','postalCode');
    $element->setLabel('ZIP or Postal Code');
    $element->setFormat('');
    $element->addValidator(new \Foundation\Form\Validator\MaximumLength($element, 60));
    
    $element = $field->newElement('SelectList','country');
    $element->setLabel('Country');
    $countries = array('USA' => "United States", 'AFG' => "Afghanistan", 'ALA' => "Aland Islands", 'ALB' => "Albania", 'DZA' => "Algeria", 'ASM' => "American Samoa", 'AND' => "Andorra", 'AGO' => "Angola", 'AIA' => "Anguilla", 'ATA' => "Antarctica", 'ATG' => "Antigua And Barbuda", 'ARG' => "Argentina", 'ARM' => "Armenia", 'ABW' => "Aruba", 'AUS' => "Australia", 'AUT' => "Austria", 'AZE' => "Azerbaijan", 'BHS' => "Bahamas", 'BHR' => "Bahrain", 'BGD' => "Bangladesh", 'BRB' => "Barbados", 'BLR' => "Belarus", 'BEL' => "Belgium", 'BLZ' => "Belize", 'BEN' => "Benin", 'BMU' => "Bermuda", 'BTN' => "Bhutan", 'BOL' => "Bolivia", 'BIH' => "Bosnia And Herzegowina", 'BWA' => "Botswana", 'BVT' => "Bouvet Island", 'BRA' => "Brazil", 'IOT' => "British Indian Ocean Territory", 'BRN' => "Brunei Darussalam", 'BGR' => "Bulgaria", 'BFA' => "Burkina Faso", 'BDI' => "Burundi", 'KHM' => "Cambodia", 'CMR' => "Cameroon", 'CAN' => "Canada", 'CPV' => "Cape Verde", 'CYM' => "Cayman Islands", 'CAF' => "Central African Republic", 'TCD' => "Chad", 'CHL' => "Chile", 'CHN' => "China", 'CXR' => "Christmas Island", 'CCK' => "Cocos (Keeling) Islands", 'COL' => "Colombia", 'COM' => "Comoros", 'COG' => "Congo", 'COD' => "Congo, The Democratic Republic Of The", 'COK' => "Cook Islands", 'CRI' => "Costa Rica", 'CIV' => "Cote D'Ivoire", 'HRV' => "Croatia (Local Name: Hrvatska)", 'CUB' => "Cuba", 'CYP' => "Cyprus", 'CZE' => "Czech Republic", 'DNK' => "Denmark", 'DJI' => "Djibouti", 'DMA' => "Dominica", 'DOM' => "Dominican Republic", 'ECU' => "Ecuador", 'EGY' => "Egypt", 'SLV' => "El Salvador", 'GNQ' => "Equatorial Guinea", 'ERI' => "Eritrea", 'EST' => "Estonia", 'ETH' => "Ethiopia", 'FLK' => "Falkland Islands (Malvinas)", 'FRO' => "Faroe Islands", 'FJI' => "Fiji", 'FIN' => "Finland", 'FRP' => "France", 'GUF' => "French Guiana", 'PYF' => "French Polynesia", 'ATF' => "French Southern Territories", 'GAB' => "Gabon", 'GMB' => "Gambia", 'GEO' => "Georgia", 'DEU' => "Germany", 'GHA' => "Ghana", 'GIB' => "Gibraltar", 'GRC' => "Greece", 'GRL' => "Greenland", 'GRD' => "Grenada", 'GLP' => "Guadeloupe", 'GUM' => "Guam", 'GTM' => "Guatemala", 'GIN' => "Guinea", 'GNB' => "Guinea-Bissau", 'GUY' => "Guyana", 'HTI' => "Haiti", 'HMD' => "Heard And McDonald Islands", 'VAT' => "Holy See (Vatican City State)", 'HND' => "Honduras", 'HKG' => "Hong Kong", 'HUN' => "Hungary", 'ISL' => "Iceland", 'IND' => "India", 'IDN' => "Indonesia", 'IRN' => "Iran (Islamic Republic Of)", 'IRQ' => "Iraq", 'IRL' => "Ireland", 'ISR' => "Israel", 'ITA' => "Italy", 'JAM' => "Jamaica", 'JPN' => "Japan", 'JOR' => "Jordan", 'KAZ' => "Kazakhstan", 'KEN' => "Kenya", 'KIR' => "Kiribati", 'PRK' => "Korea, Democratic People's Republic Of", 'KOR' => "Korea, Republic Of", 'KWT' => "Kuwait", 'KGZ' => "Kyrgyzstan", 'LAO' => "Lao People's Democratic Republic", 'LVA' => "Latvia", 'LBN' => "Lebanon", 'LSO' => "Lesotho", 'LBR' => "Liberia", 'LBY' => "Libyan Arab Jamahiriya", 'LIE' => "Liechtenstein", 'LTU' => "Lithuania", 'LUX' => "Luxembourg", 'MAC' => "Macau", 'MKD' => "Macedonia, The Former Yugoslav Republic Of", 'MDG' => "Madagascar", 'MWI' => "Malawi", 'MYS' => "Malaysia", 'MDV' => "Maldives", 'MLI' => "Mali", 'MLT' => "Malta", 'MHL' => "Marshall Islands", 'MTQ' => "Martinique", 'MRT' => "Mauritania", 'MUS' => "Mauritius", 'MYT' => "Mayotte", 'MEX' => "Mexico", 'FSM' => "Micronesia, Federated States Of", 'MDA' => "Moldova, Republic Of", 'MCO' => "Monaco", 'MNE' => "Montenegro", 'MNG' => "Mongolia", 'MSR' => "Montserrat", 'MAR' => "Morocco", 'MOZ' => "Mozambique", 'MMR' => "Myanmar", 'NAM' => "Namibia", 'NRU' => "Nauru", 'NPL' => "Nepal", 'NLD' => "Netherlands, The", 'ANT' => "Netherlands Antilles", 'NCL' => "New Caledonia", 'NZL' => "New Zealand", 'NIC' => "Nicaragua", 'NER' => "Niger", 'NGA' => "Nigeria", 'NIU' => "Niue", 'NFK' => "Norfolk Island", 'MNP' => "Northern Mariana Islands", 'NOR' => "Norway", 'OMN' => "Oman", 'PAK' => "Pakistan", 'PLW' => "Palau", 'PSE' => "Palestinian Territory", 'PAN' => "Panama", 'PNG' => "Papua New Guinea", 'PRY' => "Paraguay", 'PER' => "Peru", 'PHL' => "Philippines, The", 'PCN' => "Pitcairn", 'POL' => "Poland", 'PRT' => "Portugal", 'PRI' => "Puerto Rico", 'QAT' => "Qatar", 'REU' => "Reunion", 'ROM' => "Romania", 'RUS' => "Russian Federation", 'RWA' => "Rwanda", 'KNA' => "Saint Kitts And Nevis", 'LCA' => "Saint Lucia", 'VCT' => "Saint Vincent And The Grenadines", 'WSM' => "Samoa", 'SMR' => "San Marino", 'STP' => "Sao Tome And Principe", 'SAU' => "Saudi Arabia", 'SRB' => "Serbia", 'SEN' => "Senegal", 'SYC' => "Seychelles", 'SLE' => "Sierra Leone", 'SGF' => "Singapore", 'SVK' => "Slovakia (Slovak Republic)", 'SVN' => "Slovenia", 'SLB' => "Solomon Islands", 'SOM' => "Somalia", 'ZAF' => "South Africa", 'SGS' => "South Georgia And The South Sandwich Islands", 'ESP' => "Spain", 'LKA' => "Sri Lanka", 'SHN' => "St. Helena", 'SPM' => "St. Pierre And Miquelon", 'SDN' => "Sudan", 'SRB' => "Serbia", 'SUR' => "Suriname", 'SJM' => "Svalbard And Jan Mayen Islands", 'SWZ' => "Swaziland", 'SWE' => "Sweden", 'CHE' => "Switzerland", 'SYR' => "Syrian Arab Republic", 'TWN' => "Taiwan", 'TJK' => "Tajikistan", 'TZA' => "Tanzania, United Republic Of", 'THA' => "Thailand", 'TLS' => "Timor-Leste", 'TGO' => "Togo", 'TKL' => "Tokelau", 'TON' => "Tonga", 'TTO' => "Trinidad And Tobago", 'TUN' => "Tunisia", 'TUR' => "Turkey", 'TKM' => "Turkmenistan", 'TCA' => "Turks And Caicos Islands", 'TUV' => "Tuvalu", 'UGA' => "Uganda", 'UKR' => "Ukraine", 'ARE' => "United Arab Emirates", 'GBR' => "United Kingdom", 'UMI' => "United States Minor Outlying Islands", 'URY' => "Uruguay", 'UZB' => "Uzbekistan", 'VUT' => "Vanuatu", 'VEN' => "Venezuela", 'VNM' => "Vietnam", 'VGB' => "Virgin Islands (British)", 'VIR' => "Virgin Islands (U.S.)", 'WLF' => "Wallis And Futuna Islands", 'ESH' => "Western Sahara", 'YEM' => "Yemen", 'ZMB' => "Zambia", 'ZWE' => "Zimbabwe");
    foreach($countries as $value => $label){
      $element->newItem($value, $label);
    }
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    
    $form->newButton('submit', 'Save');
    return $form;
  }
  
  public function validateInput($arr){
    if($input = $this->getForm()->processInput($arr)){
      return $this->validateAddress($input);
    }
    return false;
  }
  
  public function fill($answerId){
    if($answer = $this->_applicant->findAnswerById($answerId)){
      $fixedElements = array(
        self::FID_ADDRESS1 => 'address1',
        self::FID_ADDRESS2 => 'address2',
        self::FID_ADDRESS3 => 'address3',
        self::FID_CITY => 'city',
        self::FID_STATE => 'state',
        self::FID_POSTALCODE => 'postalCode',
        self::FID_COUNTRY => 'country'
      );
      foreach($fixedElements as $fid => $name){
        $element = $this->_applicationPage->getPage()->getElementByFixedId($fid);
        $element->getJazzeeElement()->setController($this->_controller);
        $value = $element->getJazzeeElement()->formValue($answer);
        if($value) $this->getForm()->getElementByName($name)->setValue($value);
      }
      $this->getForm()->setAction($this->_controller->getActionPath() . "/edit/{$answerId}");
    }
  }
  
  /**
   * Create the recommenders form
   */
  public function setupNewPage(){
    $entityManager = $this->_controller->getEntityManager();
    $types = $entityManager->getRepository('Jazzee\Entity\ElementType')->findAll();
    $elementTypes = array();
    foreach($types as $type){
      $elementTypes[$type->getClass()] = $type;
    };
    
    $element = new \Jazzee\Entity\Element;
    $element->setType($elementTypes['\Jazzee\Element\TextInput']);
    $element->setTitle('company name, department, c/o, etc');
    $element->setWeight(1);
    $element->setFixedId(self::FID_ADDRESS3);
    $this->_applicationPage->getPage()->addElement($element);
    $entityManager->persist($element);
    
    $element = new \Jazzee\Entity\Element;
    $element->setType($elementTypes['\Jazzee\Element\TextInput']);
    $element->setTitle('Address 1');
    $element->required();
    $element->setWeight(2);
    $element->setFixedId(self::FID_ADDRESS1);
    $this->_applicationPage->getPage()->addElement($element);
    $entityManager->persist($element);
    
    $element = new \Jazzee\Entity\Element;
    $element->setType($elementTypes['\Jazzee\Element\TextInput']);
    $element->setTitle('Address 2');
    $element->setWeight(3);
    $element->setFixedId(self::FID_ADDRESS2);
    $this->_applicationPage->getPage()->addElement($element);
    $entityManager->persist($element);
    
    $element = new \Jazzee\Entity\Element;
    $element->setType($elementTypes['\Jazzee\Element\TextInput']);
    $element->setTitle('City');
    $element->setWeight(4);
    $element->setFixedId(self::FID_CITY);
    $element->required();
    $this->_applicationPage->getPage()->addElement($element);
    $entityManager->persist($element);
    
    $element = new \Jazzee\Entity\Element;
    $element->setType($elementTypes['\Jazzee\Element\TextInput']);
    $element->setTitle('State');
    $element->setWeight(5);
    $element->setFixedId(self::FID_STATE);
    $this->_applicationPage->getPage()->addElement($element);
    $entityManager->persist($element);
    
    $element = new \Jazzee\Entity\Element;
    $element->setType($elementTypes['\Jazzee\Element\TextInput']);
    $element->setTitle('Country');
    $element->setWeight(5);
    $element->setFixedId(self::FID_COUNTRY);
    $element->required();
    $this->_applicationPage->getPage()->addElement($element);
    $entityManager->persist($element);
    
    $element = new \Jazzee\Entity\Element;
    $element->setType($elementTypes['\Jazzee\Element\TextInput']);
    $element->setTitle('Postal Code');
    $element->setWeight(6);
    $element->setFixedId(self::FID_POSTALCODE);
    $element->required();
    $this->_applicationPage->getPage()->addElement($element);
    $entityManager->persist($element);
    
    $defaultVars = array(
      'wsdlAddress' => null,
      'validatedCountries' => ''
    );
    foreach($defaultVars as $name=>$value){
      $var = $this->_applicationPage->getPage()->setVar($name, $value);
      $entityManager->persist($var);
    }    
  }
  
  /**
   * Validate an address with QAS
   * @param \Foundation\Form\Input $input 
   * @return array addresses
   */
  protected function validateAddress(\Foundation\Form\Input $input){
    
    //Check to see if this is the second time the user has inptu this address, 
    //if it is then just use that as the address unverified
    $sameUserInput = false;
    if($str = $input->get('originalInput')){
      $str = base64_decode($str);
      $originalInput = unserialize($str);
      $sameUserInput = true;
      foreach(array('address1','address2','address3','city','state','postalCode','country') as $name){
        if($originalInput->get($name) != $input->get($name)){
          $sameUserInput = false;
          break;
        }
      }
    }
    $country = $input->get('country');
    $countriesToValidate = explode(',', $this->_applicationPage->getPage()->getVar('validatedCountries'));
    if($sameUserInput or !in_array($country, $countriesToValidate)){
      $input->set('el'.$this->_applicationPage->getPage()->getElementByFixedId(self::FID_ADDRESS1)->getId(), $input->get('address1'));
      $input->set('el'.$this->_applicationPage->getPage()->getElementByFixedId(self::FID_ADDRESS2)->getId(), $input->get('address2'));
      $input->set('el'.$this->_applicationPage->getPage()->getElementByFixedId(self::FID_ADDRESS3)->getId(), $input->get('address3'));
      $input->set('el'.$this->_applicationPage->getPage()->getElementByFixedId(self::FID_CITY)->getId(), $input->get('city'));
      $input->set('el'.$this->_applicationPage->getPage()->getElementByFixedId(self::FID_STATE)->getId(), $input->get('state'));
      $input->set('el'.$this->_applicationPage->getPage()->getElementByFixedId(self::FID_POSTALCODE)->getId(), $input->get('postalCode'));
      $countryName = $this->getForm()->getElementByName('country')->getLabelForValue($input->get('country'));
      $input->set('el'.$this->_applicationPage->getPage()->getElementByFixedId(self::FID_COUNTRY)->getId(), $countryName);
      return $input;
    }
    
    $search = array();
        
    $search[0] = $input->get('address1');
    $search[1] = $input->get('address2');
    $search[2] = $input->get('address3');
    $search[3] = $input->get('city');
    $search[4] = $input->get('state');
    $search[5] = $input->get('country');
  
    # Create the QuickAddress Object and set the engine and picklist type
    $qas = new \QuickAddress($this->_applicationPage->getPage()->getVar('wsdlAddress'));
    $qas->setEngineType(QAS_VERIFICATION_ENGINE);
    $qas->setFlatten(true);
    
    
    #Perform the search itself
    $result = $qas->search($country, $search, QAS_DEFAULT_PROMPT, "Database layout");
    switch($result->sVerifyLevel){
      case 'Verified':
        $arr = $result->address->atAddressLines;
        $input->set('el'.$this->_applicationPage->getPage()->getElementByFixedId(self::FID_ADDRESS1)->getId(), $arr[0]->Line);
        $input->set('el'.$this->_applicationPage->getPage()->getElementByFixedId(self::FID_ADDRESS2)->getId(), $arr[1]->Line);
        $input->set('el'.$this->_applicationPage->getPage()->getElementByFixedId(self::FID_ADDRESS3)->getId(), $input->get('address3'));
        $input->set('el'.$this->_applicationPage->getPage()->getElementByFixedId(self::FID_CITY)->getId(), $arr[3]->Line);
        $input->set('el'.$this->_applicationPage->getPage()->getElementByFixedId(self::FID_STATE)->getId(), $arr[4]->Line);
        $input->set('el'.$this->_applicationPage->getPage()->getElementByFixedId(self::FID_POSTALCODE)->getId(), $arr[5]->Line);
        $input->set('el'.$this->_applicationPage->getPage()->getElementByFixedId(self::FID_COUNTRY)->getId(), $arr[6]->Line);
        return $input;
        break;
      case 'Multiple':
        $this->_controller->addMessage('error', 'We were unable to validate your address.');
        $this->_controller->setVar('confirm', true);
        $this->_form->getElementByName('submit')->setValue('Confirm Address as Entered');
        $this->_form->newHiddenElement('originalInput', base64_encode(serialize($input)));
        $this->_controller->setVar('picklist', $result->picklist);
        break;
      case 'StreetPartial':
      case 'PremisesPartial':
        $this->_controller->addMessage('error', 'We were unable to validate your address.');
        $this->getForm()->getElementByName('address1')->addMessage('Your address is incomplete');
        $this->getForm()->getElementByName('address2')->addMessage('Your address is incomplete');
        $this->_form->getElementByName('submit')->setValue('Confirm Address as Entered');
        $this->_form->newHiddenElement('originalInput', base64_encode(serialize($input)));
        break;
      case 'None':
        $this->_controller->addMessage('error', 'We were unable to validate your address.');
        $this->_form->getElementByName('submit')->setValue('Confirm Address as Entered');
        $this->_form->newHiddenElement('originalInput', base64_encode(serialize($input)));
        break;
      default:
        throw new \Jazzee\Exception("{$result->sVerifyLevel} is not a known QAS address verification type.", E_USER_ERROR, 'There was a problem verifying your address.  Please try entering it again.');
    }
    return false;
  }
  
  /**
   * Format an address
   * @todo: This maybe can be done with QAS, for now its just manual
   * @param \Jazzee\Entity\Answer
   * @return array address lines
   */
  public function formatAddress(\Jazzee\Entity\Answer $answer){
    $lines = array();
    
    
    $lines[] = $this->_applicationPage->getPage()->getElementByFixedId(self::FID_ADDRESS3)->getJazzeeElement()->displayValue($answer);
    $lines[] = $this->_applicationPage->getPage()->getElementByFixedId(self::FID_ADDRESS1)->getJazzeeElement()->displayValue($answer);
    $lines[] = $this->_applicationPage->getPage()->getElementByFixedId(self::FID_ADDRESS2)->getJazzeeElement()->displayValue($answer);
    $lines[] = $this->_applicationPage->getPage()->getElementByFixedId(self::FID_CITY)->getJazzeeElement()->displayValue($answer) . ', '
               . $this->_applicationPage->getPage()->getElementByFixedId(self::FID_STATE)->getJazzeeElement()->displayValue($answer) . ' '
               . $this->_applicationPage->getPage()->getElementByFixedId(self::FID_POSTALCODE)->getJazzeeElement()->displayValue($answer);
    $lines[] = $this->_applicationPage->getPage()->getElementByFixedId(self::FID_COUNTRY)->getJazzeeElement()->displayValue($answer);
    
    return $lines;
  }
  
  /**
   * Pick an address from a list
   * @param integer $answerId If set we are updating an existing answer
   * @param array $postData
   */
  public function do_pickAddress($answerId, $postData){
    if(empty($postData['addressMoniker'])){
      throw new \Jazzee\Exception('Tried to do QASAddress::do_pickAddress with no addressMoniker');
    }
    $qas = new \QuickAddress($this->_applicationPage->getPage()->getVar('wsdlAddress'));
    $address = $qas->getFormattedAddress("Database layout", $postData['addressMoniker']);
    $arr = $address->atAddressLines;
    $input = new \Foundation\Form\Input($postData);
    $input->set('el'.$this->_applicationPage->getPage()->getElementByFixedId(self::FID_ADDRESS1)->getId(), $arr[0]->Line);
    $input->set('el'.$this->_applicationPage->getPage()->getElementByFixedId(self::FID_ADDRESS2)->getId(), $arr[1]->Line);
    $input->set('el'.$this->_applicationPage->getPage()->getElementByFixedId(self::FID_ADDRESS3)->getId(), $input->get('address3'));
    $input->set('el'.$this->_applicationPage->getPage()->getElementByFixedId(self::FID_CITY)->getId(), $arr[3]->Line);
    $input->set('el'.$this->_applicationPage->getPage()->getElementByFixedId(self::FID_STATE)->getId(), $arr[4]->Line);
    $input->set('el'.$this->_applicationPage->getPage()->getElementByFixedId(self::FID_POSTALCODE)->getId(), $arr[5]->Line);
    $input->set('el'.$this->_applicationPage->getPage()->getElementByFixedId(self::FID_COUNTRY)->getId(), $arr[6]->Line);
    if(!empty($answerId)){
      $this->updateAnswer($input, $answerId);
      $this->_controller->setVar('currentAnswerID', null);
    } else {
      $this->newAnswer($input);
    }
  }
  
  public static function applyPageElement(){
    return 'QASAddress-apply_page';
  }
  
  public static function pageBuilderScriptPath(){
    return 'resource/scripts/page_types/JazzeePageQASAddress.js';
  }
}