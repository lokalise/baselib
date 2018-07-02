<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\BaseLib
 */

namespace Logics\Tests\Foundation\BaseLib;

use \Logics\Foundation\SQL\MySQLdatabase;
use \Logics\Tests\GetConnectionMySQL;
use \Logics\Tests\PHPUnit_Extensions_Database_SQL_TestCase;

/**
 * Test for RollingAuth class
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:44:24 +0000 (Wed, 17 Aug 2016) $ $Revision: 341 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/baselib/tags/0.1.6/tests/RollingAuthTest.php $
 *
 * @donottranslate
 */

class RollingAuthTest extends PHPUnit_Extensions_Database_SQL_TestCase
    {

	use GetConnectionMySQL;

	/**
	 * Testing object
	 *
	 * @var RollingAuthHelper
	 */
	protected $object;

	/**
	 * Instance of SQLdatabase
	 *
	 * @var SQLdatabase
	 */
	private $_db;

	/**
	 * Get test data set
	 *
	 * @return dataset
	 */

	public function getDataSet()
	    {
		return $this->createFlatXmlDataSet(__DIR__ . "/testRollingAuthTable.xml");
	    } //end getDataSet()


	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */

	protected function setUp()
	    {
		$this->_db = new MySQLdatabase($GLOBALS["DB_HOST"], $GLOBALS["DB_DBNAME"], $GLOBALS["DB_USER"], $GLOBALS["DB_PASSWD"]);
		$this->_db->execUntilSuccessful("DROP TABLE IF EXISTS `auth`");
		$this->object = new RollingAuthHelper($this->_db);

		parent::setUp();
	    } //end setUp()


	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */

	protected function tearDown()
	    {
		unset($this->object);
		$this->_db->execUntilSuccessful("DROP TABLE IF EXISTS `auth`");
	    } //end tearDown()


	/**
	 * Testing checkKey()
	 *
	 * @return void
	 */

	public function testCheckKey()
	    {
		$key = "304a65ba047034c70a19ba289826c347";

		$this->assertFalse($this->object->testCheckKey("authid", "wrongkey"));
		$this->assertFalse($this->object->testCheckKey("unknownauthid", strtoupper($key)));

		$this->assertFalse($this->object->testCheckKey("authid", strtoupper(md5(date("Ymd", strtotime("-2 day")) . strtoupper($key)))));
		$this->assertTrue($this->object->testCheckKey("authid", strtoupper(md5(date("Ymd", strtotime("-1 day")) . strtoupper($key)))));
		$this->assertTrue($this->object->testCheckKey("authid", strtoupper(md5(date("Ymd") . strtoupper($key)))));
		$this->assertTrue($this->object->testCheckKey("authid", strtoupper(md5(date("Ymd", strtotime("1 day")) . strtoupper($key)))));
		$this->assertFalse($this->object->testCheckKey("authid", strtoupper(md5(date("Ymd", strtotime("2 day")) . strtoupper($key)))));
	    } //end testCheckKey()


    } //end class

?>
