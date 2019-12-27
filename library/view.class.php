<?php
/** @author Viruscerbero
**  A view uses an instance of a Controller subclass to implement a particular response strategy.
**  The view uses a controller (although a view might not use none).
**  To implement a different strategy, simply replace the instance with a different kind of controller.
**  The View-Controller relationship is an example of the Strategy design pattern.
**  MVC supports nested views with the CompositeView class, a subclass of View. 
**  CompositeView objects act just like View objects.
**  A composite view can be used wherever a view can be used, but it also contains and manages nested views.
**  The View always has a contract with a model.
**  It may be useful for multiple views to use the same data.
**/
Abstract class View
{
	protected $model;

	protected $controller;

	/*array*/
	protected $viewVars;

	protected $currentLayout;


	/**
	 * @Ctor
	*/
	public function __construct(Model $model = null) {
		$this->model = $model;

		$this->viewVars = array();

		if ($model) {
			// The view suscribes itself to the model
			$this->model->registerObserver($this);
		}

		// This is a function to implement whatever is necessary 
		// (like instantiating the controller) before using the view
		$this->initView();

	}// End ctor


	/**
	* This function can be implemented in a different way on each view,
	* and it is necessary for guaranteeing the completion of the constructor's Template Method pattern.
	*/
	abstract protected function initView();


	/**
	* Assign a layout
	* @param string $layoutName
	*/
	public function setLayout($layoutName) {
		$layoutFile = LAYOUTS_DIRECTORY_PATH . D_S . "{$layoutName}.php";

		if ( is_readable($layoutFile) ) {
			$this->currentLayout = $layoutFile;
		}
		else {
			echo("The layout {$layoutName}.php can't be found");

			exit();
		}

	}// End setLayout


	/**
	* Make variables assigned to the layout accesible to the view.
	* @param string $variable
	* @param mixed $valor
	* @return void
	*/
	protected function assign($variable, $valor) {
		$this->viewVars[$variable] = $valor;

	}


	/**
	* This function creates the controller
	*/
	protected function createController($controllerName) {
		// If the controller file is there...
		$controllerFile = CONTROLLERS_DIRECTORY_PATH . D_S . "{$controllerName}Controller.php";

		if ( is_readable($controllerFile) ) {
			// include the controller file
			require_once $controllerFile;

			// The controller class name
			$controllerClassName = ucfirst( strtolower($controllerName) ).'Controller';

			// Instantiate the controller (it doesn't matter if the model is null)
			$this->controller = new $controllerClassName($this->model, $this);
		}
		else {
			// There is no controller
			$this->controller = null;
		}

	}// End createController


	public function getActiveController() {
		return $this->controller;
	}


	/**
	* This function can be overwritten and reimplemented in different ways on each view
	*/ 
	public function render() {
		// Make the variables passed to the view accessible from the layout
		foreach ($this->viewVars as $key => $val) {
			$$key = $val;
		}

		// Include the layout
		include ($this->currentLayout);

	}// End render


	/**
	 * Send either a success or error JSON response
	**/
	protected function jsonResponse($flag, $msj) {

		$respuesta = json_encode(array('flag'=>$flag, 'msj'=>$msj));

		if ($flag == 'error') {
			/* The WTF story says "exit()" should be used instead of "die()":
			 * If there is a die immediately after a header redirect,
			 * the code will always hit it, and your error logs will fill up with useless "errors"
			*/
			exit($respuesta);
		}
		else {
			echo($respuesta);
		}
	}


}//End class
