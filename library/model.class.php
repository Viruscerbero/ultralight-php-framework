<?php
/** @author Viruscerbero
 ** The idea here (and the one suggested by MVC in general) is putting all the business logic in the model.
 ** The model is the one that holds the state.
 ** The interface is determined by the three functions: registerObserver, removeObserver and notifyObservers
**/

require_once('dataBaseAccess.class.php');

abstract class Model
{
	protected $dbHandle;

	protected $registeredObservers = array();

	protected $status;

	protected $message;



	/**
	 * @Ctor
	**/
	protected function __construct() {
		$dBObj = new DataBaseAccess();

		$this->dbHandle = $dBObj->getDBHandle();

	}


	/**
	 * This function is part of the MVC pattern
	**/
	public function registerObserver($observer) {
		if (!in_array($observer, $this->registeredObservers)) {
			$this->registeredObservers[] = $observer;
		}

	}


	/**
	 * This function is part of the MVC pattern
	**/
	public function removeObserver($observer) {
		foreach ($this->registeredObservers as $key => $val) {
			if ($val == $observer) {
				unset($this->registeredObservers[$key]);
			}
		}

	}


	/**
	 * This function is part of the MVC pattern
	**/
	public function notifyObservers() {
		foreach ($this->registeredObservers as $observer) {
			$observer->update();
		}

	}


	/**
	 * Function for reading the HTML of a node
	**/
	protected function innerHTML(DOMNode $element) {
		$innerHTML = "";

		$children = $element->childNodes;

		foreach ($children as $child) {
			$innerHTML .= $element->ownerDocument->saveHTML($child);
		}

		return $innerHTML;

	}


	/**
	 * PDO prepare a query
	**/
	protected function prepareQuery($query) {
		/* Prepare the query */
		try {
			$statement = $this->dbHandle->prepare($query);

			return $statement;
		}
		catch(PDOException $e) {
			// Initialize an error string with the code that will be shown in the debug version
			$cod_error = '';

			if (DEVELOPMENT_ENVIRONMENT) {
				$cod_error = ":<br/>{$e->getMessage()}";
			}

			throw new Exception('Query preparation failed. The query cannot be prepared. ' . $cod_error);
		}

	}


	/**
	 * PDO executes a prepared query
	**/
	protected function executeStatement($statement, array $ar_params) {
		try {
			/*
			PDOStatement::rowCount() returns the number of rows affected by the last DELETE, INSERT or 
			UPDATE statement excuted by the PDOStatement object.

			Some databases may return the number of rows returned by the SELECT statement. However, this behaviour is not guaranteed for all databases and should not be relied upon when building portable applications.
			*/

			$statement->execute($ar_params);
		}
		catch(PDOException $e) {
			// Initialize an error string with the code that will be shown in the debug version
			$cod_error = '';

			if (DEVELOPMENT_ENVIRONMENT) {
				$cod_error = ":<br/>{$e->getMessage()}";
			}

			throw new Exception('Query execution failed. The query cannot be executed. ' . $cod_error);
		}

	}


	/**
	 * Get all the fields from a table
	**/
	protected function getTableFields($tableName) {
		$statement = $this->prepareQuery(
			"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '$tableName'"
		);

		//$statement = $this->prepareQuery("SHOW COLUMNS FROM {$this->tableName}");

		$statement->setFetchMode(PDO::FETCH_COLUMN, 0);

		// Execute the query
		$this->executeStatement($statement, array());

		$ar_columnas = $statement->fetchAll();

		$statement = null;

		// Add the table name as a prefix to each element
		$ar_columnas = preg_replace('/^/', "$tableName.", $ar_columnas);

		return $ar_columnas;

	}


	protected function updateTable($table, $id, $column, $value) {
		$statement = $this->prepareQuery("UPDATE $table SET $column=? WHERE id=?");

		$this->executeStatement($statement, array($value, $id));

		$rowCount = $statement->rowCount();

		$statement = null;

		$this->{$column} = $value;

		return $rowCount;

	}


	protected function updateDataInTable($table, $id, array $userData) {
		// The array of columns that will be updated
		$ar_setStatement = array();

		// The array of values that will update the columns
		$ar_values = array();

		foreach ($userData as $column=>$value) {
			if (is_null($value) || is_numeric($column)) {
				continue;
			}
			else {
				$this->{$column} = $value;

				$ar_setStatement[] = "$column=?";

				$ar_values[] = $this->{$column};
			}
		}

		// Add the id to the array of values
		$ar_values[] = $id;

		$setStatement = implode(', ', $ar_setStatement);

		$statement = $this->prepareQuery("UPDATE $table SET $setStatement WHERE id=?");

		$this->executeStatement($statement, $ar_values);

		$rowCount = $statement->rowCount();

		$statement = null;

		return $rowCount;

	}


	protected function rejectIfNotPOST() {
		if (strtolower($_SERVER['REQUEST_METHOD']) != 'post') {
			// End script
			header('HTTP/1.0 401 No Autorizado');

			include($_SERVER['DOCUMENT_ROOT'] . D_S . 'errorPages' . D_S . 'error401' . D_S . 'error401.html');

			exit;
		}

	}


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


	protected function setResponse($status, $message) {
		$this->status = $status;

		$this->message = $message;
	}


	public function getStatus() {
		$status = new stdClass();

		$status->status = $this->status;

		$status->message = $this->message;

		//header($_SERVER['SERVER_PROTOCOL'] . $status->status);

		return $status;
	}


	/**
	 * @Dtor
	**/
	public function __destruct() {
		if (!is_null($this->dbHandle)) {//PDO
			$this->dbHandle = null;
		}

	}


}// End class

?>
