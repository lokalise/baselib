<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\BaseLib
 */

namespace Logics\Tests\Foundation\BaseLib;

use \Logics\Foundation\BaseLib\XMLWriter;
use \PHPUnit_Framework_TestCase;

/**
 * Class for XMLWriter testing
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2017 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2017-02-26 09:34:44 +0000 (Sun, 26 Feb 2017) $ $Revision: 358 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/baselib/tags/0.1.6/tests/XMLWriterTest.php $
 *
 * @donottranslate
 */

class XMLWriterTest extends PHPUnit_Framework_TestCase
    {

	/**
	 * Testing ability to suspend and resume indentation
	 *
	 * @return void
	 */

	public function testAbleToSuspendAndResumeIndentation()
	    {
		$xmlwriter = new XMLWriter();
		$xmlwriter->openMemory();
		$xmlwriter->setIndent(true);
		$xmlwriter->setIndentString("  ");
		$xmlwriter->startDocument("1.0", "UTF-8");
		$xmlwriter->startElement("Books");
		$xmlwriter->startElement("Book");
		$xmlwriter->suspendIndent();
		$xmlwriter->startElement("Title");
		$xmlwriter->endElement();
		$xmlwriter->endElement();
		$xmlwriter->resumeIndent();
		$xmlwriter->endElement();
		$xmlwriter->endDocument();

		$expected  = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$expected .= "<Books>\n";
		$expected .= "  <Book><Title/></Book>\n";
		$expected .= "</Books>\n";

		$this->assertEquals($expected, $xmlwriter->outputMemory());
	    } //end testAbleToSuspendAndResumeIndentation()


    } //end class

?>
