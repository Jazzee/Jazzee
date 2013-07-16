<?php
namespace Jazzee\Page;

require_once __DIR__ . '/../../../lib/qas/qaddress.inc';

/**
 * QAS Address Verification
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
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

  protected static $_countries = array("United States","Afghanistan","Albania","Algeria","Andorra","Angola","Anguilla","Antigua And Barbuda","Argentina","Armenia","Aruba","Australia","Austria","Azerbaijan","Bahamas","Bahrain","Bangladesh","Barbados","Belarus","Belgium","Belize","Benin","Bermuda","Bhutan","Bolivia","Bosnia And Herzegovina","Botswana","Brazil","Brunei","Bulgaria","Burkina Faso","Burma", "Burundi","Cambodia","Cameroon","Canada","Cape Verde Island","Cayman Islands","Central African Republic","Chad","Chile","China","Colombia","Comoros","Congo, Republic of the","Congo, The Democratic Republic Of The","Cook Islands","Costa Rica","Cote D'Ivoire","Croatia","Cuba","Cyprus","Czech Republic","Denmark","Djibouti","Dominica","Dominican Republic","Ecuador","Egypt","El Salvador","Equatorial Guinea","Eritrea","Estonia","Ethiopia","Falkland Islands (Malvinas)","Faroe Islands","Fiji","Finland","France","French Guiana","French Polynesia","Gabon","Gambia","Georgia","Germany","Ghana","Gibraltar","Greece","Greenland","Grenada","Guadeloupe","Guatemala","Guinea","Guinea-Bissau","Guyana","Haiti","Vatican City","Honduras","Hong Kong","Hungary","Iceland","India","Indonesia","Iran","Iraq","Ireland","Israel","Italy","Jamaica","Japan","Jordan","Kazakhstan","Kenya","Kiribati","Korea, Democratic People's Republic Of (North Korea)","Korea, Republic of (South Korea)","Kuwait","Kyrgyzstan","Lao People's Democratic Republic","Latvia","Lebanon","Lesotho","Liberia","Libya","Liechtenstein","Lithuania","Luxembourg","Macau","Macedonia","Madagascar","Malawi","Malaysia","Maldives","Mali","Malta","Marshall Islands","Martinique","Mauritania","Mauritius","Mexico","Micronesia, Federated States Of","Moldova, Republic Of","Monaco","Montenegro","Mongolia","Montserrat","Morocco","Mozambique","Namibia","Nauru","Nepal","Netherlands","Netherlands Antilles","New Caledonia","New Zealand","Nicaragua","Niger","Nigeria","Niue","Norway","Oman","Pakistan","Palau","Palestinian Territory","Panama","Papua New Guinea","Paraguay","Peru","Philippines","Pitcairn Island","Poland","Portugal","Qatar","Reunion","Romania","Russian Federation","Rwanda","Saint Kitts And Nevis","Saint Lucia","Saint Vincent And The Grenadines","Samoa","San Marino","Sao Tome And Principe","Saudi Arabia","Serbia","Senegal","Seychelles","Sierra Leone","Singapore","Slovakia","Slovenia","Solomon Islands","Somalia","South Africa","South Georgia And The South Sandwich Islands","Spain","Sri Lanka","St. Helena","St. Pierre And Miquelon","Sudan","Suriname","Swaziland","Sweden","Switzerland","Syria","Taiwan","Tajikistan","Tanzania","Thailand","Timor-Leste","Togo","Tonga","Trinidad And Tobago","Tunisia","Turkey","Turkmenistan","Turks And Caicos Islands","Tuvalu","Uganda","Ukraine","United Arab Emirates","United Kingdom","Uruguay","Uzbekistan","Vanuatu","Venezuela","Vietnam","Virgin Islands (British)","Virgin Islands (U.S.)","Wallis And Futuna Islands","Western Sahara","Yemen","Zambia","Zimbabwe");
  /**
   *
   * Enter description here ...
   */
  protected function makeForm()
  {
    return $this->selectCountryForm();
  }
  
  /**
   * Select a country is the first form shown so validation and elements can 
   * be specified for different countries
   * 
   * @return \Foundation\Form
   */
  protected function selectCountryForm()
  {
    $this->_controller->setVar('confirm', false);
    $form = new \Foundation\Form;
    $form->setAction($this->_controller->getActionPath());
    $form->setCSRFToken($this->_controller->getCSRFToken());
    $field = $form->newField();
    $field->setLegend($this->_applicationPage->getTitle());
    $field->setInstructions($this->_applicationPage->getInstructions());

    $element = $field->newElement('SelectList', 'country');
    $element->setLabel('Country');
    foreach (self::$_countries as $value => $label) {
      $element->newItem($value, $label);
    }
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $form->newHiddenElement('level', 1);
    $form->newButton('submit', 'Next');

    return $form;
  }

  public function validateInput($arr)
  {
    if ($input = $this->selectCountryForm()->processInput($arr)) {
      $countryName = $this->selectCountryForm()->getElementByName('country')->getLabelForValue($input->get('country'));
      $form = $this->getFormForCountry($countryName);
      $form->newHiddenElement('countryName', $countryName);
      $form->newHiddenElement('country', $input->get('country'));
      $this->_form = $form;
      if ($input->get('level') == 1) {
        $this->_controller->setVar('confirm', false);
        return false;
      } else {
        if ($input = $this->_form->processInput($arr)) {
          return $this->validateAddress($input);
        }
      }
    }

    return false;
  }

  /**
   * Get the form for a country
   * @param string $countryCode
   * @return \Fondation\Form
   */
  protected function getFormForCountry($countryName)
  {
    $specialCountryForms = array(
      'United States' => 'usAddressForm'
    );
    if(array_key_exists($countryName, $specialCountryForms) and method_exists($this, $specialCountryForms[$countryName])){
      $form = $this->$specialCountryForms[$countryName]();
    } else {
      $form = $this->defaultAddressForm();
    }

    return $form;
  }

  /**
   * Get the form for a country
   * @param string $countryCode
   * @return \Fondation\Form
   */
  protected function getQASCodeForCountry($country)
  {
    $qasCountries = array('United States' => 'USA');
    if(array_key_exists($country, $qasCountries)){
      return $qasCountries[$country];
    }

    return null;
  }

  /**
   * Pick the address form from the country and fill it with data
   * @param int $answerId
   */
  public function fill($answerId)
  {
    if ($answer = $this->_applicant->findAnswerById($answerId)) {
      $fixedElements = array(
        self::FID_ADDRESS1 => 'address1',
        self::FID_ADDRESS2 => 'address2',
        self::FID_ADDRESS3 => 'address3',
        self::FID_CITY => 'city',
        self::FID_STATE => 'state',
        self::FID_POSTALCODE => 'postalCode'
      );
      $element = $this->_applicationPage->getPage()->getElementByFixedId(self::FID_COUNTRY);
      $element->getJazzeeElement()->setController($this->_controller);
      $countryName = $element->getJazzeeElement()->formValue($answer);
      $country = array_search($countryName, self::$_countries);
      if($country === false){
        $this->_controller->addMessage('error', "This address cannot be edited, you will need to delete it and add it again.");
        $this->_controller->redirectPath($this->_controller->getActionPath());
      }
      $form = $this->getFormForCountry($countryName);
      $form->setAction($this->_controller->getActionPath() . "/edit/{$answerId}");
      $form->newHiddenElement('countryName', $countryName);
      $form->newHiddenElement('country', $country);
      $this->_controller->setVar('confirm', false);
      foreach ($fixedElements as $fid => $name) {
        $element = $this->_applicationPage->getPage()->getElementByFixedId($fid);
        $element->getJazzeeElement()->setController($this->_controller);
        $value = $element->getJazzeeElement()->formValue($answer);
        if ($value and $formElement = $form->getElementByName($name)) {
          $formElement->setValue($value);
        }
      }
      $this->_form = $form;
    }
  }

  /**
   * Create the recommenders form
   */
  public function setupNewPage()
  {
    $entityManager = $this->_controller->getEntityManager();
    $types = $entityManager->getRepository('Jazzee\Entity\ElementType')->findAll();
    $elementTypes = array();
    foreach ($types as $type) {
      $elementTypes[$type->getClass()] = $type;
    };

    $element = new \Jazzee\Entity\Element;
    $element->setType($elementTypes['\Jazzee\Element\TextInput']);
    $element->setTitle('Address 3');
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
    foreach ($defaultVars as $name => $value) {
      $var = $this->_applicationPage->getPage()->setVar($name, $value);
      $entityManager->persist($var);
    }
  }

  /**
   * Validate an address with QAS
   * @param \Foundation\Form\Input $input
   * @return array addresses
   */
  protected function validateAddress(\Foundation\Form\Input $input)
  {

    //Check to see if this is the second time the user has inptu this address,
    //if it is then just use that as the address unverified
    $sameUserInput = false;
    if ($str = $input->get('originalInput')) {
      $str = base64_decode($str);
      $originalInput = unserialize($str);
      $sameUserInput = true;
      foreach (array('address1', 'address2', 'address3', 'city', 'state', 'postalCode', 'country') as $name) {
        if ($originalInput->get($name) != $input->get($name)) {
          $sameUserInput = false;
          break;
        }
      }
    }
    $countryName = $input->get('countryName');
    $countriesToValidate = explode(',', $this->_applicationPage->getPage()->getVar('validatedCountries'));
    if ($sameUserInput or !in_array($this->getQASCodeForCountry($countryName), $countriesToValidate)) {
      $input->set('el' . $this->_applicationPage->getPage()->getElementByFixedId(self::FID_ADDRESS1)->getId(), $input->get('address1'));
      $input->set('el' . $this->_applicationPage->getPage()->getElementByFixedId(self::FID_ADDRESS2)->getId(), $input->get('address2'));
      $input->set('el' . $this->_applicationPage->getPage()->getElementByFixedId(self::FID_ADDRESS3)->getId(), $input->get('address3'));
      $input->set('el' . $this->_applicationPage->getPage()->getElementByFixedId(self::FID_CITY)->getId(), $input->get('city'));
      $input->set('el' . $this->_applicationPage->getPage()->getElementByFixedId(self::FID_STATE)->getId(), $input->get('state'));
      $input->set('el' . $this->_applicationPage->getPage()->getElementByFixedId(self::FID_POSTALCODE)->getId(), $input->get('postalCode'));
      $input->set('el' . $this->_applicationPage->getPage()->getElementByFixedId(self::FID_COUNTRY)->getId(), $countryName);

      return $input;
    }

    $search = array();

    $search[0] = $input->get('address1');
    $search[1] = $input->get('address2');
    $search[2] = $input->get('city');
    $search[3] = $input->get('state');
    $search[4] = $input->get('postalCode');

    //Create the QuickAddress Object and set the engine and picklist type
    $qas = new \QuickAddress($this->_applicationPage->getPage()->getVar('wsdlAddress'));
    $qas->setEngineType(QAS_VERIFICATION_ENGINE);
    $qas->setFlatten(true);


    //Perform the search itself
    $result = $qas->search($this->getQASCodeForCountry($countryName), $search, QAS_DEFAULT_PROMPT, "Database layout");
    switch ($result->sVerifyLevel) {
      case 'Verified':
        $arr = $result->address->atAddressLines;
        $input->set('el' . $this->_applicationPage->getPage()->getElementByFixedId(self::FID_ADDRESS1)->getId(), $arr[0]->Line);
        $input->set('el' . $this->_applicationPage->getPage()->getElementByFixedId(self::FID_ADDRESS2)->getId(), $arr[1]->Line);
        $input->set('el' . $this->_applicationPage->getPage()->getElementByFixedId(self::FID_ADDRESS3)->getId(), $input->get('address3'));
        $input->set('el' . $this->_applicationPage->getPage()->getElementByFixedId(self::FID_CITY)->getId(), $arr[3]->Line);
        $input->set('el' . $this->_applicationPage->getPage()->getElementByFixedId(self::FID_STATE)->getId(), $arr[4]->Line);
        $input->set('el' . $this->_applicationPage->getPage()->getElementByFixedId(self::FID_POSTALCODE)->getId(), $arr[5]->Line);
        $input->set('el' . $this->_applicationPage->getPage()->getElementByFixedId(self::FID_COUNTRY)->getId(), $countryName);

        return $input;
          break;
      case 'Multiple':
        $this->_controller->addMessage('error', 'We were unable to validate your address.');
        $this->_controller->setVar('confirm', true);
        $this->_form->getElementByName('submit')->setValue('Confirm Address as Entered');
        $this->_controller->setVar('originalInput', base64_encode(serialize($input)));
        $this->_controller->setVar('picklist', $result->picklist);
          break;
      case 'StreetPartial':
      case 'PremisesPartial':
        $this->_controller->addMessage('error', 'We were unable to validate your address.  If you are sure this address is correct then click the "Confirm Address as Entered" button.');
        $this->getForm()->getElementByName('address1')->addMessage('Your address is incomplete');
        $this->getForm()->getElementByName('address2')->addMessage('Your address is incomplete');
        $this->_form->getElementByName('submit')->setValue('Confirm Address as Entered');
        $this->_form->newHiddenElement('originalInput', base64_encode(serialize($input)));
          break;
      case 'InteractionRequired':
        $this->_controller->addMessage('error', 'We were unable to validate your address.');
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
  public function formatAddress(\Jazzee\Entity\Answer $answer)
  {
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
  public function do_pickAddress($answerId, $postData)
  {
    if (empty($postData['addressMoniker'])) {
      throw new \Jazzee\Exception('Tried to do QASAddress::do_pickAddress with no addressMoniker');
    }
    $qas = new \QuickAddress($this->_applicationPage->getPage()->getVar('wsdlAddress'));
    $address = $qas->getFormattedAddress("Database layout", $postData['addressMoniker']);
    $arr = $address->atAddressLines;
    $originalInput = unserialize(base64_decode($postData['originalInput']));
    $input = new \Foundation\Form\Input($postData);
    $input->set('el' . $this->_applicationPage->getPage()->getElementByFixedId(self::FID_ADDRESS1)->getId(), $arr[0]->Line);
    $input->set('el' . $this->_applicationPage->getPage()->getElementByFixedId(self::FID_ADDRESS2)->getId(), $arr[1]->Line);
    $input->set('el' . $this->_applicationPage->getPage()->getElementByFixedId(self::FID_ADDRESS3)->getId(), $originalInput->get('address3'));
    $input->set('el' . $this->_applicationPage->getPage()->getElementByFixedId(self::FID_CITY)->getId(), $arr[3]->Line);
    $input->set('el' . $this->_applicationPage->getPage()->getElementByFixedId(self::FID_STATE)->getId(), $arr[4]->Line);
    $input->set('el' . $this->_applicationPage->getPage()->getElementByFixedId(self::FID_POSTALCODE)->getId(), $arr[5]->Line);
    $input->set('el' . $this->_applicationPage->getPage()->getElementByFixedId(self::FID_COUNTRY)->getId(), $originalInput->get('countryName'));
    if (!empty($answerId)) {
      $this->updateAnswer($input, $answerId);
      $this->_controller->setVar('currentAnswerID', null);
    } else {
      $this->newAnswer($input);
    }
  }

  /**
   * The Address form for the united stated
   * 
   * @return \Foundation\Form
   */
  protected function usAddressForm()
  {
    $form = new \Foundation\Form;
    $form->setAction($this->_controller->getActionPath());
    $form->setCSRFToken($this->_controller->getCSRFToken());
    $field = $form->newField();
    $field->setLegend($this->_applicationPage->getTitle());
    $field->setInstructions($this->_applicationPage->getInstructions());

    $element = $field->newElement('TextInput', 'address3');
    $element->setLabel('company name, department, etc');
    $element->addValidator(new \Foundation\Form\Validator\MaximumLength($element, 100));

    $element = $field->newElement('TextInput', 'address1');
    $element->setLabel('Address 1');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addValidator(new \Foundation\Form\Validator\MaximumLength($element, 100));

    $element = $field->newElement('TextInput', 'address2');
    $element->setLabel('Address 2');
    $element->addValidator(new \Foundation\Form\Validator\MaximumLength($element, 100));

    $element = $field->newElement('TextInput', 'city');
    $element->setLabel('City');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addValidator(new \Foundation\Form\Validator\MaximumLength($element, 100));

    $element = $field->newElement('SelectList', 'state');
    $element->setLabel('State');
    $states = array('AL'=>"Alabama",'AK'=>"Alaska",'AZ'=>"Arizona",'AR'=>"Arkansas",'CA'=>"California",'CO'=>"Colorado",'CT'=>"Connecticut",'DE'=>"Delaware",'DC'=>"District Of Columbia",'FL'=>"Florida",'GA'=>"Georgia",'HI'=>"Hawaii",'ID'=>"Idaho",'IL'=>"Illinois",'IN'=>"Indiana",'IA'=>"Iowa",'KS'=>"Kansas",'KY'=>"Kentucky",'LA'=>"Louisiana",'ME'=>"Maine",'MD'=>"Maryland",'MA'=>"Massachusetts",'MI'=>"Michigan",'MN'=>"Minnesota",'MS'=>"Mississippi",'MO'=>"Missouri",'MT'=>"Montana",'NE'=>"Nebraska",'NV'=>"Nevada",'NH'=>"New Hampshire",'NJ'=>"New Jersey",'NM'=>"New Mexico",'NY'=>"New York",'NC'=>"North Carolina",'ND'=>"North Dakota",'OH'=>"Ohio",'OK'=>"Oklahoma",'OR'=>"Oregon",'PA'=>"Pennsylvania",'RI'=>"Rhode Island",'SC'=>"South Carolina",'SD'=>"South Dakota",'TN'=>"Tennessee",'TX'=>"Texas",'UT'=>"Utah",'VT'=>"Vermont",'VA'=>"Virginia",'WA'=>"Washington",'WV'=>"West Virginia",'WI'=>"Wisconsin",'WY'=>"Wyoming");
    foreach ($states as $value => $label) {
      $element->newItem($value, $label);
    }
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $element = $field->newElement('TextInput', 'postalCode');
    $element->setLabel('ZIP or Postal Code');
    $element->addValidator(new \Foundation\Form\Validator\MaximumLength($element, 10));
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $form->newButton('submit', 'Save');

    return $form;
  }

  /**
   * The default address form where a specific country form is not available
   * 
   * @return \Foundation\Form
   */
  protected function defaultAddressForm()
  {
    $form = new \Foundation\Form;
    $form->setAction($this->_controller->getActionPath());
    $form->setCSRFToken($this->_controller->getCSRFToken());
    $field = $form->newField();
    $field->setLegend($this->_applicationPage->getTitle());
    $field->setInstructions($this->_applicationPage->getInstructions());

    $element = $field->newElement('TextInput', 'address3');
    $element->setLabel('company name, department, etc');
    $element->addValidator(new \Foundation\Form\Validator\MaximumLength($element, 100));

    $element = $field->newElement('TextInput', 'address1');
    $element->setLabel('Address 1');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addValidator(new \Foundation\Form\Validator\MaximumLength($element, 100));

    $element = $field->newElement('TextInput', 'address2');
    $element->setLabel('Address 2');
    $element->addValidator(new \Foundation\Form\Validator\MaximumLength($element, 100));

    $element = $field->newElement('TextInput', 'city');
    $element->setLabel('City (territory, provinces, etc)');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addValidator(new \Foundation\Form\Validator\MaximumLength($element, 100));

    $element = $field->newElement('TextInput', 'postalCode');
    $element->setLabel('ZIP or Postal Code');
    $element->addValidator(new \Foundation\Form\Validator\MaximumLength($element, 10));

    $form->newButton('submit', 'Save');

    return $form;
  }

  public static function applyPageElement()
  {
    return 'QASAddress-apply_page';
  }

  public static function pageBuilderScriptPath()
  {
    return 'resource/scripts/page_types/JazzeePageQASAddress.js';
  }

}