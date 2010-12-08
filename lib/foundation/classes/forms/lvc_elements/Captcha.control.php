<?php
/**
 * Textarea element form control
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
$errorString = '';
if($element->errorString)
  $errorString = "&amp;error={$element->errorString}";
?>
<script>
var RecaptchaOptions = {
   theme : '<?php print $element->getTheme() ?>'
};
</script>

<script type="text/javascript" src='<?php print $element->server?>/challenge?k=<?php print $element->publicKey . $errorString?>'></script>
<noscript>
    <iframe src='<?php print $element->server ?>/noscript?k=<?php print $element->publicKey . $errorString ?>' height="300" width="500" frameborder="0"></iframe><br/>
    <textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
    <input type="hidden" name="recaptcha_response_field" value="manual_challenge"/>
</noscript>