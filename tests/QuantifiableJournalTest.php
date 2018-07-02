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
use \Logics\Foundation\BaseLib\QuantifiableJournal;
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
 * @version   SVN: $Date: 2018-01-22 09:37:54 +0000 (Mon, 22 Jan 2018) $ $Revision: 365 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/baselib/tags/0.1.6/tests/QuantifiableJournalTest.php $
 *
 * @runTestsInSeparateProcesses
 *
 * @donottranslate
 */

class QuantifiableJournalTest extends PHPUnit_Extensions_Database_SQL_TestCase
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
				    "available",
				    "reserved",
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
		    "`amount` decimal(64,18) NOT NULL," .
		    "`record` longtext NOT NULL," .
		    "`startingBalance` boolean NOT NULL," .
		    "INDEX `client` (`client`(40))," .
		    "INDEX `date` (`date`)," .
		    "INDEX `typeOfRecord` (`typeOfRecord`)" .
		    ") ENGINE=InnoDB DEFAULT CHARSET=utf8;"
		);

		$this->db->execUntilSuccessful(
		    "CREATE TABLE IF NOT EXISTS `" . $this->_logname . "fulllog` (" .
		    "`id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY," .
		    "`client` text NOT NULL," .
		    "`date` datetime NOT NULL," .
		    "`typeOfRecord` ENUM" . $typeOfRecordsString . " NOT NULL," .
		    "`amount` decimal(64,18) NOT NULL," .
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
		return $this->createMySQLXmlDataSet(__DIR__ . "/dataset/quantifiablejournal.xml");
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
		$this->db->exec("DROP TABLE IF EXISTS `testfulllog`");
	    } //end tearDown()


	/**
	 * Test for recording
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_INVALID_RECORD_TYPE
	 */

	public function testShouldRecordDetailsAndAnAmountForClientAndRecordTypeInJournal()
	    {
		defined("EXCEPTION_INVALID_RECORD_TYPE") || define("EXCEPTION_INVALID_RECORD_TYPE", 1);

		$clientName = "testClientNew";

		$journal = new QuantifiableJournal($this->db, $this->_logname, $this->_typesOfRecords);
		$journal->record($clientName, "available", 5, "test record");

		$result = $this->db->exec("SELECT * FROM `" . $this->_logname . "log` WHERE client =" . $this->db->sqlText($clientName));
		$this->assertEquals(1, $result->getNumRows());

		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_INVALID_RECORD_TYPE);
		$journal->record($clientName, "test", 5, "test record");
	    } //end testShouldRecordDetailsAndAnAmountForClientAndRecordTypeInJournal()


	/**
	 * Test for balance
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_INVALID_RECORD_TYPE
	 */

	public function testShouldReturnBalanceForClientAndRecordType()
	    {
		defined("EXCEPTION_INVALID_RECORD_TYPE") || define("EXCEPTION_INVALID_RECORD_TYPE", 1);

		$journal = new QuantifiableJournal($this->db, $this->_logname, $this->_typesOfRecords);

		$clientName   = "testClientNew";
		$typeOfRecord = "available";

		$emptyBalance = $journal->getCompoundBalance($clientName, $typeOfRecord);

		$this->assertSame(0.00, $emptyBalance);

		$clientName      = "testClient";
		$compoundBalance = $journal->getCompoundBalance($clientName, $typeOfRecord);

		$this->assertEquals(3, $compoundBalance);

		$startDate    = new DateTime("2014-06-21 00:00:00");
		$clientName   = "testClientWithStartingBalance";
		$typeOfRecord = "available";

		$compoundBalance = $journal->getCompoundBalance($clientName, $typeOfRecord, $startDate);
		$this->assertEquals(20, $compoundBalance);

		$startDate       = new DateTime("now");
		$compoundBalance = $journal->getCompoundBalance($clientName, $typeOfRecord, $startDate);
		$this->assertEquals(0, $compoundBalance);

		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_INVALID_RECORD_TYPE);
		$journal->getCompoundBalance("testClientWithStartingBalance", "badTypeOfRecord");
	    } //end testShouldReturnBalanceForClientAndRecordType()


	/**
	 * Test for balance
	 *
	 * @return void
	 */

	public function testShouldReturnBalanceForClientAndRecordTypeOverParticularPeriodOfTime()
	    {
		$journal   = new QuantifiableJournal($this->db, $this->_logname, $this->_typesOfRecords);
		$startDate = new DateTime("2015-06-21 00:00:00");

		$clientName   = "testClient3";
		$typeOfRecord = "reserved";

		$compoundBalance = $journal->getCompoundBalance($clientName, $typeOfRecord, $startDate);
		$this->assertEquals(5, $compoundBalance);
	    } //end testShouldReturnBalanceForClientAndRecordTypeOverParticularPeriodOfTime()


	/**
	 * Test for credit balance
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_INVALID_RECORD_TYPE
	 */

	public function testShouldReturnCreditBalanceForClientAndRecordTypeOverParticularPeriodOfTime()
	    {
		defined("EXCEPTION_INVALID_RECORD_TYPE") || define("EXCEPTION_INVALID_RECORD_TYPE", 1);

		$journal   = new QuantifiableJournal($this->db, $this->_logname, $this->_typesOfRecords);
		$startDate = new DateTime("2015-06-21 00:00:00");

		$clientName   = "testClient5";
		$typeOfRecord = "available";

		$creditBalance = $journal->getTotalCreditAmount($clientName, $typeOfRecord, $startDate);
		$this->assertEquals(2, $creditBalance);

		$creditBalance = $journal->getTotalCreditAmount($clientName, $typeOfRecord);
		$this->assertEquals(2, $creditBalance);

		$startDate = new DateTime("2014-11-01 00:00:00");

		$creditBalance = $journal->getTotalCreditAmount("testClientWithStartingBalance", $typeOfRecord, $startDate);
		$this->assertEquals(25, $creditBalance);

		$startDate     = new DateTime("now");
		$creditBalance = $journal->getTotalCreditAmount("testClientWithStartingBalance", $typeOfRecord, $startDate);
		$this->assertEquals(0, $creditBalance);

		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_INVALID_RECORD_TYPE);
		$creditBalance = $journal->getTotalCreditAmount("testClientWithStartingBalance", "badTypeOfRecord");
	    } //end testShouldReturnCreditBalanceForClientAndRecordTypeOverParticularPeriodOfTime()


	/**
	 * Test for debit balance
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_INVALID_RECORD_TYPE
	 */

	public function testShouldReturnDebitBalanceForClientAndRecordTypeOverParticularPeriodOfTime()
	    {
		defined("EXCEPTION_INVALID_RECORD_TYPE") || define("EXCEPTION_INVALID_RECORD_TYPE", 1);

		$journal   = new QuantifiableJournal($this->db, $this->_logname, $this->_typesOfRecords);
		$startDate = new DateTime("2015-06-21 00:00:00");

		$clientName   = "testClient5";
		$typeOfRecord = "available";

		$debitBalance = $journal->getTotalDebitAmount($clientName, $typeOfRecord, $startDate);
		$this->assertEquals(1, $debitBalance);

		$debitBalance = $journal->getTotalDebitAmount($clientName, $typeOfRecord, $startDate);
		$this->assertEquals(1, $debitBalance);
		$startDate = new DateTime("2014-11-01 00:00:00");

		$debitBalance = $journal->getTotalDebitAmount("testClientWithStartingBalance", $typeOfRecord, $startDate);
		$this->assertEquals(5, $debitBalance);

		$debitBalance = $journal->getTotalDebitAmount("testClientWithStartingBalance", $typeOfRecord);
		$this->assertEquals(0, $debitBalance);

		$startDate    = new DateTime("now");
		$debitBalance = $journal->getTotalDebitAmount("testClientWithStartingBalance", $typeOfRecord, $startDate);
		$this->assertEquals(0, $debitBalance);

		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_INVALID_RECORD_TYPE);
		$debitBalance = $journal->getTotalDebitAmount("testClientWithStartingBalance", "badTypeOfRecord");
	    } //end testShouldReturnDebitBalanceForClientAndRecordTypeOverParticularPeriodOfTime()


	/**
	 * Test for rertieving all records
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

		$timeZone  = new DateTimeZone(date_default_timezone_get());
		$journal   = new QuantifiableJournal($this->db, $this->_logname, $this->_typesOfRecords);
		$startDate = new DateTime("2015-06-21 00:00:00");

		$clientName = "testClientNew";

		try
		    {
			$journal->getRecords($clientName, $startDate);
			$this->fail("Request is not expected in this stage");
		    }
		catch (Exception $e)
		    {
			$this->assertEquals(EXCEPTION_NO_RECORDS_SELECTED, $e->getCode());
		    }

		$clientName = "testClient";
		$records    = $journal->getRecords($clientName, $startDate);
		$this->assertEquals(3, count($records));

		$startDate = new DateTime("2015-05-21 00:00:00");
		$records   = $journal->getRecords($clientName, $startDate);
		$this->assertEquals(3, count($records));

		$startDate = new DateTime("2015-05-21 00:00:00");
		$endDate   = new DateTime("2015-08-21 00:00:00");
		$records   = $journal->getRecords($clientName, $startDate, $endDate);
		$this->assertEquals(1, count($records));

		$clientName   = "testClient4";
		$startDate    = new DateTime("2015-08-17 00:00:00");
		$endDate      = new DateTime("now", $timeZone);
		$typeOfRecord = "reserved";
		$records      = $journal->getRecords($clientName, $startDate, $endDate, $typeOfRecord);
		$this->assertEquals(1, count($records));

		$records = $journal->getRecords($clientName, false, $endDate, $typeOfRecord);
		$this->assertEquals(1, count($records));

		$records = $journal->getRecords($clientName);
		$this->assertEquals(1, count($records));

		try
		    {
			$journal->getRecords("testClientWithStartingBalance", false, false, "badTypeOfRecord");
			$this->fail("Request is not expected in this stage");
		    }
		catch (Exception $e)
		    {
			$this->assertEquals(EXCEPTION_INVALID_RECORD_TYPE, $e->getCode());
		    }
	    } //end testShouldRetrieveAllRecordsForClientAndRecordTypeForAParticularPeriodOfTime()


	/**
	 * Test for rertieving fixed count of records
	 *
	 * @return void
	 */

	public function testShouldRetrieveFixedCountOfRecordsForClientAndRecordType()
	    {
		$journal      = new QuantifiableJournal($this->db, $this->_logname, $this->_typesOfRecords);
		$startDate    = new DateTime("2014-06-21 00:00:00");
		$client       = "testClient";
		$typeOfRecord = "available";

		$records = $journal->getRecords($client, $startDate, false, $typeOfRecord, 2);
		$this->assertEquals(2, count($records));
	    } //end testShouldRetrieveFixedCountOfRecordsForClientAndRecordType()


	/**
	 * Test for transfer
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_USELESS_OPERATION
	 * @exceptioncode EXCEPTION_INVALID_RECORD_TYPE
	 */

	public function testShouldTransferAnAmountFromOneTypeToAnother()
	    {
		defined("EXCEPTION_INVALID_RECORD_TYPE") || define("EXCEPTION_INVALID_RECORD_TYPE", 1);
		defined("EXCEPTION_USELESS_OPERATION") || define("EXCEPTION_USELESS_OPERATION", 3);

		$clientName              = "testClient4";
		$sourceTypeOfRecord      = "reserved";
		$destinationTypeOfRecord = "available";
		$amount                  = 2;

		$journal = new QuantifiableJournal($this->db, $this->_logname, $this->_typesOfRecords);

		$availableCompoundBalance = $journal->getCompoundBalance($clientName, $sourceTypeOfRecord);
		$reservedCompoundBalance  = $journal->getCompoundBalance($clientName, $destinationTypeOfRecord);

		$journal->transferBalance($clientName, $sourceTypeOfRecord, $destinationTypeOfRecord, $amount);

		$newAvailableCompoundBalance = $journal->getCompoundBalance($clientName, $sourceTypeOfRecord);
		$newReservedCompoundBalance  = $journal->getCompoundBalance($clientName, $destinationTypeOfRecord);

		$differenceOfAvailableBalance = ($availableCompoundBalance - $newAvailableCompoundBalance);
		$differenceOfReservedBalance  = ($reservedCompoundBalance - $newReservedCompoundBalance);

		$this->assertTrue($differenceOfAvailableBalance === (-$differenceOfReservedBalance));

		try
		    {
			$journal->transferBalance($clientName, $sourceTypeOfRecord, $destinationTypeOfRecord, 0);
			$this->fail("Request is not expected in this stage");
		    }
		catch (Exception $e)
		    {
			$this->assertEquals(EXCEPTION_USELESS_OPERATION, $e->getCode());
		    }

		try
		    {
			$journal->transferBalance($clientName, "badRecordType", $destinationTypeOfRecord, 0);
			$this->fail("Request is not expected in this stage");
		    }
		catch (Exception $e)
		    {
			$this->assertEquals(EXCEPTION_INVALID_RECORD_TYPE, $e->getCode());
		    }
	    } //end testShouldTransferAnAmountFromOneTypeToAnother()


	/**
	 * Test for speed perfomance
	 *
	 * @return void
	 */

	public function testHasFastSpeedOfPerfomanceOfJournalClass()
	    {
		$clientName   = "speedTestClient";
		$journal      = new QuantifiableJournal($this->db, $this->_logname, $this->_typesOfRecords);
		$typeOfRecord = "available";

		for ($i = 1; $i <= 10000; $i++)
		    {
			$journal->record($clientName, "available", $i, "test record");
		    }

		$commence = microtime(true);

		for ($i = 1; $i <= 100; $i++)
		    {
			$journal->getCompoundBalance($clientName, $typeOfRecord);
		    }

		$complete = microtime(true);
		$exectime = ($complete - $commence);
		$this->assertTrue($exectime < 10, "Execution time is excessive: " . $exectime . " seconds");
	    } //end testHasFastSpeedOfPerfomanceOfJournalClass()


	/**
	 * Test for verify integrity
	 *
	 * @return void
	 */

	public function testIsAbleToVerifyIntegrityOfJournalTables()
	    {
		$clientName   = "testClient";
		$typeOfRecord = "available";

		$journal = new QuantifiableJournal($this->db, $this->_logname, $this->_typesOfRecords);

		$availableCompoundBalance = $journal->getCompoundBalance($clientName, $typeOfRecord);

		$result = $this->db->exec(
		    "SELECT SUM(`amount`) AS `total` FROM `" . $this->_logname . "fulllog` " .
		    "WHERE `client` =  " . $this->db->sqlText($clientName) .
		    " AND `typeOfRecord` = " . $this->db->sqlText($typeOfRecord)
		);

		if ($result->getNumRows() !== 0)
		    {
			$row = $result->getRow();
			$this->assertTrue($row["total"] === $availableCompoundBalance, "False");
		    }
		else
		    {
			$this->fail("Result should contain non-zero number of rows");
		    }
	    } //end testIsAbleToVerifyIntegrityOfJournalTables()


    } //end class

?>
