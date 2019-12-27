<?php
/** @author Viruscerbero **/

define('D_S', DIRECTORY_SEPARATOR);

/** MVC setup **/

/* Set the path to the models directory */
define('MODELS_DIRECTORY_PATH', ROOT . D_S . 'application' . D_S . 'models');

/* Set the path to the views directory */
define('VIEWS_DIRECTORY_PATH', ROOT . D_S . 'application' . D_S . 'views');

/* Set the path to the layouts directory */
define('LAYOUTS_DIRECTORY_PATH', ROOT . D_S . 'application' . D_S . 'views' .D_S . 'layouts');

/* Set the path to the controllers directory */
define('CONTROLLERS_DIRECTORY_PATH', ROOT . D_S . 'application' . D_S . 'controllers');

/* Set the path to the error pages */
define('ERRORS_DIRECTORY_PATH', dirname(ROOT) . D_S . 'errorPages');


/* Include the configuration.php */
require_once(ROOT . D_S . 'configuration' . D_S . 'configuration.php');


/* Check if environment is development and display errors, do not log them */
if (DEVELOPMENT_ENVIRONMENT == true) {
	error_reporting(E_ALL);

	ini_set('display_errors', 'On');

	ini_set('log_errors', 'On');

	ini_set('error_log', ROOT . D_S . 'tmp' . D_S . 'logs' . D_S . 'error.log');
}
else {  // Environment is production so log errors and do not display them
	error_reporting(E_ALL);

	ini_set('display_errors', 'Off');

	ini_set('log_errors', 'On');

	ini_set('error_log', ROOT . D_S . 'tmp' . D_S . 'logs' . D_S . 'error.log');
}


/* Include the MVC */

/* Include the router class */
require_once(ROOT . D_S .  'library' . D_S . 'router.class.php');

/* Include the model class */
require_once(ROOT . D_S . 'library' . D_S . 'model.class.php');

/* Include the controller class */
require_once(ROOT . D_S . 'library' . D_S . 'controller.class.php');

/* Include the view class */
require_once(ROOT . D_S . 'library' . D_S . 'view.class.php');


function stripSlashesDeep($value) {
	$value = is_array($value) ? array_map('stripSlashesDeep', $value) : stripslashes($value);

	return $value;
}


/**
 * Check for Magic Quotes and remove them
**/
function removeMagicQuotes() {
	if (get_magic_quotes_gpc()) {
		$_GET = stripSlashesDeep($_GET);

		$_POST = stripSlashesDeep($_POST);

		$_COOKIE = stripSlashesDeep($_COOKIE);
	}
}


/**
 * Check register globals and remove them
**/
function unregisterGlobals() {
	if (ini_get('register_globals'))
	{
		$array = array('_SESSION', '_POST', '_GET', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');

		foreach($array as $value)
		{
			foreach($GLOBALS[$value] as $key => $var)
			{
				if ($var === $GLOBALS[$key]) {
					unset($GLOBALS[$key]);
				}
			}
		}
	}
}


/**
 * Automatic class loading
**/
spl_autoload_register(
	function($className)
	{
		$paths = array(
			MODELS_DIRECTORY_PATH,
			VIEWS_DIRECTORY_PATH,
			LAYOUTS_DIRECTORY_PATH,
			CONTROLLERS_DIRECTORY_PATH
		);

		// Search for the files
		foreach($paths as $path)
		{
			if (file_exists("$path/$className.php"))
			{
				require_once("$path/$className.php");
			}
		}
	}
);


/**
 * Load the router
 * @return void
**/
function run() {
	// Get the path from the URL
	$REQUEST_URI = $_SERVER['REQUEST_URI'];

	// Instantiate the router with the URL
	$router = new Router($REQUEST_URI);

	// Dispatch the request
	$router->dispatch();

}


removeMagicQuotes();

unregisterGlobals();

run();
