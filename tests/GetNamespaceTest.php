<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\BaseLib
 */

namespace Logics\Tests\Foundation\BaseLib;

use \Logics\Foundation\BaseLib\GetNamespace;
use \PHPUnit_Framework_TestCase;

/**
 * Class for GetNamespace trait testing
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2017 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2017-02-26 11:48:47 +0000 (Sun, 26 Feb 2017) $ $Revision: 360 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/baselib/tags/0.1.6/tests/GetNamespaceTest.php $
 *
 * @donottranslate
 */

class GetNamespaceTest extends PHPUnit_Framework_TestCase
    {

	use GetNamespace;

	/**
	 * Testing getting of namespace of the file.
	 *
	 * @return void
	 */

	public function testShouldBeAbleToGetNamespaceOfTheFile()
	    {
		$namespace = $this->_getFileNamespace(__FILE__);
		$this->assertEquals(__NAMESPACE__, $namespace);
	    } //end testShouldBeAbleToGetNamespaceOfTheFile()


    } //end class

?>
