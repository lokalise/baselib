<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\BaseLib
 */

namespace Logics\Tests\Foundation\BaseLib;

use \Logics\Foundation\BaseLib\ReadOnlyProperties;
use \PHPUnit_Framework_TestCase;

/**
 * Class for ReadOnlyProperties trait testing
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2017 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2017-02-26 11:48:47 +0000 (Sun, 26 Feb 2017) $ $Revision: 360 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/baselib/tags/0.1.6/tests/ReadOnlyPropertiesTest.php $
 *
 * @donottranslate
 */

class ReadOnlyPropertiesTest extends PHPUnit_Framework_TestCase
    {

	use ReadOnlyProperties;

	/**
	 * Testing getting of read only properties
	 *
	 * @return void
	 */

	public function testShouldBeAbleToGetReadOnlyPropertiesWhichCannotBeUnset()
	    {
		$this->readonlyproperties["test"] = "value";
		$this->assertTrue(isset($this->test));
		$this->assertFalse(isset($this->nonexistentproperty));
		$this->assertEquals("value", $this->test);
		$this->assertNull($this->nonexistentproperty);
		unset($this->test);
		$this->assertEquals("value", $this->test);
	    } //end testShouldBeAbleToGetReadOnlyPropertiesWhichCannotBeUnset()


    } //end class

?>
