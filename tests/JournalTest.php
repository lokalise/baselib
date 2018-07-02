<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\BaseLib
 */

namespace Logics\Tests\Foundation\BaseLib;

use \DateTime;
use \DateTimeZone;
use \Exception;
use \Logics\Foundation\BaseLib\Journal;
use \Logics\Foundation\SQL\MySQLdatabase;
use \Logics\Tests\DefaultDataSet;
use \Logics\Tests\GetConnectionMySQL;
use \Logics\Tests\GetSetUpOperation;
use \Logics\Tests\PHPUnit_Extensions_Database_SQL_TestCase;

/**
 * Journal class
 *
 * @author    Ekaterina Bizimova <kate@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2017-01-08 18:00:18 +0000 (Sun, 08 Jan 2017) $ $Revision: 354 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/baselib/tags/0.1.6/tests/JournalTest.php $
 *
 * @runTestsInSeparateProcesses
 *
 * @donottranslate
 */

class JournalTest extends PHPUnit_Extensions_Database_SQL_TestCase
    {

	use GetConnectionMySQL;

	use GetSetUpOperation;

	use DefaultDataSet;

	/**
	 * Instance of SQLdatabase
	 *
	 * @var SQLdatabase
	 */
	protected $db;

	/**
	 * Log name
	 *
	 * @var string
	 */
	private $_logname = "test";

	/**
	 * Types of records
	 *
	 * @var array
	 */
	private $_typesOfRecords = array(
				    "testTypeOfRecord1",
				    "testTypeOfRecord2",
				   );

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */

	protected function setUp()
	    {
		$this->db = new MySQLdatabase($GLOBALS["DB_HOST"], $GLOBALS["DB_DBNAME"], $GLOBALS["DB_USER"], $GLOBALS["DB_PASSWD"]);
		$this->db->exec("SET NAMES 'UTF8'");

		$typeOfRecordsString = "(\"" . implode("\",\"", $this->_typesOfRecords) . "\")";

		$this->db->execUntilSuccessful(
		    "CREATE TABLE IF NOT EXISTS `" . $this->_logname . "log` (" .
		    "`id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY," .
		    "`client` text NOT NULL," .
		    "`date` datetime NOT NULL," .
		    "`typeOfRecord` ENUM" . $typeOfRecordsString . " NOT NULL," .
		    "`record` longtext NOT NULL," .
		    "INDEX `client` (`client`(40))," .
		    "INDEX `date` (`date`)," .
		    "INDEX `typeOfRecord` (`typeOfRecord`)" .
		    ") ENGINE=InnoDB DEFAULT CHARSET=utf8;"
		);
		parent::setUp();
	    } //end setUp()


	/**
	 * Get test data set
	 *
	 * @return dataset
	 */

	public function getDataSet()
	    {
		return $this->createMySQLXmlDataSet(__DIR__ . "/dataset/journal.xml");
	    } //end getDataSet()


	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */

	protected function tearDown()
	    {
		$this->db->exec("DROP TABLE IF EXISTS `testlog`");
	    } //end tearDown()


	/**
	 * Test for recording
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_INVALID_RECORD_TYPE
	 */

	public function testShouldRecordDateAndDetailsForClientAndRecordTypeInJournal()
	    {
		defined("EXCEPTION_INVALID_RECORD_TYPE") || define("EXCEPTION_INVALID_RECORD_TYPE", 1);

		$clientName = "testClientNew";

		$journal = new Journal($this->db, $this->_logname, $this->_typesOfRecords);

		$journal->record($clientName, "testTypeOfRecord1", "test record");

		$result = $this->db->exec("SELECT * FROM `" . $this->_logname . "log` WHERE client =" . $this->db->sqlText($clientName));
		$this->assertEquals(1, $result->getNumRows());

		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_INVALID_RECORD_TYPE);
		$journal->record($clientName, "badrecordtype", "test record");
	    } //end testShouldRecordDateAndDetailsForClientAndRecordTypeInJournal()


	/**
	 * Test for receiving
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_NO_RECORDS_SELECTED
	 * @exceptioncode EXCEPTION_INVALID_RECORD_TYPE
	 */

	public function testShouldRetrieveAllRecordsForClientAndRecordTypeForAParticularPeriodOfTime()
	    {
		defined("EXCEPTION_INVALID_RECORD_TYPE") || define("EXCEPTION_INVALID_RECORD_TYPE", 1);
		defined("EXCEPTION_NO_RECORDS_SELECTED") || define("EXCEPTION_NO_RECORDS_SELECTED", 2);

		$journal   = new Journal($this->db, $this->_logname, $this->_typesOfRecords);
		$startDate = new DateTime("2015-06-21 00:00:00");

		$clientName   = "testClientNew";
		$typeOfRecord = "testTypeOfRecord2";

		try
		    {
			$journal->getRecords($clientName, $startDate);
			$this->fail("Exception expected at this stage");
		    }
		catch (Exception $e)
		    {
			$this->assertEquals(EXCEPTION_NO_RECORDS_SELECTED, $e->getCode());
		    }

		$clientName = "testClient";
		$records    = $journal->getRecords($clientName, $startDate);
		$this->assertEquals(2, count($records));

		$startDate = new DateTime("2015-05-21 00:00:00");
		$records   = $journal->getRecords($clientName, $startDate);
		$this->assertEquals(2, count($records));

		$records = $journal->getRecords($clientName);
		$this->assertEquals(2, count($records));

		try
		    {
			$records = $journal->getRecords($clientName, false, false, $typeOfRecord);
			$this->fail("Exception expected at this stage");
		    }
		catch (Exception $e)
		    {
			$this->assertEquals(EXCEPTION_NO_RECORDS_SELECTED, $e->getCode());
		    }

		try
		    {
			$journal->getRecords($clientName, false, false, "badrecordtype");
			$this->fail("Exception expected at this stage");
		    }
		catch (Exception $e)
		    {
			$this->assertEquals(EXCEPTION_INVALID_RECORD_TYPE, $e->getCode());
		    }

		$startDate = new DateTime("2015-05-21 00:00:00");
		$endDate   = new DateTime("2015-08-21 00:00:00");
		$records   = $journal->getRecords($clientName, $startDate, $endDate);
		$this->assertEquals(1, count($records));

		$clientName = "testClient4";
		$startDate  = new DateTime("2015-08-17 00:00:00");
		$timeZone   = new DateTimeZone(date_default_timezone_get());
		$endDate    = new DateTime("now", $timeZone);
		$records    = $journal->getRecords($clientName, $startDate, $endDate, $typeOfRecord);
		$this->assertEquals(2, count($records));

		$records = $journal->getRecords($clientName, $startDate, false, $typeOfRecord, 1);
		$this->assertEquals(1, count($records));
	    } //end testShouldRetrieveAllRecordsForClientAndRecordTypeForAParticularPeriodOfTime()


    } //end class

?>
