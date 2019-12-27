<?php
/** @author Viruscerbero
*
* Purposely, the closing "?>" is not included to avoid injection of any extra whitespaces in our output.
* Because this is pure PHP code, it is preferable to omit the PHP closing tag at the end of the file.
* This prevents accidental whitespace or new lines being added after the PHP closing tag, which may cause 
* unwanted effects because PHP will start output buffering when there is no intention from the programmer 
* to send any output at that point in the script.
*/

define('ROOT', dirname(__FILE__));

require_once(ROOT . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'bootstrap.php');
