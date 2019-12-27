<?php
/** @author Viruscerbero
**  The base controller is a simple abstract class that defines the structure of all controllers.
**
**  The job of a controller is to handle data that the user inputs or submits, and update the Model accordingly.
**  The Controller’s life blood is the user; without user interactions, the Controller has no purpose.
**  The Controller is the only part of the pattern the user should be interacting with.
**  The Controller never gives data to the View. The controller doesn't interact with the View at all.
**  The Controller can be summed up simply as a collector of information, which then passes it on to the Model
**  and does not contain any logic other than that needed to collect the input.
**  The Controller is also connected to a single View and to a single Model, making it a one way data flow system, 
**  with handshakes and signoffs at each point of data exchange.
**  Less controller code means more reusable code.
**/
abstract class Controller
{
  protected $vars = array();

  protected $model;

  /* The controller has a reference to the view for when there is no need for having a model but it is neccesary  
   * to operate on the view. But in all other cases, it is only the Model the one that talks to the view, not the * controller */
  protected $view;


 /**
  * @Ctor
 **/
  public function __construct(Model $model = null, View $view) {
    $this->model = $model;
	
    $this->view = $view;

  }


}// End class


?>
