<?php
/**
 * Foundation Helper Functions
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 */

/**
 * Convert ini values like 2M into bytes
 * Copied from http://www.php.net/manual/en/function.ini-get.php
 * @param string $string 
 * @return integer bytes
 */
function convertIniShorthandValue($value){
  $value = trim($value);
  $last = strtolower($value[strlen($value)-1]);
  switch($last) {
    //go from top to bottom and multiply every time
    case 'g':
      $value *= 1024;
    case 'm':
      $value *= 1024;
    case 'k':
      $value *= 1024;
  }
  return $value;
}

/**
 * Convert a file size in bytes to a nice format
 * From http://www.php.net/manual/en/function.filesize.php#91477
 * @param float $bytes
 * @param integer $precision
 * @return string
 */
function convertBytesToString($bytes, $precision = 2) {
    $units = array('b', 'kb', 'mb', 'g', 'tb');
  
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
  
    $bytes /= pow(1024, $pow);
  
    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Create gramatically correct ordinals from integer values
 * @param integer $num
 * @return string
 */
function ordinalValue($num){
  if($num%100 > 10 AND $num%100 < 14){
     $suffix = 'th'; 
  } else {
    $r = $num%10;
    switch($r){
      case 0:
        $suffix = 'th';
        break;
      case 1:
        $suffix = 'st';
        break;
      case 2:
        $suffix = 'nd';
        break;
      case 3:
        $suffix = 'rd';
        break;
      default:
        $suffix = 'th';
        break;  
    }
  }
  return "{$num}<sup>{$suffix}</sup>";
}

/**
 * Check and array of values for null values
 * Javascript posts a string 'null' this function looks through an array
 * with the purpose of converting that to php null type
 * @param array $arr
 * @return array
 */
function replaceNullString($arr){
  foreach(array_keys($arr, 'null', true) as $key){
    $arr[$key] = null;
  }
  return $arr;
}

/**
 * Get a preview thumbnail for a pdf
 * @param blobl $blob
 */
function thumbnailPDF($blob, $width, $height){
  $im = new imagick;
  $im->readimageblob($blob);
  $im->setImageFormat("png");
  $im->scaleimage($width, $height);
  return $im->getimageblob();
}
?>