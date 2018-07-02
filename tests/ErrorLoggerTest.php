<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\BaseLib
 */

namespace Logics\Tests\Foundation\BaseLib;

use \Exception;
use \Logics\Foundation\BaseLib\ErrorLogger;
use \Logics\Tests\GetConnectionMySQL;
use \Logics\Tests\GetSetUpOperation;
use \Logics\Tests\PHPUnit_Extensions_Database_SQL_TestCase;
use \SimpleXMLElement;

/**
 * Test for ErrorLogger class
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2018-01-22 09:37:54 +0000 (Mon, 22 Jan 2018) $ $Revision: 365 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/baselib/tags/0.1.6/tests/ErrorLoggerTest.php $
 *
 * @runTestsInSeparateProcesses
 *
 * @donottranslate
 */

class ErrorLoggerTest extends PHPUnit_Extensions_Database_SQL_TestCase
    {

	const EXIT_CODE = -1234;

	use GetConnectionMySQL;

	use GetSetUpOperation;

	/**
	 * Instance of SQLdatabase
	 *
	 * @var SQLdatabase
	 */
	protected $db;

	/**
	 * Testing object
	 *
	 * @var mixed
	 */
	protected $object;

	/**
	 * Get test data set
	 *
	 * @return dataset
	 */

	public function getDataSet()
	    {
		return $this->createFlatXmlDataSet(__DIR__ . "/emptyErrorLoggerErrorHandler.xml");
	    } //end getDataSet()


	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */

	protected function setUp()
	    {
		$conn     = $this->getConnection();
		$this->db = $conn->getConnection();

		$this->db->exec(
		    "CREATE TABLE IF NOT EXISTS `Errors` (" .
		    "`id` char(32) NOT NULL," .
		    "`Type` char(64) NOT NULL," .
		    "`Message` longtext NOT NULL," .
		    "`File` text NOT NULL," .
		    "`Line` int(11) NOT NULL," .
		    "PRIMARY KEY (`id`)" .
		    ") ENGINE=InnoDB DEFAULT CHARSET=utf8;"
		);
		$this->db->exec(
		    "CREATE TABLE IF NOT EXISTS `Backtraces` (" .
		    "`id` char(32) NOT NULL," .
		    "`Backtrace` longblob NOT NULL," .
		    "PRIMARY KEY (`id`)" .
		    ") ENGINE=InnoDB DEFAULT CHARSET=utf8;"
		);
		$this->db->exec(
		    "CREATE TABLE IF NOT EXISTS `Contexts` (" .
		    "`id` char(32) NOT NULL," .
		    "`Context` longblob NOT NULL," .
		    "PRIMARY KEY (`id`)" .
		    ") ENGINE=InnoDB DEFAULT CHARSET=utf8;"
		);
		$this->db->exec(
		    "CREATE TABLE IF NOT EXISTS `Log` (" .
		    "`DateTime` datetime NOT NULL," .
		    "`Error` char(32) NOT NULL," .
		    "`Backtrace` char(32) NOT NULL," .
		    "`Context` char(32) NOT NULL," .
		    "KEY `Error` (`Error`(32))," .
		    "FOREIGN KEY (`Error`) REFERENCES `Errors`(`id`) ON DELETE CASCADE, " .
		    "FOREIGN KEY (`Backtrace`) REFERENCES `Backtraces`(`id`) ON DELETE RESTRICT, " .
		    "FOREIGN KEY (`Context`) REFERENCES `Contexts`(`id`) ON DELETE RESTRICT" .
		    ") ENGINE=InnoDB DEFAULT CHARSET=utf8;"
		);

		if (defined("LOGGER_FILE") === false)
		    {
			define("LOGGER_FILE", sys_get_temp_dir() . "/ErrorLoggerTest");
		    }

		parent::setUp();
	    } //end setUp()


	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 *
	 * @optionalconst LOGGER_FILE "/tmp/php-errorlog" File for PHP error log
	 */

	protected function tearDown()
	    {
		$this->db->exec("DROP TABLE IF EXISTS `Log`");
		$this->db->exec("DROP TABLE IF EXISTS `Errors`");
		$this->db->exec("DROP TABLE IF EXISTS `Backtraces`");
		$this->db->exec("DROP TABLE IF EXISTS `Contexts`");

		if (file_exists(LOGGER_FILE) === true)
		    {
			unlink(LOGGER_FILE);
		    }
	    } //end tearDown()


	/**
	 * Test errorHandler() with logging to database
	 *
	 * @return void
	 */

	public function testErrorHandlerOnDB()
	    {
		define("LOGGER_HOST", $GLOBALS["DB_HOST"]);
		define("LOGGER_DB", $GLOBALS["DB_DBNAME"]);
		define("LOGGER_USER", $GLOBALS["DB_USER"]);
		define("LOGGER_PASS", $GLOBALS["DB_PASSWD"]);

		$unserializeable = new SimpleXMLElement("<test/>");
		$this->object    = $unserializeable;

		$brokenunicode = substr("юникод", 1, 8);

		set_error_handler(array(ErrorLogger::CLASS, "errorHandler"));
		trigger_error("Testing errorHandler", E_USER_WARNING);
		restore_error_handler();

		$conn          = $this->getConnection();
		$queryTable    = $conn->createQueryTable("Log", "SELECT `Type`, `Message` FROM `Log`, `Errors` WHERE `Log`.`Error` = `Errors`.`id`");
		$expectedTable = $this->createFlatXmlDataSet(__DIR__ . "/expectedErrorLoggerErrorHandler.xml")->getTable("Log");
		$this->assertTablesEqual($expectedTable, $queryTable);

		$result = $this->db->exec("SELECT `Backtrace` FROM `Backtraces`");
		$this->assertEquals(1, $result->getNumRows());
		$row       = $result->getRow();
		$backtrace = unserialize($row["Backtrace"]);
		$this->assertTrue(is_array($backtrace));

		$result = $this->db->exec("SELECT `Context` FROM `Contexts`");
		$this->assertEquals(1, $result->getNumRows());
		$row     = $result->getRow();
		$context = unserialize($row["Context"]);
		$this->assertTrue(is_array($context));
		$this->assertEquals($brokenunicode, $context["brokenunicode"]);
	    } //end testErrorHandlerOnDB()


	/**
	 * Test errorHandler() with logging to non-existent database
	 *
	 * @return void
	 *
	 * @optionalconst LOGGER_FILE "/tmp/php-errorlog" File for PHP error log
	 */

	public function testErrorHandlerOnNonExistentDB()
	    {
		define("LOGGER_HOST", $GLOBALS["DB_HOST"]);
		define("LOGGER_DB", "nonexistent");
		define("LOGGER_USER", $GLOBALS["DB_USER"]);
		define("LOGGER_PASS", $GLOBALS["DB_PASSWD"]);

		set_error_handler(array(ErrorLogger::CLASS, "errorHandler"));
		trigger_error("Testing errorHandler", E_USER_WARNING);
		restore_error_handler();

		$this->assertFileExists(LOGGER_FILE);
		$this->assertContains("Testing errorHandler", file_get_contents(LOGGER_FILE));
	    } //end testErrorHandlerOnNonExistentDB()


	/**
	 * Test errorHandler() with database failure
	 *
	 * @return void
	 *
	 * @optionalconst LOGGER_FILE "/tmp/php-errorlog" File for PHP error log
	 */

	public function testErrorHandlerOnFailedDB()
	    {
		define("LOGGER_HOST", $GLOBALS["DB_HOST"]);
		define("LOGGER_DB", $GLOBALS["DB_DBNAME"]);
		define("LOGGER_USER", $GLOBALS["DB_USER"]);
		define("LOGGER_PASS", $GLOBALS["DB_PASSWD"]);

		$this->db->exec("DROP TABLE `Log`");
		$this->db->exec(
		    "CREATE TABLE `Log` (" .
		    "id int NOT NULL AUTO_INCREMENT, " .
		    "string text NOT NULL, " .
		    "PRIMARY KEY (`id`)" .
		    ") ENGINE=InnoDB DEFAULT CHARSET=utf8;"
		);

		set_error_handler(array(ErrorLogger::CLASS, "errorHandler"));
		trigger_error("Testing errorHandler", E_USER_WARNING);
		restore_error_handler();

		$this->assertFileExists(LOGGER_FILE);
		$this->assertContains("Testing errorHandler", file_get_contents(LOGGER_FILE));
	    } //end testErrorHandlerOnFailedDB()


	/**
	 * Test errorHandler()
	 *
	 * @return void
	 *
	 * @optionalconst LOGGER_FILE "/tmp/php-errorlog" File for PHP error log
	 */

	public function testErrorHandlerOnFile()
	    {
		set_error_handler(array(ErrorLogger::CLASS, "errorHandler"));
		trigger_error("Testing errorHandler", E_USER_WARNING);
		restore_error_handler();

		$this->assertFileExists(LOGGER_FILE);
		$this->assertContains("Testing errorHandler", file_get_contents(LOGGER_FILE));
	    } //end testErrorHandlerOnFile()


	/**
	 * Test exceptionHandler() with logging to database
	 *
	 * @return void
	 */

	public function testExceptionHandlerOnDB()
	    {
		define("LOGGER_HOST", $GLOBALS["DB_HOST"]);
		define("LOGGER_DB", $GLOBALS["DB_DBNAME"]);
		define("LOGGER_USER", $GLOBALS["DB_USER"]);
		define("LOGGER_PASS", $GLOBALS["DB_PASSWD"]);

		$exception = new Exception("Testing exceptionHandler", 0);
		ErrorLogger::exceptionHandler($exception);

		$conn          = $this->getConnection();
		$queryTable    = $conn->createQueryTable("Log", "SELECT `Type`, `Message` FROM `Log`, `Errors` WHERE `Log`.`Error` = `Errors`.`id`");
		$expectedTable = $this->createFlatXmlDataSet(__DIR__ . "/expectedErrorLoggerExceptionHandler.xml")->getTable("Log");
		$this->assertTablesEqual($expectedTable, $queryTable);
	    } //end testExceptionHandlerOnDB()


	/**
	 * Test shutdownHandler() with logging to database
	 *
	 * @return void
	 */

	public function testShutdownHandlerOnDB()
	    {
		define("LOGGER_HOST", $GLOBALS["DB_HOST"]);
		define("LOGGER_DB", $GLOBALS["DB_DBNAME"]);
		define("LOGGER_USER", $GLOBALS["DB_USER"]);
		define("LOGGER_PASS", $GLOBALS["DB_PASSWD"]);

		set_error_handler(null);
		error_reporting(0);
		trigger_error("Testing shutdownHandler", E_USER_WARNING);
		restore_error_handler();

		ErrorLogger::shutdownHandler();

		$conn          = $this->getConnection();
		$queryTable    = $conn->createQueryTable("Log", "SELECT `Type`, `Message` FROM `Log`, `Errors` WHERE `Log`.`Error` = `Errors`.`id`");
		$expectedTable = $this->createFlatXmlDataSet(__DIR__ . "/expectedErrorLoggerShutdownHandler.xml")->getTable("Log");
		$this->assertTablesEqual($expectedTable, $queryTable);
	    } //end testShutdownHandlerOnDB()


	/**
	 * Test error handler with fatal error
	 *
	 * @return void
	 *
	 * @requires extension test_helpers
	 *
	 * @throws Exception If exit() is encountered
	 */

	public function testErrorHandlerWithFatalError()
	    {
		set_exit_overload(
		function()
		    {
			throw new Exception("exit()", self::EXIT_CODE);
		    }
		);

		$exited = false;
		try
		    {
			ErrorLogger::errorHandler(E_ERROR, "Fatal error", __FILE__, 0, array("supressLogging" => "Fatal error"));
		    }
		catch (Exception $e)
		    {
			if ($e->getCode() === self::EXIT_CODE)
			    {
				$exited = true;
			    }
		    } //end try
		unset_exit_overload();

		$this->assertTrue($exited);
	    } //end testErrorHandlerWithFatalError()


    } //end class

?>
