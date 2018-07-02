<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\BaseLib
 */

namespace Logics\Foundation\BaseLib;

use \DateTime;
use \DateTimeZone;
use \Exception;
use \Logics\Foundation\SQL\SQLdatabase;

/**
 * Journal class
 *
 * @author    Ekaterina Bizimova <kate@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2018-01-22 09:37:54 +0000 (Mon, 22 Jan 2018) $ $Revision: 365 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/baselib/tags/0.1.6/src/QuantifiableJournal.php $
 */

class QuantifiableJournal extends Journal
    {

	/**
	 * Database to work with
	 *
	 * @var SQLdatabase
	 */
	private $_db;

	/**
	 * Prefix for table in use
	 *
	 * @var string
	 */
	private $_prefix;

	/**
	 * Type of records enumerator
	 *
	 * @var array
	 */
	private $_typesOfRecords;

	/**
	 * Time zone
	 *
	 * @var mixed
	 */
	private $_timeZone;

	/**
	 * Instantiate this class
	 *
	 * @param SQLdatabase $db          Database which contains journal tables
	 * @param string      $name        Prefix name
	 * @param array       $enumerators Array of record types
	 *
	 * @return void
	 *
	 * @untranslatable log` (
	 * @untranslatable fulllog` (
	 * @untranslatable NOT NULL,
	 * @untranslatable -3 month
	 * @untranslatable log`
	 * @untranslatable credits
	 * @untranslatable debits
	 * @untranslatable Y-m-d H:i:s
	 * @untranslatable starting
	 * @untranslatable TRUE)
	 * @untranslatable sqlText
	 */

	public function __construct(SQLdatabase $db, $name, array $enumerators)
	    {
		$this->_db             = $db;
		$this->_prefix         = $name;
		$this->_typesOfRecords = $enumerators;
		$this->_timeZone       = new DateTimeZone(date_default_timezone_get());

		$typeOfRecordsString = "(" . implode(", ", array_map(array($this->_db, "sqlText"), $enumerators)) . ")";

		$this->_db->execUntilSuccessful(
		    "CREATE TABLE IF NOT EXISTS `" . $name . "log` (" .
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

		$this->_db->execUntilSuccessful(
		    "CREATE TABLE IF NOT EXISTS `" . $name . "fulllog` (" .
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

		$turnoverDate = new DateTime("-3 month", $this->_timeZone);

		$result = $this->_db->exec(
		    "SELECT * FROM `" . $this->_prefix . "log` " .
		    "WHERE `date` <  " . $this->_db->sqlText($turnoverDate->format("Y-m-d H:i:s"))
		);

		if ($result->getNumRows() !== 0)
		    {
			$sums = array();

			$this->_db->exec("START TRANSACTION");

			while ($row = $result->getRow())
			    {
				$direction = ($row["amount"] > 0) ? "credits" : "debits";

				if (isset($sums[$row["client"]][$row["typeOfRecord"]][$direction]) === false)
				    {
					$sums[$row["client"]][$row["typeOfRecord"]][$direction] = 0;
				    }

				$sums[$row["client"]][$row["typeOfRecord"]][$direction] += $row["amount"];

				$this->_db->exec("DELETE FROM `" . $this->_prefix . "log` WHERE `id` = " . $this->_db->sqlText($row["id"]));
			    }

			foreach ($sums as $client => $amounts)
			    {
				foreach ($amounts as $typeOfRecord => $totals)
				    {
					foreach ($totals as $direction => $amount)
					    {
						$this->_db->exec(
						    "INSERT INTO `" . $this->_prefix . "log` (`client`, `date`, `typeOfRecord`, `amount`, `record`, `startingBalance`) " .
						    "VALUES (" . $this->_db->sqlText($client) . ", " .
								 $this->_db->sqlText($turnoverDate->format("Y-m-d H:i:s")) . ", " .
								 $this->_db->sqlText($typeOfRecord) . ", " .
								 $this->_db->sqlText($amount) . ", " . $this->_db->sqlText("starting " . $direction) . ", " .
								 "TRUE)"
						);
					    }
				    }
			    }

			$this->_db->exec("COMMIT");
		    } //end if
	    } //end __construct()


	/**
	 * Record
	 *
	 * @param string $client       Client name
	 * @param string $typeOfRecord Record type
	 * @param float  $amount       Amount
	 * @param string $record       Record details
	 *
	 * @return void
	 *
	 * @throws Exception Record has invalid type
	 *
	 * @exceptioncode EXCEPTION_INVALID_RECORD_TYPE
	 *
	 * @untranslatable Y-m-d H:i:s
	 * @untranslatable now
	 * @untranslatable FALSE
	 */

	public function record($client, $typeOfRecord, $amount, $record = false)
	    {
		if (in_array($typeOfRecord, $this->_typesOfRecords) === true)
		    {
			$dateNow = new DateTime("now", $this->_timeZone);

			$this->_db->exec("START TRANSACTION");
			$this->_db->exec(
			    "INSERT INTO `" . $this->_prefix . "log` (`client`, `date`, `typeOfRecord`, `amount`, `record`, `startingBalance`)" .
			    "VALUES (" . $this->_db->sqlText($client) . ", " .
					 $this->_db->sqlText($dateNow->format("Y-m-d H:i:s")) . " ," .
					 $this->_db->sqlText($typeOfRecord) . " ," .
					 $this->_db->sqlText($amount) . " ," .
					 $this->_db->sqlText($record) . " ," .
					 "FALSE" . ")"
			);
			$this->_db->exec(
			    "INSERT INTO `" . $this->_prefix . "fulllog` (`client`, `date`, `typeOfRecord`, `amount`, `record`)" .
			    "VALUES (" . $this->_db->sqlText($client) . ", " .
					 $this->_db->sqlText($dateNow->format("Y-m-d H:i:s")) . " ," .
					 $this->_db->sqlText($typeOfRecord) . " ," .
					 $this->_db->sqlText($amount) . " ," .
					 $this->_db->sqlText($record) . ")"
			);
			$this->_db->exec("COMMIT");
		    }
		else
		    {
			throw new Exception(_("Invalid record type"), EXCEPTION_INVALID_RECORD_TYPE);
		    } //end if
	    } //end record()


	/**
	 * Transfer balance
	 *
	 * @param string $client                  Client name
	 * @param string $sourceTypeOfRecord      Record type
	 * @param string $destinationTypeOfRecord Record type
	 * @param float  $amount                  Amount
	 * @param string $record                  Record
	 *
	 * @return void
	 *
	 * @throws Exception Record has invalid type
	 *
	 * @exceptioncode EXCEPTION_USELESS_OPERATION
	 * @exceptioncode EXCEPTION_INVALID_RECORD_TYPE
	 *
	 * @untranslatable Y-m-d H:i:s
	 * @untranslatable now
	 */

	public function transferBalance($client, $sourceTypeOfRecord, $destinationTypeOfRecord, $amount, $record = false)
	    {
		if (in_array($sourceTypeOfRecord, $this->_typesOfRecords) === true && in_array($destinationTypeOfRecord, $this->_typesOfRecords) === true)
		    {
			if ($amount === 0 || $sourceTypeOfRecord === $destinationTypeOfRecord)
			    {
				throw new Exception(_("Useless operation"), EXCEPTION_USELESS_OPERATION);
			    }
			else
			    {
				$dateNow = new DateTime("now", $this->_timeZone);

				$this->_db->exec("START TRANSACTION");
				$this->_db->exec(
				    "INSERT INTO `" . $this->_prefix . "log` (`client`, `date`, `typeOfRecord`, `amount`, `record`)" .
				    "VALUES (" . $this->_db->sqlText($client) . ", " .
						 $this->_db->sqlText($dateNow->format("Y-m-d H:i:s")) . " ," .
						 $this->_db->sqlText($sourceTypeOfRecord) . " ," .
						 $this->_db->sqlText((-$amount)) . " ," .
						 $this->_db->sqlText($record) . ")"
				);
				$this->_db->exec(
				    "INSERT INTO `" . $this->_prefix . "log` (`client`, `date`, `typeOfRecord`, `amount`, `record`)" .
				    "VALUES (" . $this->_db->sqlText($client) . ", " .
						 $this->_db->sqlText($dateNow->format("Y-m-d H:i:s")) . " ," .
						 $this->_db->sqlText($destinationTypeOfRecord) . " ," .
						 $this->_db->sqlText($amount) . " ," .
						 $this->_db->sqlText($record) . ")"
				);
				$this->_db->exec(
				    "INSERT INTO `" . $this->_prefix . "fulllog` (`client`, `date`, `typeOfRecord`, `amount`, `record`)" .
				    "VALUES (" . $this->_db->sqlText($client) . ", " .
						 $this->_db->sqlText($dateNow->format("Y-m-d H:i:s")) . " ," .
						 $this->_db->sqlText($sourceTypeOfRecord) . " ," .
						 $this->_db->sqlText((-$amount)) . " ," .
						 $this->_db->sqlText($record) . ")"
				);
				$this->_db->exec(
				    "INSERT INTO `" . $this->_prefix . "fulllog` (`client`, `date`, `typeOfRecord`, `amount`, `record`)" .
				    "VALUES (" . $this->_db->sqlText($client) . ", " .
						 $this->_db->sqlText($dateNow->format("Y-m-d H:i:s")) . " ," .
						 $this->_db->sqlText($destinationTypeOfRecord) . " ," .
						 $this->_db->sqlText($amount) . " ," .
						 $this->_db->sqlText($record) . ")"
				);
				$this->_db->exec("COMMIT");
			    } //end if
		    }
		else
		    {
			throw new Exception(_("Invalid record type"), EXCEPTION_INVALID_RECORD_TYPE);
		    } //end if
	    } //end transferBalance()


	/**
	 * Get end date
	 *
	 * @param mixed $endDate End date
	 *
	 * @return DateTime
	 *
	 * @untranslatable now
	 */

	private function _getEndDate($endDate)
	    {
		if ($endDate === false)
		    {
			$endDate = new DateTime("now", $this->_timeZone);
		    }

		return $endDate;
	    } //end _getEndDate()


	/**
	 * Get total credit amount
	 *
	 * @param string $client       Client name
	 * @param string $typeOfRecord Record type
	 * @param mixed  $startDate    Start date
	 * @param mixed  $endDate      End date
	 *
	 * @return float
	 *
	 * @throws Exception Record has invalid type
	 *
	 * @exceptioncode EXCEPTION_INVALID_RECORD_TYPE
	 *
	 * @untranslatable log`
	 */

	public function getTotalCreditAmount($client, $typeOfRecord, $startDate = false, $endDate = false)
	    {
		$endDate = $this->_getEndDate($endDate);

		if (in_array($typeOfRecord, $this->_typesOfRecords) === true)
		    {
			if ($startDate === false)
			    {
				$result = $this->_db->exec(
				    "SELECT SUM(`amount`) AS `sum` FROM `" . $this->_prefix . "log` " .
				    "WHERE `client` =  " . $this->_db->sqlText($client) .
				    " AND `typeOfRecord` = " . $this->_db->sqlText($typeOfRecord) .
				    " AND `amount` > 0"
				);
			    }
			else
			    {
				$result = $this->_db->exec(
				    "SELECT `date` FROM `" . $this->_prefix . "log` " .
				    "WHERE `startingBalance` = TRUE"
				);

				$choiceFullLog = false;

				if ($result->getNumRows() !== 0)
				    {
					while ($row = $result->getRow())
					    {
						$date = new DateTime($row["date"], $this->_timeZone);

						if ($startDate < $date)
						    {
							$choiceFullLog = true;
						    }
					    }
				    }

				$result = $this->_getSumAmount($choiceFullLog, $client, $startDate, $endDate, $typeOfRecord, true);
			    } //end if
		    }
		else
		    {
			throw new Exception(_("Invalid record type"), EXCEPTION_INVALID_RECORD_TYPE);
		    } //end if

		$row = $result->getRow();
		return $row["sum"];
	    } //end getTotalCreditAmount()


	/**
	 * Get total debit amount
	 *
	 * @param string $client       Client name
	 * @param string $typeOfRecord Record type
	 * @param mixed  $startDate    Start date
	 * @param mixed  $endDate      End date
	 *
	 * @return float
	 *
	 * @throws Exception Record has invalid type
	 *
	 * @exceptioncode EXCEPTION_INVALID_RECORD_TYPE
	 *
	 * @untranslatable log`
	 */

	public function getTotalDebitAmount($client, $typeOfRecord, $startDate = false, $endDate = false)
	    {
		$endDate = $this->_getEndDate($endDate);

		if (in_array($typeOfRecord, $this->_typesOfRecords) === true)
		    {
			if ($startDate === false)
			    {
				$result = $this->_db->exec(
				    "SELECT ABS(SUM(`amount`)) AS `sum` FROM `" . $this->_prefix . "log` " .
				    "WHERE `client` =  " . $this->_db->sqlText($client) .
				    " AND `typeOfRecord` = " . $this->_db->sqlText($typeOfRecord) .
				    " AND `amount` < 0"
				);
			    }
			else
			    {
				$result = $this->_db->exec(
				    "SELECT `date` FROM `" . $this->_prefix . "log` " .
				    "WHERE `startingBalance` = TRUE"
				);

				$choiceFullLog = false;

				if ($result->getNumRows() !== 0)
				    {
					while ($row = $result->getRow())
					    {
						$date = new DateTime($row["date"], $this->_timeZone);

						if ($startDate < $date)
						    {
							$choiceFullLog = true;
						    }
					    }
				    }

				$result = $this->_getSumAmount($choiceFullLog, $client, $startDate, $endDate, $typeOfRecord, false);
			    } //end if
		    }
		else
		    {
			throw new Exception(_("Invalid record type"), EXCEPTION_INVALID_RECORD_TYPE);
		    } //end if

		$row = $result->getRow();
		return $row["sum"];
	    } //end getTotalDebitAmount()


	/**
	 * Get summ debit or summ credit balance
	 *
	 * @param bool   $choiceFullLog If we use full log
	 * @param string $client        Client name
	 * @param mixed  $startDate     Start date
	 * @param mixed  $endDate       End date
	 * @param string $typeOfRecord  Record type
	 * @param bool   $amountSign    Sign of amount value
	 *
	 * @return array
	 *
	 * @untranslatable Y-m-d H:i:s
	 * @untranslatable AND
	 * @untranslatable log`
	 * @untranslatable fulllog`
	 */

	private function _getSumAmount($choiceFullLog, $client, $startDate, $endDate, $typeOfRecord, $amountSign)
	    {
		$result = "";
		if ($amountSign === true)
		    {
			$sign = ">";
		    }
		else
		    {
			$sign = "<";
		    }

		if ($choiceFullLog === false)
		    {
			$result = $this->_db->exec(
			    "SELECT ABS(SUM(`amount`)) AS `sum` FROM `" . $this->_prefix . "log` " .
			    "WHERE `client` =  " . $this->_db->sqlText($client) .
			    " AND (`date` BETWEEN " . $this->_db->sqlText($startDate->format("Y-m-d H:i:s")) .
			    " AND " . $this->_db->sqlText($endDate->format("Y-m-d H:i:s")) . ")" .
			    " AND `typeOfRecord` = " . $this->_db->sqlText($typeOfRecord) .
			    " AND `amount`" . $sign . "0"
			);
		    }
		else
		    {
			$result = $this->_db->exec(
			    "SELECT ABS(SUM(`amount`)) AS `sum` FROM `" . $this->_prefix . "fulllog` " .
			    "WHERE `client` =  " . $this->_db->sqlText($client) .
			    " AND (`date` BETWEEN " . $this->_db->sqlText($startDate->format("Y-m-d H:i:s")) .
			    " AND " . $this->_db->sqlText($endDate->format("Y-m-d H:i:s")) . ")" .
			    " AND `typeOfRecord` = " . $this->_db->sqlText($typeOfRecord) .
			    " AND `amount`" . $sign . "0"
			);
		    } //end if
		return $result;
	    } //end _getSumAmount()


	/**
	 * Get compound balance
	 *
	 * @param string $client       Client name
	 * @param string $typeOfRecord Record type
	 * @param mixed  $startDate    Start date
	 * @param mixed  $endDate      End date
	 *
	 * @return float
	 *
	 * @throws Exception Record has invalid type
	 *
	 * @exceptioncode EXCEPTION_INVALID_RECORD_TYPE
	 *
	 * @untranslatable Y-m-d H:i:s
	 * @untranslatable AND
	 * @untranslatable log`
	 * @untranslatable fulllog`
	 */

	public function getCompoundBalance($client, $typeOfRecord, $startDate = false, $endDate = false)
	    {
		$endDate = $this->_getEndDate($endDate);

		if (in_array($typeOfRecord, $this->_typesOfRecords) === true)
		    {
			if ($startDate === false)
			    {
				$result = $this->_db->exec(
				    "SELECT SUM(`amount`) AS `total` FROM `" . $this->_prefix . "log` " .
				    "WHERE `client` =  " . $this->_db->sqlText($client) .
				    " AND `typeOfRecord` = " . $this->_db->sqlText($typeOfRecord)
				);
			    }
			else
			    {
				$result = $this->_db->exec(
				    "SELECT `date` FROM `" . $this->_prefix . "log` " .
				    "WHERE `startingBalance` = TRUE"
				);

				$choiceFullLog = false;

				if ($result->getNumRows() !== 0)
				    {
					while ($row = $result->getRow())
					    {
						$date = new DateTime($row["date"], $this->_timeZone);
						if ($startDate < $date)
						    {
							$choiceFullLog = true;
						    }
					    }
				    }

				if ($choiceFullLog === false)
				    {
					$result = $this->_db->exec(
					    "SELECT SUM(`amount`) AS `total` FROM `" . $this->_prefix . "log` " .
					    "WHERE `client` =  " . $this->_db->sqlText($client) .
					    " AND (`date` BETWEEN " . $this->_db->sqlText($startDate->format("Y-m-d H:i:s")) .
					    " AND " . $this->_db->sqlText($endDate->format("Y-m-d H:i:s")) . ")" .
					    " AND `typeOfRecord` = " . $this->_db->sqlText($typeOfRecord)
					);
				    }
				else
				    {
					$result = $this->_db->exec(
					    "SELECT SUM(`amount`) AS `total` FROM `" . $this->_prefix . "fulllog` " .
					    "WHERE `client` =  " . $this->_db->sqlText($client) .
					    " AND (`date` BETWEEN " . $this->_db->sqlText($startDate->format("Y-m-d H:i:s")) .
					    " AND " . $this->_db->sqlText($endDate->format("Y-m-d H:i:s")) . ")" .
					    " AND `typeOfRecord` = " . $this->_db->sqlText($typeOfRecord)
					);
				    }
			    } //end if
		    }
		else
		    {
			throw new Exception(_("Invalid record type"), EXCEPTION_INVALID_RECORD_TYPE);
		    } //end if

		$row = $result->getRow();

		if ($row["total"] === null)
		    {
			$totalSum = 0.0;
		    }
		else
		    {
			$totalSum = $row["total"];
		    }

		return $totalSum;
	    } //end getCompoundBalance()


	/**
	 * Get records
	 *
	 * @param string $client       Client name
	 * @param mixed  $startDate    Start date
	 * @param mixed  $endDate      End date
	 * @param string $typeOfRecord Record type
	 * @param int    $count        Count of records
	 *
	 * @return array
	 *
	 * @throws Exception Record has invalid type
	 *
	 * @exceptioncode EXCEPTION_INVALID_RECORD_TYPE
	 * @exceptioncode EXCEPTION_NO_RECORDS_SELECTED
	 *
	 * @untranslatable Y-m-d H:i:s
	 * @untranslatable AND
	 * @untranslatable fulllog`
	 * @untranslatable LIMIT
	 */

	public function getRecords($client, $startDate = false, $endDate = false, $typeOfRecord = false, $count = false)
	    {
		$limit = ($count === false) ? "" : " LIMIT " . $count;

		$endDate = $this->_getEndDate($endDate);

		if ($typeOfRecord === false)
		    {
			if ($startDate === false)
			    {
				$result = $this->_db->exec(
				    "SELECT * FROM `" . $this->_prefix . "fulllog` " .
				    "WHERE `client` =  " . $this->_db->sqlText($client) .
				    " AND `date` <" . $this->_db->sqlText($endDate->format("Y-m-d H:i:s")) . $limit
				);
			    }
			else
			    {
				$result = $this->_db->exec(
				    "SELECT * FROM `" . $this->_prefix . "fulllog` " .
				    "WHERE `client` =  " . $this->_db->sqlText($client) .
				    " AND (`date` BETWEEN " . $this->_db->sqlText($startDate->format("Y-m-d H:i:s")) .
				    " AND " . $this->_db->sqlText($endDate->format("Y-m-d H:i:s")) . ")" . $limit
				);
			    }
		    }
		else if (in_array($typeOfRecord, $this->_typesOfRecords) === true)
		    {
			if ($startDate === false)
			    {
				$result = $this->_db->exec(
				    "SELECT * FROM `" . $this->_prefix . "fulllog` " .
				    "WHERE `client` =  " . $this->_db->sqlText($client) .
				    " AND `date` <" . $this->_db->sqlText($endDate->format("Y-m-d H:i:s")) .
				    " AND `typeOfRecord` = " . $this->_db->sqlText($typeOfRecord) . $limit
				);
			    }
			else
			    {
				$result = $this->_db->exec(
				    "SELECT * FROM `" . $this->_prefix . "fulllog` " .
				    "WHERE `client` =  " . $this->_db->sqlText($client) .
				    " AND (`date` BETWEEN " . $this->_db->sqlText($startDate->format("Y-m-d H:i:s")) .
				    " AND " . $this->_db->sqlText($endDate->format("Y-m-d H:i:s")) . ")" .
				    " AND `typeOfRecord` = " . $this->_db->sqlText($typeOfRecord) . $limit
				);
			    }
		    }
		else
		    {
			throw new Exception(_("Invalid record type"), EXCEPTION_INVALID_RECORD_TYPE);
		    } //end if

		if ($result->getNumRows() !== 0)
		    {
			$records = array();

			while ($row = $result->getRow())
			    {
				$records[] = array(
					      "date"         => $row["date"],
					      "typeOfRecord" => $row["typeOfRecord"],
					      "amount"       => $row["amount"],
					      "record"       => $row["record"],
					     );
			    }

			return $records;
		    }
		else
		    {
			throw new Exception(_("No records selected"), EXCEPTION_NO_RECORDS_SELECTED);
		    }
	    } //end getRecords()


    } //end class

?>
