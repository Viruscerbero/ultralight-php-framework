<?php
/** @author Viruscerbero
**  Class for accessing the database
**/

final class DataBaseAccess
{
	private $servername;
	private $username;
	private $password;
	private $dbName;
	protected $dbHandle;


	/**
	 * @Ctor
	**/
	public function __construct() {
		$this->servername = DATABASE_SERVER_NAME;
		$this->username = DATABASE_USER;
		$this->password = PASSWORD;
		$this->dbName = DATABASE_NAME;

		// Connect to the database using PDO
		$this->connectPDO();
	}


	/**
	 * Get a handler for the database
	**/
	public function getDBHandle() {
		return $this->dbHandle;
	}


	/**
	 * PDO
	**/
	private function connectPDO() {
		try {
			// $options = array(PDO::MYSQL_ATTR_INIT_COMMAND =>  "SET NAMES 'utf8'");
			// Or Replace MYSQL_ATTR_INIT_COMMAND with 1002
			$this->dbHandle = new PDO(
				"mysql:host={$this->servername}; dbname={$this->dbName}", $this->username, $this->password
			);

			// Set the PDO error mode to exception
			$this->dbHandle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		catch(PDOException $e) {
			// Initialize an error string with the code that will be shown in the debug version
			$cod_error = '';

			if (DEVELOPMENT_ENVIRONMENT) {
				$cod_error = ":<br/>{$e->getMessage()}";
			}

			$this->jsonResponse('error', 'The connection to the database has failed. ' . $cod_error);
		}

		// Change character set to utf8. By default is latin1.
		// This is important, that is why we'll close the connection in case of any issue
		try {
			$this->dbHandle->exec('SET NAMES utf8');
		}
		catch(PDOException $e) {
			// Initialize an error string with the code that will be shown in the debug version
			$cod_error = '';

			if (DEVELOPMENT_ENVIRONMENT) {
				$cod_error = ":<br/>{$e->getMessage()}";
			}

			$this->jsonResponse(
				'error',
				'The utf-8 setting on the database has failed. ' . $cod_error
			);
		}

		// Make sure that the time zone is correct.
		// This is not critical, so the script is not terminated in case of any issue
		$this->dbHandle->exec("SET time_zone = '" . TIME_ZONE . "'");

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


	/**
	 * @Dtor
	**/
	public function __destruct() {
		// PDO
		if (!is_null($this->dbHandle)) {
			$this->dbHandle = null;
		}

	}


}// End class

?>
