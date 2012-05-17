<?php

################################################################
#
# QAS Pro Web - PHP Integration code
# (c) QAS Ltd - www.qas.com
#
# qaddress.inc - QAS common classes
#
################################################################


# Constants

define("QAS_SINGLELINE_ENGINE", "Singleline");

define("QAS_TYPEDOWN_ENGINE", "Typedown");
define("QAS_VERIFICATION_ENGINE", "Verification");
define("QAS_KEYSEARCH_ENGINE", "Keyfinder");

define("QAS_EXACT_SEARCHING", "Exact");
define("QAS_CLOSE_SEARCHING", "Close");
define("QAS_EXTENSIVE_SEARCHING", "Extensive");

define("QAS_ONELINE_PROMPT", "OneLine");
define("QAS_DEFAULT_PROMPT", "Default");
define("QAS_GENERIC_PROMPT", "Generic");
define("QAS_OPTIMAL_PROMPT", "Optimal");
define("QAS_ALTERNATE_PROMPT", "Alternate");
define("QAS_ALTERNATE2_PROMPT", "Alternate2");
define("QAS_ALTERNATE3_PROMPT", "Alternate3");

define("QAS_MATCH_INTERACTION", "InteractionRequired");
define("QAS_MATCH_MULTIPLE", "Multiple");
define("QAS_MATCH_NONE", "None");
define("QAS_MATCH_PREMISES", "PremisesPartial");
define("QAS_MATCH_STREET", "StreetPartial");
define("QAS_MATCH_VERIFY", "Verified");
define("QAS_MATCH_VERIFIEDSTREET", "VerifiedStreet");
define("QAS_MATCH_VERIFIEDPLACE", "VerifiedPlace");

define("QAS_LANGUAGE_DEFAULT", "");
define("QAS_LANGUAGE_ENGLISH_UK", "044");
define("QAS_LANGUAGE_ENGLISH_USA", "001");
define("QAS_LANGUAGE_ENGLISH_AUS", "044");
define("QAS_LANGUAGE_DUTCH", "031");
define("QAS_LANGUAGE_FRENCH", "033");
define("QAS_LANGUAGE_SPANISH", "034");
define("QAS_LANGUAGE_GERMAN", "049");

define("QAS_LINE_SEPARATOR", "|");
define("QAS_BULK_LINE_SEPARATOR", "\\");


# Picklist class - list of picklist items
#
class Picklist
{
    var $iTotal           = 0;
    var $sPicklistMoniker = "";
    var $sPrompt          = "No Items";
    var $atItems          = array();
    var $isTimeout;
    var $isMaxMatches;
    var $bOverThreshold;
    var $bLargePotential;
    var $bMoreOtherMatches;
    var $bAutoStepinSafe;
    var $bAutoStepinPastClose;
    var $bAutoFormatSafe;
    var $bAutoFormatPastClose;

    # Picklist constructor - make sure that the picklist items are consistently an array
    #
    function Picklist($result)
    {
        if (QuickAddress::check_soap($result) != NULL && ($tPicklist=$result->QAPicklist) != NULL)
        {
            $this->iTotal              = $tPicklist->Total;
            $this->sPrompt             = $tPicklist->Prompt;
            $this->sPicklistMoniker    = $tPicklist->FullPicklistMoniker;
            $this->isTimeout           = $tPicklist->Timeout;
            $this->isMaxMatches        = $tPicklist->MaxMatches;
            $this->bOverThreshold      = $tPicklist->OverThreshold;
            $this->bLargePotential     = $tPicklist->LargePotential;
            $this->bMoreOtherMatches   = $tPicklist->MoreOtherMatches;
            $this->bAutoStepinSafe     = $tPicklist->AutoStepinSafe;
            $this->bAutoStepinPastClose= $tPicklist->AutoStepinPastClose;
            $this->bAutoFormatSafe     = $tPicklist->AutoFormatSafe;
            $this->bAutoFormatPastClose= $tPicklist->AutoFormatPastClose;

            if (!isset($tPicklist->PicklistEntry))
                $this->atItems = array();

            elseif (is_array($tPicklist->PicklistEntry))
                $this->atItems = $tPicklist->PicklistEntry;

            else
                $this->atItems = array($tPicklist->PicklistEntry);
        }
    }

    # Is a picklist object suitable for auto-stepin?
    #
    function isAutoStepinSingle() 
    {
        return( $this->iTotal == 1 &&
                $this->atItems[ 0 ]->CanStep &&
                !$this->atItems[ 0 ]->Information );
    }

    # Is a picklist object suitable for auto-format?
    #
    function isAutoFormatSingle() 
    {
        return( $this->iTotal == 1 &&
                $this->atItems[ 0 ]->FullAddress &&
                !$this->atItems[ 0 ]->Information );
    }
}


# Address class - final formatted address
#
class Address
{
    var $atAddressLines;
    var $bOverflow;
    var $bTruncated;

    # Address constructor - make sure that address lines are consistently an array
    #
    function Address($tQAAddress)
    {
        $this->atAddressLines = $tQAAddress->AddressLine;
        $this->bOverflow      = $tQAAddress->Overflow;
        $this->bTruncated     = $tQAAddress->Truncated;

        if (!is_array($this->atAddressLines))
            $this->atAddressLines = array($this->atAddressLines);

        for ($i=0; $i < sizeof($this->atAddressLines); $i++)
        {
            if (isset($this->atAddressLines[$i]->DataplusGroup) && !is_array($this->atAddressLines[$i]->DataplusGroup))
            {
                $this->atAddressLines[$i]->DataplusGroup=array($this->atAddressLines[$i]->DataplusGroup);
            }
        }
    }
}


# FormattedAddress class - final formatted address handler
#
class FormattedAddress extends Address
{
# FormattedAddress constructor - check for SOAP errors then call Address constructor
#
    function FormattedAddress($result)
    {
        if (QuickAddress::check_soap($result) != NULL)
        {
            parent::Address($result->QAAddress);
        }
    }
}


# Examples class - list of example addresses
#
class Examples
{
    var $atAddress = NULL;
    var $asComment = NULL;

    # Examples constructor - check for SOAP errors, then build an address/comment array
    #
    function Examples($result)
    {
        if (QuickAddress::check_soap($result) != NULL)
        {
            if (is_array($result->ExampleAddress))
            {
                foreach ($result->ExampleAddress AS $tAddress)
                {
                    $this->atAddress[] = new Address($tAddress->Address);
                    $this->asComment[] = $tAddress->Comment;
                }
            }
            else
            {
                $this->atAddress[] = new Address($result->ExampleAddress->Address);
                $this->asComment[] = $result->ExampleAddress->Comment;
            }
        }
    }
}


# SearchResult class - result of an intial search (final address or picklist)
#
class SearchResult
{
    var $picklist;
    var $address;
    var $sVerifyLevel;

    # SearchResult constructor - check SOAP then attach picklist & address objects
    #
    function SearchResult($result)
    {
        if (QuickAddress::check_soap($result) != NULL)
        {
            if (isset($result->QAPicklist))
                $this->picklist = new Picklist($result);

            if (isset($result->QAAddress))
            {
                $this->address = new FormattedAddress($result);
            }
 
            $this->sVerifyLevel = $result->VerifyLevel;
        }
    }
}


class BulkSearchItem
{
    var $fAddress;
    var $sVerifyLevel;
    var $sInputAddress;

    function BulkSearchItem($result)
    {
        if (isset($result->QAAddress))
        {
            $this->fAddress         = new FormattedAddress($result);
        }
        $this->sVerifyLevel     = $result->VerifyLevel;
        $this->sInputAddress    = $result->InputAddress;
    }
}

class BulkSearchResult
{
    var $bulkSearchItems;
    var $bulkError;
    var $errorCode;
    var $sCount;
    var $sSsearchCount;


    # SearchResult constructor - check SOAP then attach picklist & address objects
    #
    function BulkSearchResult($result)
    {
        if (QuickAddress::check_soap($result) != NULL)
        {
            if (is_array($result->BulkAddress))
            {
                foreach ($result->BulkAddress AS $tBulkSearchItem)
                {
                    $this->bulkSearchItems[] = new BulkSearchItem($tBulkSearchItem);
                }
            }
            else
            {
                $this->bulkSearchItems[] = new BulkSearchItem($result->BulkAddress);
            }
            
            if (isset($result->BulkError))
                $this->bulkError = $result->BulkError;

            if (isset($result->ErrorCode))
            {
                $this->errorCode = $result->ErrorCode;
            }

            $this->sCount       = $result->Count;
            $this->sSearchCount = $result->SearchCount;
        }
    }
}


# PromptSet class - list of prompt lines

class PromptSet
{
    var $atLines  = NULL;
    var $bDynamic = NULL;

    # PromptSet constructor - check SOAP then make sure that prompt lines are consistently an array
    #
    function PromptSet($result)
    {
        if (QuickAddress::check_soap($result) != NULL)
        {
            if (is_array($result->Line))
                $this->atLines=$result->Line;
            else
                $this->atLines=array($result->Line);

            $this->bDynamic=$result->Dynamic;
        }
    }
}


# QuickAddress class - service worker
#
class QuickAddress
{
    var $sEngineType        = QAS_SINGLELINE_ENGINE;
    var $sConfigFile        = "";
    var $sConfigSection     = "";
    var $sEngineIntensity   = "";
    var $iThreshold         = 0;
    var $iTimeout           = -1;
    var $bFlatten           = FALSE;
    var $sLanguage          = QAS_LANGUAGE_DEFAULT;
    var $soap               = NULL;

    # QuickAddress constructor - suppress exception generation as we want to keep this integration code
    # PHP4 compatible
    #
    function QuickAddress($sEndpointURL)
    {
        $this->soap=new SoapClient($sEndpointURL, array('exceptions' => 0));

        if (is_soap_fault($this->soap))
        {
            $this->soap=NULL;
        }
    }

    # Check a result for a soap fault object, and log it to the PHP log channel
    #
    static function check_soap($soapResult)
    {
        if (is_soap_fault($soapResult))
        {
            $err = "QAS SOAP Fault - " . "Code: {" . $soapResult->detail->QAFault->ErrorCode . "}, " . "Description: {"
                   . $soapResult->detail->QAFault->ErrorMessage . "}";

            error_log($err, 0);

            $soapResult = NULL;
        
            throw new Exception($err);
        }

        return ($soapResult);
    }

    # Get the last QAS Soap fault
    #
    function getSoapFault() 
    {
        return (isset($this->soap->__soap_fault) ? $this->soap->__soap_fault->detail->QAFault : NULL); 
    }

    # Setup a fault string for display
    #
    function getFaultString($sFault)
    {
        if ((!is_string($sFault) || $sFault == "") && ($this->getSoapFault() != NULL))
            return ($this->getSoapFault()->ErrorMessage . " [" . $this->getSoapFault()->ErrorCode . "]");
        else
            return ($sFault);
    }

    # Set the engine type to use
    #
    function setEngineType($sType)
    {
        $this->sEngineType = $sType;
    }

    function setLanguage($sLang)
    {
        $this->sLanguage = $sLang;
    }

    # Set the engine intensity
    #
    function setEngineIntensity($sIntensity)
    {
        $this->sEngineIntensity = $sIntensity;
    }

    # Set the picklist threshold
    #
    function setThreshold($iThreshold)
    {
        $this->iThreshold = $iThreshold;
    }

    # Set the search/refinement timeout
    #
    function setTimeout($iTimeout)
    {
        $this->iTimeout = $iTimeout;
    }

    # Set flattened mode
    #
    function setFlatten($bFlatten)
    {
        $this->bFlatten = $bFlatten;
    }

    # Set configuration file to use
    #
    function setConfigFile($sConfig)
    {
        $this->sConfigFile = $sConfig;
    }

    # Set configuration section to use
    #
    function setConfigSection($sSection)
    {
        $this->sConfigSection = $sSection;
    }

    # Get a list of available data sets - and check that the result is consistently an array
    #
    function getAllDataSets()
    {
       
        $args = array("_" => NULL );
    
        $args = $this->addLanguage($args);

        $data=$this->soap->DoGetData($args);
        
        if (is_soap_fault($data) || $data == NULL)
            return (NULL);

        $result=$data->DataSet;

        if (is_array($result))
            return ($result);

        return (array($result));
    }

    # Get a list of available datamap detail - and check that the result is consistently an array
    #
    function getAllDataMapDetail($sID)
    {    
        $args = array( "DataMap" => $sID );
        
        $args = $this->addLanguage($args);
                     
        $result=$this->check_soap($this->soap->DoGetDataMapDetail($args));

        if ($result != NULL)
        {
            $result=$result->LicensedSet;

            if (is_array($result))
            {
                return ($result);
            }
            else
            {
                return (array($result));
            }
        }
        else
        {
            return (NULL);
        }
    }


    # Get a list of available layouts - and check that the result is consistently an array
    #
    function getLayouts($sDataSetID)
    {
        $args = array("Country" => $sDataSetID);
        
        $args = $this->addLanguage($args);
        
        $result = $this->check_soap($this->soap->DoGetLayouts($args));

        if ($result != NULL)
        {
            $result = $result->Layout;

            if (is_array($result))
                return ($result);
            else
                return (array($result));
        }
        else
        {
            return (array());
        }
    }

    # Test whether a search can be performed for a layout/dataset combination by checking licensing, etc.
    #
    # Parameters:
    #   sDataSetID      ID of the dataset to be searches
    #   sLayoutName     Name of the output layout (verification mode only)
    #   sPromptSet      (optional) Name of the prompt set used for these search terms
    #
    # Return the result object on success, else FALSE
    #
    function canSearch($sDataSetID, $sLayoutName, $sPromptSet = NULL)
    {

        # Set engine type and options - "_" is reserved by PHP SOAP to indicate the
        # tag value while the other elements of the array set attribute values
        $aEngineOptions = array
            (
            "_"       => $this->sEngineType,
            "Flatten" => $this->bFlatten
            );
            
        # Set prompt set if not default
        if ($sPromptSet != NULL)
            $aEngineOptions["PromptSet"]=$sPromptSet;

        $args = array
            (
            "Country" => $sDataSetID,
            "Engine"  => $aEngineOptions,
            );

        # Set flatten if not default
        if ($this->bFlatten != NULL)
            $args["Flatten"] = $this->bFlatten;
            

        # Set layout (for verification engine) if not default
        if ($sLayoutName != NULL)
            $args["Layout"] = $sLayoutName;
            
        $args = $this->addLanguage($args);

        return ($this->check_soap($this->soap->DoCanSearch($args)));
                    
    }

    # Perform an initial search
    #
    # Parameters:
    #   sDataSetID      ID of the dataset to be searched
    #   asSearch        array of search terms
    #   sPromptSet      (optional) Name of the prompt set used for these search terms
    #   sVerifyLayout   (optional) Name of the output layout (verification mode only)
    #
    # Return a picklist containing the results of the search
    #
    function search($sDataSetID, $asSearch, $sPromptSet = NULL, $sVerifyLayout = NULL)
    {
        $this->sDataSetID=$sDataSetID;

        # Concatenate each line of input to a search string delimited by line separator characters
        $sSearchString   = "";
        $bFirst          = TRUE;

        if (isset($asSearch))
        {
            if (is_array($asSearch))
            {
                foreach ($asSearch AS $sSearch)
                {
                    if (!$bFirst)
                    {
                        $sSearchString=$sSearchString . QAS_LINE_SEPARATOR;
                    }

                    $sSearchString = $sSearchString . $sSearch;
                    $bFirst        = FALSE;
                }
            }
            else
            {
                $sSearchString = $asSearch;
            }
        }

        # Set engine type and options - "_" is reserved by PHP SOAP to indicate the
        # tag value while the other elements of the array set attribute values
        $aEngineOptions = array
            (
            "_"       => $this->sEngineType,
            "Flatten" => $this->bFlatten
            );

        # Set prompt set if not default
        if ($sPromptSet != NULL)
            $aEngineOptions["PromptSet"] = $sPromptSet;

        # Set threshold if not default
        if ($this->iThreshold != 0)
            $aEngineOptions["Threshold"] = $this->iThreshold;

        # Set timeout if not default
        if ($this->iTimeout != -1)
            $aEngineOptions["Timeout"] = $this->iTimeout;


        # Build main search arguments
        $args = array
            (
            "Country" => $this->sDataSetID,
            "Search"  => $sSearchString,
            "Engine"  => $aEngineOptions
            );

        # Are we using a non-default configuration file or section ?
        # then setup the appropriate tags
        if ($this->sConfigFile != "" || $this->sConfigSection != "")
        {
            $asConfig=array();

            if ($this->sConfigFile != "")
                $asConfig["IniFile"] = $this->sConfigFile;

            if ($this->sConfigSection != "")
                $asConfig["IniSection"] = $this->sConfigSection;

            $args["QAConfig"] = $asConfig;
        }

        # Set layout (for verification engine) if not default
        if ($sVerifyLayout != NULL)
            $args["Layout"] = $sVerifyLayout;

        $args = $this->addLanguage($args);

        # Perform the web service call and create a SearchResult instance with the result
        return (new SearchResult($this->soap->DoSearch($args)));
    }


    # Perform an initial bulk search
    #
    # Parameters:
    #   sDataSetID      ID of the dataset to be searched
    #   asSearch        array of search terms
    #   sPromptSet      (optional) Name of the prompt set used for these search terms
    #   sVerifyLayout   (optional) Name of the output layout (verification mode only)
    #
    # Return a picklist containing the results of the search
    #
    function bulkSearch($sDataSetID, $asSearch, $sPromptSet = NULL, $sVerifyLayout = NULL)
    {
        $this->sDataSetID = $sDataSetID;

        # Concatenate each line of input to a search string delimited by line separator characters
        $sSearchString   = "";
        $bFirst          = TRUE;

        # Set engine type and options - "_" is reserved by PHP SOAP to indicate the
        # tag value while the other elements of the array set attribute values
        $aEngineOptions = array
            (
            "_"       => $this->sEngineType,
            "Flatten" => $this->bFlatten
            );

        # Set prompt set if not default
        if ($sPromptSet != NULL)
            $aEngineOptions["PromptSet"] = $sPromptSet;

        # Set threshold if not default
        if ($this->iThreshold != 0)
            $aEngineOptions["Threshold"] = $this->iThreshold;

        # Set timeout if not default
        if ($this->iTimeout != -1)
            $aEngineOptions["Timeout"] = $this->iTimeout;


        # Build main search arguments
        $args = array
            (
            "Country" => $this->sDataSetID,
            "Engine"  => $aEngineOptions
            );

        # Are we using a non-default configuration file or section ?
        # then setup the appropriate tags
        if ($this->sConfigFile != "" || $this->sConfigSection != "")
        {
            $asConfig = array();

            if ($this->sConfigFile != "")
                $asConfig["IniFile"] = $this->sConfigFile;

            if ($this->sConfigSection != "")
                $asConfig["IniSection"] = $this->sConfigSection;

            $args["QAConfig"]=$asConfig;
        }

        if ($asSearch != "")
        {
            $asSearchTerm=array();

            $asSearchTerm["Search"] =$asSearch;
            $asSearchTerm["Count"] = sizeof($asSearch);
            $args["BulkSearchTerm"] = $asSearchTerm;
        }


        # Set layout (for verification engine) if not default
        if ($sVerifyLayout != NULL)
            $args["Layout"] = $sVerifyLayout;

        $args = $this->addLanguage($args);

        # Perform the web service call and create a SearchResult instance with the result

        return (new BulkSearchResult($this->soap->DoBulkSearch($args)));
    }


    # Perform an initial search using the Singleline engine, returning a picklist.
    #
    # Parameters:
    #   sDataSetID  ID of the dataset to be searched
    #   asSearch    array of search terms
    #
    # Return a Picklist item
    #
    function searchSingleline($sDataSetID, $asSearch, $sPromptSet = NULL)
    {
        $engineOld         = $this->sEngineType;
        $this->sEngineType = QAS_SINGLELINE_ENGINE;

        $searchResult      = $this->search($sDataSetID, $asSearch, $sPromptSet);
        $this->sEngineType = $engineOld;

        return ($searchResult->picklist);
    }

    # Perform a refinement
    #
    # Parameters:
    #   sRefinementText     Text on which to refine
    #   sMoniker            Search point moniker of the picklist being refined
    #
    # Return A picklist instance containing the results of the refinement
    #
    function refine($sMoniker, $sRefinementText, $sLayoutName = NULL)
    {
        $args = array
            (
            "Moniker"    => $sMoniker,
            "Refinement" => $sRefinementText
            );

        if ($this->iThreshold != 0)
        {
            $args["Threshold"] = $this->iThreshold;
        }

        if ($this->iTimeout != -1)
        {
            $args["Timeout"] = $this->iTimeout;
        }
        
        # Set layout if not default
        if ($sLayoutName != NULL)
            $args["Layout"]=$sLayoutName;

        $args = $this->addLanguage($args);

        return (new Picklist($this->soap->DoRefine($args)));
    }

    # Perform a step-in
    #
    # Parameters:
    #   sMoniker    The search point moniker of the picklist item to be entered
    #
    # Return A picklist instance containing the results of the refinement
    #
    function stepIn($sMoniker, $sLayoutName = NULL)
    {
        # A stepin simply creates a picklist from the supplied moniker with a null refinement
        $args = array
            (
            "Moniker"    => $sMoniker,
            "Refinement" => ""
            );

        # If the threshold or timeout values are not default then specify them
        if ($this->iThreshold != 0)
        {
            $args["Threshold"] = $this->iThreshold;
        }

        if ($this->iTimeout != -1)
        {
            $args["Timeout"] = $this->iTimeout;
        }
        
        # Set layout if not default
        if ($sLayoutName != NULL)
            $args["Layout"] = $sLayoutName;

        $args = $this->addLanguage($args);

        return (new Picklist($this->soap->DoRefine($args)));
    }

    # Get a prompt set
    #
    # Parameters:
    #   sDataSetID  ID of the dataset whose prompt sets is required
    #   sPromptSet  String identifying the type of prompt e.g. "Optimal"
    #
    # Return the prompt set (i.e. array of prompt lines) identified by the name and country.
    #
    function getPromptSet($sDataSetID, $sPromptSet = QAS_DEFAULT_PROMPT, $sEngine = QAS_SINGLELINE_ENGINE)
    {
        $args = array
            (
            "Country"   => $sDataSetID,
            "PromptSet" => $sPromptSet,
            "Engine"    => $sEngine
            );
            
        $args = $this->addLanguage($args);
        
        $ret = $this->check_soap($this->soap->DoGetPromptSet($args));

        return (new PromptSet($ret));
    }

    # Get a formatted address from layout and a moniker
    #
    # Parameters:
    #   sLayoutName     Layout name (specifies how the address should be formatted)
    #   sMoniker        Search point moniker string that represents the address
    #
    # Return the appropriate FormattedAddress object.
    #
    function getFormattedAddress($sLayoutName, $sMoniker)
    {
        
        $args = array
            (
            "Layout"  => $sLayoutName,
            "Moniker" => $sMoniker
            );
        
        $args = $this->addLanguage($args);	
        
        $result = $this->soap->DoGetAddress($args);

        return (new FormattedAddress($result));
    }

    # Get all layouts appropriate for a data set
    #
    # Parameters:
    #   sDataSetID  ID of the dataset whose layouts are required
    #
    # Return an array of layouts available to the server for the specified data set
    #
    function getAllLayouts($sDataSetID)
    {
        $args = array("Country" => $sDataSetID);
        
        $args = $this->addLanguage($args);
        
        $result = $this->check_soap($this->soap->DoGetLayouts($args));

        if ($result != NULL)
        {
            if (is_array($result->Layout))
                return ($result->Layout);
            else
                return (array($result->Layout));
        }
        else
        {
            return (array());
        }
    }

    # Get example addresses for a layout
    #
    # Parameters:
    #   sDataSetID      <code>String</code> ID of the dataset for which examples are required
    #   sLayoutName     <code>String</code> name of the layout for the example
    #
    # Return an array of example addresses for the country/layout combination
    #
    function getExampleAddresses($sDataSetID, $sLayoutName)
    {
        
        $args = array
            (
            "Country" => $sDataSetID,
            "Layout"  => $sLayoutName
            );
            
        $args = $this->addLanguage($args);
        
        $result = $this->check_soap($this->soap->DoGetExampleAddresses($args));

        return (new Examples($result));
    }

    # Get licensing information
    #
    # Returns an array of LicensedSet objects detailling the licence state.
    #
    function getLicenceInfo()
    {
        $args = array( "_" => NULL );
        
        $args = $this->addLanguage($args);
        
        $result = $this->check_soap($this->soap->DoGetLicenseInfo($args));

        if ($result != NULL)
        {
            if (is_array($result->LicensedSet))
                return ($result->LicensedSet);
            else
                return (array($result->LicensedSet));
        }
        else
        {
            return (NULL);
        }
    }

    # Get system configuration information
    #
    # Returns an array of strings each with a line of system info
    #
    function getSystemInfo()
    {
        $args = array( "_" => NULL );
        
        $args = $this->addLanguage($args);	
        
        $result = $this->soap->DoGetSystemInfo($args);

        if (is_soap_fault ($result))
            return (NULL);

        if (is_array($result->SystemInfo))
            return ($result->SystemInfo);

        return (array($result->SystemInfo));
    }
    
    
    function addLanguage( $args )
    {
        if ( $this->sLanguage != QAS_LANGUAGE_DEFAULT )
        {
            $args["Language"] = $this->sLanguage;	
        }
        
        return $args;	
    }
    
} // End class QuickAddress

# Define a class to handle history stacks

class Stack
{
    var $aItems = array();

    function Stack($sField)
    {
        if (isset($_REQUEST[$sField]))
        {
            $asItems = $_REQUEST[$sField];

            if (is_array($asItems))
            {
                $this->aItems=handleslash($asItems);
            }
        }
    }

    function push($sItem) { array_push($this->aItems, $sItem); }

    function pop() { return (array_pop($this->aItems)); }

    function peek() { return (end($this->aItems)); }

    function clear()
    {
        $this->aItems=array();
    }

    function size() { return (count($this->aItems)); }

    function toarray() { return ($this->aItems); }

    function firstElement() { return ($this->aItems[0]); }
}

# Automatic quote handling function
#
# If the PHP setting "magic quotes" is set, POSTed fields will come
# through with slash prefixing. This function strips slashes from
# input strings or arrays of string.
#
# Without magic quotes set, the parameter is simply returned unchanged
#
function handleslash($object)
{
    if (get_magic_quotes_gpc())
        if (is_array($object))
        {
            foreach ($object AS $item)
                $aOut[]=stripslashes($item);

            return ($aOut);
        }
        else
            return (stripslashes($object));
    else
        return ($object);
}

# This function flattens a string array to comma separated quoted strings
# suitable for a javascript Array constructor

function StrArrayToList($array)
{
    $ret="";

    if (is_array($array))
    {
        $bFirst=TRUE;

        foreach ($array AS $s)
        {
            if (!$bFirst)
                $ret=$ret . ",";

            $ret   =$ret . "'" . $s . "'";
            $bFirst=FALSE;
        }
    }

    return ($ret);
}
?>
