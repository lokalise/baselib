<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\BaseLib
 */

namespace Logics\Foundation\BaseLib;

use \Logics\Foundation\SQL\SQLdatabase;

/**
 * Rolling authentication: encryption hash is shared and private, transmitted key is changed daily
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:44:24 +0000 (Wed, 17 Aug 2016) $ $Revision: 341 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/baselib/tags/0.1.6/src/RollingAuth.php $
 */

class RollingAuth extends Root
    {

	/**
	 * Database to use
	 *
	 * @var SQLdatabase
	 */
	private $_db;

	/**
	 * Authentication ID
	 *
	 * @var mixed
	 */
	protected $authID;

	/**
	 * Authentication config details
	 *
	 * @var array
	 */
	protected $authConfig;

	/**
	 * Instantiate this class
	 *
	 * @param SQLdatabase $db Database object to use for authentication
	 *
	 * @return void
	 */

	public function __construct(SQLdatabase $db)
	    {
		$this->_db        = $db;
		$this->authID     = false;
		$this->authConfig = array();

		$this->_db->execUntilSuccessful(
		    "CREATE TABLE IF NOT EXISTS `auth` (" .
		    "`id` char(32) NOT NULL," .
		    "`key` char(32) NOT NULL," .
		    "`remotekey` char(32) NOT NULL," .
		    "`wsdl` text NOT NULL," .
		    "PRIMARY KEY (`id`)" .
		    ") ENGINE=InnoDB DEFAULT CHARSET=utf8;"
		);
	    } //end __construct()


	/**
	 * Check id and key against auth table with rolling encryption
	 *
	 * @param string $id  Client ID
	 * @param string $key MD5 hash of the shared key combined with current date as md5(date("Ymd") . strtoupper($sharedkey))
	 *
	 * @return boolean True is key is valid, false otherwise
	 *
	 * @untranslatable Ymd
	 * @untranslatable -1 day
	 * @untranslatable 1 day
	 */

	protected function checkKey($id, $key)
	    {
		$check = false;

		if (preg_match("/^[0-9A-F]{32}$/", $key) === 1)
		    {
			$result = $this->_db->exec("SELECT * FROM auth WHERE id = " . $this->_db->sqlText($id));
			if ($result !== false && $result->GetNumRows() > 0)
			    {
				$row          = $result->GetRow();
				$yesterdaykey = strtoupper(md5(date("Ymd", strtotime("-1 day")) . strtoupper($row["key"])));
				$todaykey     = strtoupper(md5(date("Ymd") . strtoupper($row["key"])));
				$tomorrowkey  = strtoupper(md5(date("Ymd", strtotime("1 day")) . strtoupper($row["key"])));
				if ($key === $yesterdaykey || $key === $todaykey || $key === $tomorrowkey)
				    {
					$this->authID = $id;
					unset($row["id"]);
					unset($row["key"]);
					$this->authConfig = $row;
					$check            = true;
				    }
			    }
		    } //end if

		return $check;
	    } //end checkKey()


    } //end class

?>
