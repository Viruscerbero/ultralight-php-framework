<?php
/** @author Viruscerbero
**  The Class Router is the one responsible of loading the right model and view, 
**  and also of passing the action and parameters to the controller.
**  The parameters come from the URL.
**/
class Router 
{
	private $model;

	private $view;

	private $parameters;


	/**
	 * @Ctor
	*/
	public function __construct($REQUEST_URI) {
		$route = str_replace('/' . MAIN_APP_DIRECTORY . '/', '', $REQUEST_URI);

		/* Get the model, view, controller and the action names, and also the function arguments. 
		*  explode returns an array of strings, each of which is a substring formed by splitting it on boundaries formed by the string delimiter.
		*  If limit is set and positive, the returned array will contain a maximum of limit elements with the last element containing the rest of the string */

		list($modelName, $view_controller_name, $actionName, $parametros) = 
			array_pad(explode("/", $route, 4), 4, null);

		if (
			(!isset($modelName) || empty($modelName)) ||
			(!isset($view_controller_name) || empty($view_controller_name))
		) {
			// Send a 404 Error Page
			header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found'); //header("HTTP/1.0 404 Not Found");

			// include (ERRORS_DIRECTORY_PATH . D_S . 'error404' . D_S . 'error404.html');

			exit();
		}

		///// The action name /////
		$this->actionName = "{$actionName}Action";

		$explodedParameters = array();

		if (isset($parametros)) {
			parse_str($parametros, $explodedParameters);
		}

		///// The parameters /////
		$this->parameters = $explodedParameters;

		/* FIXME The MVC should be created using a Builder pattern (or another creational pattern) so that the model can decide wheter it needs a view or not, and the view can decide what controller it will use */
		$this->constructModel($modelName);

		$this->constructView($view_controller_name);

	}


	private function constructModel($modelName) {
		$modelFile = MODELS_DIRECTORY_PATH . D_S . "{$modelName}Model.php";

		// If the model file is there...
		if ( is_readable($modelFile) ) {
			// Require the model file
			require_once $modelFile;

			// The model class name
			$modelClassName = ucfirst( strtolower($modelName) );

			// Instantiate the model
			$this->model = new $modelClassName();
		}
		else {
			// The requested model doesn't exist
			// Send a 404 Error Page
			header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found'); //header("HTTP/1.0 404 Not Found");

			// include(ERRORS_DIRECTORY_PATH . D_S . 'error404' . D_S . 'error404.html');

			exit();
		}

	}


	protected function constructView($viewName) {
		$viewFile = VIEWS_DIRECTORY_PATH . D_S . "{$viewName}View.php";

		// If the view file is there
		if ( is_readable($viewFile) ) {
		  // include the view file
		  require_once $viewFile;

		  // The view class name
		  $viewClassName = ucfirst( strtolower($viewName) ).'View';

		  // Instantiate the view
		  $this->view = new $viewClassName($this->model);
		}
		else {
		  // Send a 404 Error Page
		  header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found'); //header("HTTP/1.0 404 Not Found");

		  // include(ERRORS_DIRECTORY_PATH . D_S . 'error404' . D_S . 'error404.html');

		  exit();
		}

	}


	/**
	* Dispatch the request
	*/
	public function dispatch() {
		// Get the view's active controller (if there were any) and pass it the action and the parameters
		$controller = $this->view->getActiveController();

		if (
			method_exists($controller, $this->actionName) &&
			is_callable(array($controller, $this->actionName))
		) {
			call_user_func_array(
				array($controller, $this->actionName),
				array($this->parameters)
			);
		}
		else {
			// send a 404 Error Page
			header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found'); //header("HTTP/1.0 404 Not Found");

			// include( ERRORS_DIRECTORY_PATH . D_S . 'error404' . D_S . 'error404.html' );

			exit();
		}

	}


}// End class

?>
