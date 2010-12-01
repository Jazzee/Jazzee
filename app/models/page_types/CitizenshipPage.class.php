<?php
/**
 * Collect citiznship information by branching from the citizenship type selection
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
class CitizenshipPage extends StandardPage {
  /**
   * Create the form from the $page
   * @return Form
   */
  protected function makeForm(){
    $form = new Form;
    $form->newHiddenElement('level', 1);
    $field = $form->newField();
    $field->legend = $this->applicationPage->title;
    $field->instructions = $this->applicationPage->instructions;
    
    $element = $field->newElement('SelectList', 'citizenship');
    $element->label = 'Citizenship';
    $element->addItem('us', 'US Citizen');
    $element->addItem('resident', 'US Permanent Resident');
    $element->addItem('international', 'International');
    $element->addValidator('NotEmpty');
    
    $form->newButton('submit', 'Next');
    return $form;
  }
  
  /**
   * Create the branching form based on the user input from the first form
   * @param string $type the citizenship type
   * @return Form
   */
  protected function branchForm($type){
    $this->applicationPage->leadingText .= "<a href='{$this->applicationPage->id}'>Change citizenship type</a>";
    $this->form->reset();
    $this->form->newHiddenElement('citizenship', $type);
    $this->form->newHiddenElement('level', 2);
    $field = $this->form->newField();
    $field->legend = $this->applicationPage->title;
    $field->instructions = $this->applicationPage->instructions;
    $element = $field->newElement('TextInput', 'birthCity');
    $element->label = 'Birth City';
    $element->addValidator('NotEmpty');
    
    $element = $field->newElement('SelectList', 'birthState');
    $element->label = 'Birth State/Province';
    $element->format = 'US Only';
    $element->addItem(null,'');
    foreach($GLOBALS['location_us_states'] as $abbr => $state){
      $element->addItem($abbr,$state);
    }
    
    $element = $field->newElement('SelectList', 'birthCountry');
    $element->label = 'Birth Country';
    $element->addItem(null,'');
    foreach($GLOBALS['location_countries'] as $abbr => $country){
      $element->addItem($abbr,$country);
    }
    $element->addValidator('NotEmpty');
    switch($type){
      case 'us':
        $element = $field->newElement('RadioList', 'stateResident');
        $element->label = 'Are you a Resident?';
        $element->addItem(0, 'No');
        $element->addItem(1, 'Yes');
        $element->addValidator('NotEmpty');

        break;
      case 'resident':
        $element = $field->newElement('SelectList', 'citizenshipCountry');
        $element->label = 'Country of Citizenship';
        $element->addItem(null,'');
        foreach($GLOBALS['location_countries'] as $abbr => $country){
          $element->addItem($abbr,$country);
        }
        $element->addValidator('NotEmpty');
        
        $element = $field->newElement('TextInput', 'permanentResidentDate');
        $element->label = 'Permanent Resident Date';
        $element->format = 'mm/dd/yyyy';
        $element->addValidator('Date');
        $element->addFilter('DateFormat','Y-m-d');
        $element->addValidator('NotEmpty');
        break;
      case 'international':
        $element = $field->newElement('SelectList', 'citizenshipCountry');
        $element->label = 'Country of Citizenship';
        $element->addItem(null,'');
        foreach($GLOBALS['location_countries'] as $abbr => $country){
          $element->addItem($abbr,$country);
        }
        $element->addValidator('NotEmpty');
        
        $element = $field->newElement('TextInput', 'visaType');
        $element->label = 'What type of Visa do you intend to apply for?';
        $element->addValidator('NotEmpty');
        break;
    }
    
    $this->form->newButton('submit', 'Save');
    $this->form->newButton('reset', 'Clear Form');
  }
  
  public function validateInput($input){
    $this->branchForm($input['citizenship']);
    //kick back the page so it will load the second form
    if(isset($input['level']) and $input['level'] == 1) return false;
    
    if($input = $this->form->processInput($input)) return $input;
    return false;
  }
  
  public function newAnswer($input){
    $a = new Answer;
    $a->pageID = $this->applicationPage->Page->id;
    $this->applicant['Answers'][] = $a;
    $answer = new CitizenshipAnswer($a);
    $answer->update($input);
    $this->applicant->save();
    $this->form = $this->makeForm();
    $this->form->applyDefaultValues();
    return true;
  }
  
  public function updateAnswer($input, $answerID){
    if($a = $this->applicant->getAnswerByID($answerID)){
      $answer = new CitizenshipAnswer($a);
      $answer->update($input);
      $a->save();
      $this->form = $this->makeForm();
      $this->form->applyDefaultValues();
    }
  }

  public function deleteAnswer($answerID){
    if(($key = array_search($answerID, $this->applicant->Answers->getPrimaryKeys())) !== false){
      $this->applicant->Answers->remove($key);
      $this->applicant->save();
      return true;
    }
    return false;
  }
  
  public function fill($answerID){
    if($a = $this->applicant->getAnswerByID($answerID)){
      $this->branchForm($a->Citizenship->citizenship);
      $answer = new CitizenshipAnswer($a);
      foreach($answer->getElements() as $id => $element){
        $value = $answer->getFormValueForElement($id);
        if($value) $this->form->elements[$id]->value = $value;
      }
    }
  }
  
  public function getAnswers(){
    $answers = array();
    foreach($this->applicant->getAnswersForPage($this->applicationPage->Page->id) as $a){
      $answers[] = new CitizenshipAnswer($a);
    }
    return $answers;
  }
  
}

/**
 * A single StandardPage Citizenship Answer
 */
class CitizenshipAnswer extends StandardAnswer {
 /**
  * The Answer model
  * @var Answer $answer
  */
  protected $answer;
  
 /**
  * Contructor
  * Store the answer
  * @param Answer $answer
  */
  public function __construct(Answer $answer){
    $this->answer = $answer;
  }

  public function update(FormInput $input){
    $this->answer->Citizenship->citizenship = $input->citizenship;
    $this->answer->Citizenship->birthCity = $input->birthCity;
    $this->answer->Citizenship->birthState = $input->birthState;
    $this->answer->Citizenship->birthCountry = $input->birthCountry;
    $this->answer->Citizenship->stateResident = null;
    if(isset($input->stateResident)) $this->answer->Citizenship->stateResident = $input->stateResident;
    $this->answer->Citizenship->citizenshipCountry = null;
    if(isset($input->citizenshipCountry)) $this->answer->Citizenship->citizenshipCountry = $input->citizenshipCountry;
    $this->answer->Citizenship->permanentResidentDate = null;
    if(isset($input->permanentResidentDate)) $this->answer->Citizenship->permanentResidentDate = $input->permanentResidentDate;
    $this->answer->Citizenship->visaType = null;
    if(isset($input->visaType)) $this->answer->Citizenship->visaType = $input->visaType;
  }
  
  public function getElements(){
    $elements = array();
    $elements['citizenship'] = 'Citizenship';
    $elements['birthCity'] = 'Birth City';
    $elements['birthState'] = 'Birth State';
    $elements['birthCountry'] = 'Birth Country';
    $elements['stateResident'] = 'Resident';
    $elements['citizenshipCountry'] = 'Citizenship Country';
    $elements['permanentResidentDate'] = 'Permanent Resident Date';
    $elements['visaType'] = 'Visa Type';
    return $elements;
  }

  public function getDisplayValueForElement($elementID){
    if(is_null($this->answer->Citizenship->{$elementID})) return false;
    switch($elementID){
      case'citizenship':
        switch($this->answer->Citizenship->citizenship){
          case 'us':
            return 'US Citizen';
            break;
          case 'resident':
            return 'US Permanent Resident';
            break;
          case 'international':
            return 'International';
            break;
        }
        break;
      case 'birthState':
          return $GLOBALS['location_us_states'][$this->answer->Citizenship->birthState];
        break;
      case 'stateResident':
        return $this->answer->Citizenship->stateResident?'Yes':'No';
      case 'birthCountry':
      case 'citizenshipCountry':
        return $GLOBALS['location_countries'][$this->answer->Citizenship->{$elementID}];
        break;
      case 'permanentResidentDate':
        return date('m/d/Y', strtotime($this->answer->Citizenship->permanentResidentDate));
        break;
      case 'visaType':
        return $this->answer->Citizenship->visaType;
        break;
    }
    return false;
  }

  public function getFormValueForElement($elementID){
    if(isset($this->answer->Citizenship->{$elementID})){
      return $this->answer->Citizenship->{$elementID};
    }
    return false;
  }

}
/**
 * Location Globals to make the lists easier to manage
 */
global $location_us_states;
global $location_canadian_provinces;
global $location_countries;
/**
 * All the US States
 * @var array
 */
$location_us_states = array(
  'AL'=>"Alabama",
  'AK'=>"Alaska", 
  'AZ'=>"Arizona", 
  'AR'=>"Arkansas", 
  'CA'=>"California", 
  'CO'=>"Colorado", 
  'CT'=>"Connecticut", 
  'DE'=>"Delaware", 
  'DC'=>"District Of Columbia", 
  'FL'=>"Florida", 
  'GA'=>"Georgia", 
  'HI'=>"Hawaii", 
  'ID'=>"Idaho", 
  'IL'=>"Illinois", 
  'IN'=>"Indiana", 
  'IA'=>"Iowa", 
  'KS'=>"Kansas", 
  'KY'=>"Kentucky", 
  'LA'=>"Louisiana", 
  'ME'=>"Maine", 
  'MD'=>"Maryland", 
  'MA'=>"Massachusetts", 
  'MI'=>"Michigan", 
  'MN'=>"Minnesota", 
  'MS'=>"Mississippi", 
  'MO'=>"Missouri", 
  'MT'=>"Montana",
  'NE'=>"Nebraska",
  'NV'=>"Nevada",
  'NH'=>"New Hampshire",
  'NJ'=>"New Jersey",
  'NM'=>"New Mexico",
  'NY'=>"New York",
  'NC'=>"North Carolina",
  'ND'=>"North Dakota",
  'OH'=>"Ohio", 
  'OK'=>"Oklahoma", 
  'OR'=>"Oregon", 
  'PA'=>"Pennsylvania", 
  'RI'=>"Rhode Island", 
  'SC'=>"South Carolina", 
  'SD'=>"South Dakota",
  'TN'=>"Tennessee", 
  'TX'=>"Texas", 
  'UT'=>"Utah", 
  'VT'=>"Vermont", 
  'VA'=>"Virginia", 
  'WA'=>"Washington", 
  'WV'=>"West Virginia", 
  'WI'=>"Wisconsin", 
  'WY'=>"Wyoming"
);

/**
 * Canadiane Provinces
 * @var array
 */
$location_canadian_provinces = array(
  'AB' => 'Alberta',
  'BC' => 'British Columbia',
  'MB' => 'Manitoba',
  'NB' => 'New Brunswick',
  'NF' => 'Newfoundland',
  'MP' => 'Northern Mariana Island ',
  'NT' => 'Northwest Territories',
  'NS' => 'Nova Scotia',
  'ON' => 'Ontario',
  'PW' => 'Palau Island',
  'PE' => 'Prince Edward Island',
  'QC' => 'Quebec',
  'SK' => 'Saskatchewan',
  'YT' => 'Yukon Territory' 
);

/**
 * World Countries
 * @var array
 */
$location_countries = array(
  'US' => 'United States',
  'AF' => 'Afghanistan',
  'AL' => 'Albania',
  'DZ' => 'Algeria',
  'AS' => 'American Samoa',
  'AD' => 'Andorra',
  'AO' => 'Angola',
  'AI' => 'Anguilla',
  'AQ' => 'Antarctica',
  'AG' => 'Antigua And Barbuda',
  'AR' => 'Argentina',
  'AM' => 'Armenia',
  'AW' => 'Aruba',
  'AU' => 'Australia',
  'AT' => 'Austria',
  'AZ' => 'Azerbaijan',
  'BS' => 'Bahamas',
  'BH' => 'Bahrain',
  'BD' => 'Bangladesh',
  'BB' => 'Barbados',
  'BY' => 'Belarus',
  'BE' => 'Belgium',
  'BZ' => 'Belize',
  'BJ' => 'Benin',
  'BM' => 'Bermuda',
  'BT' => 'Bhutan',
  'BO' => 'Bolivia',
  'BA' => 'Bosnia And Herzegowina',
  'BW' => 'Botswana',
  'BV' => 'Bouvet Island',
  'BR' => 'Brazil',
  'IO' => 'British Indian Ocean Territory',
  'BN' => 'Brunei Darussalam',
  'BG' => 'Bulgaria',
  'BF' => 'Burkina Faso',
  'BI' => 'Burundi',
  'KH' => 'Cambodia',
  'CM' => 'Cameroon',
  'CA' => 'Canada',
  'CV' => 'Cape Verde',
  'KY' => 'Cayman Islands',
  'CF' => 'Central African Republic',
  'TD' => 'Chad',
  'CL' => 'Chile',
  'CN' => 'China',
  'CX' => 'Christmas Island',
  'CC' => 'Cocos (Keeling) Islands',
  'CO' => 'Colombia',
  'KM' => 'Comoros',
  'CG' => 'Congo',
  'CD' => 'Congo, The Democratic Republic Of The',
  'CK' => 'Cook Islands',
  'CR' => 'Costa Rica',
  'CI' => 'Cote D\'Ivoire',
  'HR' => 'Croatia (Local Name: Hrvatska)',
  'CU' => 'Cuba',
  'CY' => 'Cyprus',
  'CZ' => 'Czech Republic',
  'DK' => 'Denmark',
  'DJ' => 'Djibouti',
  'DM' => 'Dominica',
  'DO' => 'Dominican Republic',
  'TP' => 'East Timor',
  'EC' => 'Ecuador',
  'EG' => 'Egypt',
  'SV' => 'El Salvador',
  'GQ' => 'Equatorial Guinea',
  'ER' => 'Eritrea',
  'EE' => 'Estonia',
  'ET' => 'Ethiopia',
  'FK' => 'Falkland Islands (Malvinas)',
  'FO' => 'Faroe Islands',
  'FJ' => 'Fiji',
  'FI' => 'Finland',
  'FR' => 'France',
  'FX' => 'France, Metropolitan',
  'GF' => 'French Guiana',
  'PF' => 'French Polynesia',
  'TF' => 'French Southern Territories',
  'GA' => 'Gabon',
  'GM' => 'Gambia',
  'GE' => 'Georgia',
  'DE' => 'Germany',
  'GH' => 'Ghana',
  'GI' => 'Gibraltar',
  'GR' => 'Greece',
  'GL' => 'Greenland',
  'GD' => 'Grenada',
  'GP' => 'Guadeloupe',
  'GU' => 'Guam',
  'GT' => 'Guatemala',
  'GN' => 'Guinea',
  'GW' => 'Guinea-Bissau',
  'GY' => 'Guyana',
  'HT' => 'Haiti',
  'HM' => 'Heard And Mc Donald Islands',
  'HN' => 'Honduras',
  'HK' => 'Hong Kong',
  'HU' => 'Hungary',
  'IS' => 'Iceland',
  'IN' => 'India',
  'ID' => 'Indonesia',
  'IR' => 'Iran (Islamic Republic Of)',
  'IQ' => 'Iraq',
  'IE' => 'Ireland',
  'IL' => 'Israel',
  'IT' => 'Italy',
  'JM' => 'Jamaica',
  'JP' => 'Japan',
  'JO' => 'Jordan',
  'KZ' => 'Kazakhstan',
  'KE' => 'Kenya',
  'KI' => 'Kiribati',
  'KP' => 'Korea, Democratic People\'S Republic Of',
  'KR' => 'Korea, Republic Of',
  'KW' => 'Kuwait',
  'KG' => 'Kyrgyzstan',
  'LA' => 'Lao People\'S Democratic Republic',
  'LV' => 'Latvia',
  'LB' => 'Lebanon',
  'LS' => 'Lesotho',
  'LR' => 'Liberia',
  'LY' => 'Libyan Arab Jamahiriya',
  'LI' => 'Liechtenstein',
  'LT' => 'Lithuania',
  'LU' => 'Luxembourg',
  'MO' => 'Macau',
  'MK' => 'Macedonia, Former Yugoslav Republic Of',
  'MG' => 'Madagascar',
  'MW' => 'Malawi',
  'MY' => 'Malaysia',
  'MV' => 'Maldives',
  'ML' => 'Mali',
  'MT' => 'Malta',
  'MH' => 'Marshall Islands, Republic of the',
  'MQ' => 'Martinique',
  'MR' => 'Mauritania',
  'MU' => 'Mauritius',
  'YT' => 'Mayotte',
  'MX' => 'Mexico',
  'FM' => 'Micronesia, Federated States Of',
  'MD' => 'Moldova, Republic Of',
  'MC' => 'Monaco',
  'MN' => 'Mongolia',
  'MS' => 'Montserrat',
  'MA' => 'Morocco',
  'MZ' => 'Mozambique',
  'MM' => 'Myanmar',
  'NA' => 'Namibia',
  'NR' => 'Nauru',
  'NP' => 'Nepal',
  'NL' => 'Netherlands',
  'AN' => 'Netherlands Antilles',
  'NC' => 'New Caledonia',
  'NZ' => 'New Zealand',
  'NI' => 'Nicaragua',
  'NE' => 'Niger',
  'NG' => 'Nigeria',
  'NU' => 'Niue',
  'NF' => 'Norfolk Island',
  'MP' => 'Northern Mariana Islands, Commonwealth of the',
  'NO' => 'Norway',
  'OM' => 'Oman',
  'PK' => 'Pakistan',
  'PW' => 'Palau, Republic of',
  'PA' => 'Panama',
  'PG' => 'Papua New Guinea',
  'PY' => 'Paraguay',
  'PE' => 'Peru',
  'PH' => 'Philippines',
  'PN' => 'Pitcairn',
  'PL' => 'Poland',
  'PT' => 'Portugal',
  'PR' => 'Puerto Rico',
  'QA' => 'Qatar',
  'RE' => 'Reunion',
  'RO' => 'Romania',
  'RU' => 'Russian Federation',
  'RW' => 'Rwanda',
  'KN' => 'Saint Kitts And Nevis',
  'LC' => 'Saint Lucia',
  'VC' => 'Saint Vincent And The Grenadines',
  'WS' => 'Samoa',
  'SM' => 'San Marino',
  'ST' => 'Sao Tome And Principe',
  'SA' => 'Saudi Arabia',
  'SN' => 'Senegal',
  'SC' => 'Seychelles',
  'SL' => 'Sierra Leone',
  'SG' => 'Singapore',
  'SK' => 'Slovakia (Slovak Republic)',
  'SI' => 'Slovenia',
  'SB' => 'Solomon Islands',
  'SO' => 'Somalia',
  'ZA' => 'South Africa',
  'GS' => 'South Georgia, South Sandwich Islands',
  'ES' => 'Spain',
  'LK' => 'Sri Lanka',
  'SH' => 'St. Helena',
  'PM' => 'St. Pierre And Miquelon',
  'SD' => 'Sudan',
  'SR' => 'Suriname',
  'SJ' => 'Svalbard And Jan Mayen Islands',
  'SZ' => 'Swaziland',
  'SE' => 'Sweden',
  'CH' => 'Switzerland',
  'SY' => 'Syrian Arab Republic',
  'TW' => 'Taiwan',
  'TJ' => 'Tajikistan',
  'TZ' => 'Tanzania, United Republic Of',
  'TH' => 'Thailand',
  'TG' => 'Togo',
  'TK' => 'Tokelau',
  'TO' => 'Tonga',
  'TT' => 'Trinidad And Tobago',
  'TN' => 'Tunisia',
  'TR' => 'Turkey',
  'TM' => 'Turkmenistan',
  'TC' => 'Turks And Caicos Islands',
  'TV' => 'Tuvalu',
  'UG' => 'Uganda',
  'UA' => 'Ukraine',
  'AE' => 'United Arab Emirates',
  'GB' => 'United Kingdom',
  'UM' => 'United States Minor Outlying Islands',
  'UY' => 'Uruguay',
  'UZ' => 'Uzbekistan',
  'VU' => 'Vanuatu',
  'VA' => 'Vatican City, State of the',
  'VE' => 'Venezuela',
  'VN' => 'Viet Nam',
  'VG' => 'Virgin Islands (British)',
  'VI' => 'Virgin Islands (U.S.)',
  'WF' => 'Wallis And Futuna Islands',
  'EH' => 'Western Sahara',
  'YE' => 'Yemen',
  'YU' => 'Yugoslavia',
  'ZM' => 'Zambia',
  'ZW' => 'Zimbabwe'
);
?>