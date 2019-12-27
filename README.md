# Ultralight PHP Framework

This MVC framework provides the minimal functionality needed to build an object oriented PHP website.

<br>
<br>

## The Framework

Everything starts with the .htaccess file routing all the traffic through the index which in turn loads the library and the bootstrap code.

Essentially all what the framework does is routing the request through the index, splitting the string in four parts: model/view/controller-action/arguments, and then trying to instantiate the model, the view and the controller objects from the names in the request.

The bootstrap file has some functions like enabling the error reporting, declaring some directories, and some code for automatic class loading, but its main function is instatiating the Router and getting it to dispatch the request.

The Router constructs the Model, the View and the Controller, and then calls the action inside the Controller and pass it the arguments. All the information that the Router needs comes from `$_SERVER['REQUEST_URI']`. In order to pass a stream, like if we are dealing with a JSON POST from JavaScript, we need to use `file_get_contents('php://input')`. For example, we could use something like: ```$post = json_decode(file_get_contents('php://input'), false); (true for array, false for object)```.

The models, views and controllers must extend the respective base class. Actions should be written as actionNameAction() inside the controller that owns them.

<br>
<br>

## Features

- Router
- A Model base class
- A View base class
- A Controller base class
- A DataBaseAccess class
- PDO to handle the access to the database

<br>
<br>

# Architecture

The soul of the framework is the MVC pattern, GoF's style.

The model is the one that handles the logic; it is the object with the responsibility of changing the status of the views. The request goes through the controller to the model, and the model feeds the views. 

If for some reason a model object is not neccesary for a given request, then the controller is the one responsible for handling the action that is being requested. This is an easy solution when there is no need for a model.

In this framework, the controller is never a mediator, so it never passes the response from the model to the view, ever. It is the responsibility of the model to let the views know that they need to update. The only purpose of the controller is calling the appropiate function on the model by sending the request from the user to the model.
