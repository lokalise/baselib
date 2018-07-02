<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\BaseLib
 */

namespace Logics\Tests\Foundation\BaseLib;

use \Logics\Foundation\BaseLib\DateInterval;
use \PHPUnit_Framework_TestCase;

/**
 * Class for testing DateInterval
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-18 02:14:24 +0930 (Thu, 18 Aug 2016) $ $Revision: 341 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/baselib/trunk/tests/CodebaseHashTest.php $
 *
 * @donottranslate
 */

class DateIntervalTest extends PHPUnit_Framework_TestCase
    {

	/**
	 * Testing DateInterval class
	 *
	 * @return void
	 */

	public function testCapableToReturnOptimalIsoFormattedDurationString()
	    {
		$interval = new DateInterval("PT0S");
		$this->assertEquals("PT0S", $interval->format());
		$this->assertEquals("P0Y0M0DT0H0M0S", $interval->format("P%yY%mM%dDT%hH%iM%sS"));

		$interval = new DateInterval("PT1S");
		$this->assertEquals("PT1S", $interval->format());
		$this->assertEquals("P0Y0M0DT0H0M1S", $interval->format("P%yY%mM%dDT%hH%iM%sS"));

		$interval = new DateInterval("P1Y1M1DT1H1M1S");
		$this->assertEquals("P1Y1M1DT1H1M1S", $interval->format());
		$this->assertEquals("P1Y1M1DT1H1M1S", $interval->format("P%yY%mM%dDT%hH%iM%sS"));
	    } //end testCapableToReturnOptimalIsoFormattedDurationString()


    } //end class

?>
