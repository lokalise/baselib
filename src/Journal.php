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
 * @version   SVN: $Date: 2016-08-17 16:44:24 +0000 (Wed, 17 Aug 2016) $ $Revision: 341 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/baselib/tags/0.1.6/src/Journal.php $
 */

class Journal
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
	 * Instantiate this class
	 *
	 * @param SQLdatabase $db          Database which contains journal table
	 * @param string      $name        Prefix name
	 * @param array       $enumerators Array of record types
	 *
	 * @return void
	 *
	 * @untranslatable log` (
	 * @untranslatable NOT NULL,
	 */

	public function __construct(SQLdatabase $db, $name, array $enumerators)
	    {
		$this->_db             = $db;
		$this->_prefix         = $name;
		$this->_typesOfRecords = $enumerators;

		$typeOfRecordsString = "(\"" . implode("\",\"", $enumerators) . "\")";

		$this->_db->execUntilSuccessful(
		    "CREATE TABLE IF NOT EXISTS `" . $name . "log` (" .
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
	    } //end __construct()


	/**
	 * Record
	 *
	 * @param string $client       Client name
	 * @param string $typeOfRecord Record type
	 * @param string $record       Record content
	 *
	 * @return void
	 *
	 * @throws Exception Record has invalid type
	 *
	 * @exceptioncode EXCEPTION_INVALID_RECORD_TYPE
	 *
	 * @untranslatable Y-m-d H:i:s
	 * @untranslatable now
	 */

	public function record($client, $typeOfRecord, $record)
	    {
		if (in_array($typeOfRecord, $this->_typesOfRecords) === true)
		    {
			$timeZone = new DateTimeZone(date_default_timezone_get());
			$dateNow  = new DateTime("now", $timeZone);

			$this->_db->exec(
			    "INSERT INTO `" . $this->_prefix . "log` (`client`, `date`, `typeOfRecord`, `record`)" .
			    "VALUES (" . $this->_db->sqlText($client) . ", " .
					 $this->_db->sqlText($dateNow->format("Y-m-d H:i:s")) . " ," .
					 $this->_db->sqlText($typeOfRecord) . " ," .
					 $this->_db->sqlText($record) . ")"
			);
		    }
		else
		    {
			throw new Exception(_("Invalid record type"), EXCEPTION_INVALID_RECORD_TYPE);
		    }
	    } //end record()


	/**
	 * Get records
	 *
	 * @param string $client       Client name
	 * @param mixed  $startDate    Start date
	 * @param mixed  $endDate      End date
	 * @param string $typeOfRecord Record type
	 * @param int    $count        Number of records
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
	 * @untranslatable log`
	 * @untranslatable now
	 * @untranslatable LIMIT
	 */

	public function getRecords($client, $startDate = false, $endDate = false, $typeOfRecord = false, $count = false)
	    {
		($count === false) ? $limit = "" : $limit = " LIMIT " . $count;
		if ($endDate === false)
		    {
			$timeZone = new DateTimeZone(date_default_timezone_get());
			$endDate  = new DateTime("now", $timeZone);
		    }

		if ($typeOfRecord === false)
		    {
			if ($startDate === false)
			    {
				$result = $this->_db->exec(
				    "SELECT * FROM `" . $this->_prefix . "log` " .
				    "WHERE `client` =  " . $this->_db->sqlText($client) .
				    " AND `date` <" . $this->_db->sqlText($endDate->format("Y-m-d H:i:s")) . $limit
				);
			    }
			else
			    {
				$result = $this->_db->exec(
				    "SELECT * FROM `" . $this->_prefix . "log` " .
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
				    "SELECT * FROM `" . $this->_prefix . "log` " .
				    "WHERE `client` =  " . $this->_db->sqlText($client) .
				    " AND `date` <" . $this->_db->sqlText($endDate->format("Y-m-d H:i:s")) .
				    " AND `typeOfRecord` = " . $this->_db->sqlText($typeOfRecord) . $limit
				);
			    }
			else
			    {
				$result = $this->_db->exec(
				    "SELECT * FROM `" . $this->_prefix . "log` " .
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
