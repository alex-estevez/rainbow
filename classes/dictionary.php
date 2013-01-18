<?php

namespace Rainbow;

/**
 * Class to do get translated text
 * Allows to manage multilanguage environment
 *
 * Requires PHP 5.3  (for use of namespaces)
 * Requires Rainbow \Rainbow\cPdo (for database connection)
 * Requires Rainbow \Rainbow\cDebug (cPdo requirements)
 * 
 * Requires LANGUAGE to be a defined constant (easy to change).
 * GetText requires Locale configuration
 *
 * @author Alex Estevez
 * @version 1.0
 */
class cDictionary {

  /**
   * Configure this to your preferences
   */

   const use_gettext = false;
   const use_db = true;

  /**
   * Class Constructor (Disabled: private not allowing normal instance)
   */
  private function __construct(); 

  /**
   * Returns the translated text.
   * @param mixed $text_ID if integer, is DB identifier of text to translate. Else is the gettext string to translate
   * @param string $language [Optional] iso language. 'es','en'... Only needed for DB translations.
   * @return string The translated text html encoded.
   */
  static public function get($text_ID, $language = LANGUAGE ) {
    if(is_int($text_ID)) { // Is a numeric identifier.
      $result = self::getDb($text_ID, $language);
    } else { // Is not a numeric identifier
      $result = self::getTxt($text_ID);
    }
    if($result===false) return false;
    return htmlentities($result);
  }

  static public function getDb($text_ID, $language = LANGUAGE ) {
    if(self::use_db===true) { // And can Use DB.
       $result = PdoDb::db()->dbSelect("SELECT `content` FROM `dictionary` WHERE `dictionary_ID` = '$text_ID' and `dictionary_lang` = '$language'");
     } else { // Cannot use DB.
       $result = false;
     }
     return $result; 
  }
  
  static public function getTxt($text_ID ) {
    if(self::use_gettext===true) { // And can use gettext
       $result = gettext($text_ID);
     } else { // Cannot use gettext.
       $result = false;
     }
     return $result; 
  }
  
}