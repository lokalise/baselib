<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\BaseLib
 */

namespace Logics\Tests\Foundation\BaseLib;

use \DateTime;
use \Logics\Foundation\BaseLib\SerializableDateTime;
use \PHPUnit_Framework_TestCase;

/**
 * Class for testing SerializableDateTime
 *
 * @author    Anastasia Bashkirtseva <anastasia@bashkirtseva.com>
 * @copyright 2013-2018 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-18 02:14:24 +0930 (Thu, 18 Aug 2016) $ $Revision: 341 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/baselib/trunk/tests/CodebaseHashTest.php $
 *
 * @donottranslate
 */

class SerializableDateTimeTest extends PHPUnit_Framework_TestCase
    {

	/**
	 * Testing SerializableDateTime class
	 *
	 * @return void
	 */

	public function testShouldAllowToSerializeAndUnserializeDateTimeObject()
	    {
		$date              = new SerializableDateTime();
		$serializabledDate = serialize($date);
		$date              = unserialize($serializabledDate);
		$this->assertInstanceOf(DateTime::CLASS, $date);
	    } //end testShouldAllowToSerializeAndUnserializeDateTimeObject()


    } //end class

?>
