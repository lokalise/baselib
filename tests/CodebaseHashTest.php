<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\BaseLib
 */

namespace Logics\Tests\Foundation\BaseLib;

use \Logics\Foundation\BaseLib\CodebaseHash;
use \PHPUnit_Framework_TestCase;

/**
 * Class for testing CodebaseHash
 *
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:44:24 +0000 (Wed, 17 Aug 2016) $ $Revision: 341 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/baselib/tags/0.1.6/tests/CodebaseHashTest.php $
 *
 * @donottranslate
 */

class CodebaseHashTest extends PHPUnit_Framework_TestCase
    {

	use CodebaseHash;

	/**
	 * Testing CodebaseHash trait.
	 *
	 * @return void
	 */

	public function testCodebaseHash()
	    {
		$directory = __DIR__ . DIRECTORY_SEPARATOR . "codebasehashtestset";

		file_put_contents($directory . DIRECTORY_SEPARATOR . "test2.extphp", "<?php echo \"testing2\"; ?>");

		$hash = $this->_getCodebaseHash($directory, "/^[^.].*(\.extphp|\.xsd)$/");
		$this->assertEquals("d57d3cba4e6c1ece654683f04f4b24f4", $hash);

		file_put_contents($directory . DIRECTORY_SEPARATOR . "test2.extphp", "MORE_DATA", FILE_APPEND);
		$hash = $this->_getCodebaseHash($directory, "/[^.].*(\.extphp|\.xsd)$/");
		$this->assertEquals("6db4f363e10c11cf5b75613331d7b44b", $hash);

		file_put_contents($directory . DIRECTORY_SEPARATOR . "testfolder" . DIRECTORY_SEPARATOR . "test4.notphp", "<?php echo \"testing4notphp\"; ?>");

		file_put_contents($directory . DIRECTORY_SEPARATOR . "testfolder" . DIRECTORY_SEPARATOR . "test4.notphp", "MORE_DATA", FILE_APPEND);

		$hash = $this->_getCodebaseHash($directory, "/[^.].*(\.extphp|\.xsd)$/");
		$this->assertEquals("6db4f363e10c11cf5b75613331d7b44b", $hash);

		$hash = $this->_getCodebaseHash($directory);
		$this->assertEquals("d41d8cd98f00b204e9800998ecf8427e", $hash);

		file_put_contents($directory . DIRECTORY_SEPARATOR . "test2.extphp", "<?php echo \"testing2\"; ?>");
		file_put_contents($directory . DIRECTORY_SEPARATOR . "testfolder" . DIRECTORY_SEPARATOR . "test4.notphp", "<?php echo \"testing4notphp\"; ?>");
	    } //end testCodebaseHash()


    } //end class

?>
