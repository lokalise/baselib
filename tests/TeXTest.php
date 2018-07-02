<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\BaseLib
 */

namespace Logics\Tests\Foundation\BaseLib;

use \Logics\Foundation\BaseLib\TeX;
use \PHPUnit_Framework_TestCase;

/**
 * Test for TeX class
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:44:24 +0000 (Wed, 17 Aug 2016) $ $Revision: 341 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/baselib/tags/0.1.6/tests/TeXTest.php $
 *
 * @donottranslate
 */

class TeXTest extends PHPUnit_Framework_TestCase
    {

	/**
	 * Testing object
	 *
	 * @var TeX
	 */
	protected $object;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */

	protected function setUp()
	    {
		$tex  = "\\documentclass[10pt]{article}\n";
		$tex .= "\\usepackage{graphicx}\n";
		$tex .= "\\usepackage[left=0mm,top=5mm,landscape,twoside=false]{geometry}\n";
		$tex .= "\\special{papersize=220mm,110mm}\n";
		$tex .= "\\setlength\\parskip{0pt}\n";
		$tex .= "\\pagestyle{empty}\n";
		$tex .= "\n";
		$tex .= "\\begin{document}\n";
		$tex .= "  \\begin{minipage}[b]{100mm}\n";
		$tex .= "    \\footnotesize\n";
		$tex .= "    If undelivered please return to:\n";
		$tex .= "\n";
		$tex .= "    Gefest Australia Pty Ltd\n";
		$tex .= "\n";
		$tex .= "    19 North Terrace\n";
		$tex .= "\n";
		$tex .= "    Hackney SA 5069\n";
		$tex .= "  \\end{minipage}\n";
		$tex .= "  \\hfill\n";
		$tex .= "  \\begin{minipage}[b]{96mm}\n";
		$tex .= "    \\includegraphics[width=26mm,height=26mm]{\$eps1\$}\n";
		$tex .= "  \\end{minipage}\n";
		$tex .= "\n";
		$tex .= "\\vspace{1.0in}\\LARGE\n";
		$tex .= "\\setlength\\parindent{60mm}\n";
		$tex .= "\\textsf{JOE BLOGGS}\n";
		$tex .= "\n";
		$tex .= "\\textsf{BIG COMPANY PTY LTD}\n";
		$tex .= "\n";
		$tex .= "\\textsf{1 KING WILLIAM STREET}\n";
		$tex .= "\n";
		$tex .= "\\textsf{ADELAIDE SA 5000}\n";
		$tex .= "\\end{document}\n";

		$this->object = new TeX($tex);
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
	    } //end tearDown()


	/**
	 * Testing PostScript generation failure (missing image)
	 *
	 * @return void
	 */

	public function testToPostScriptFailure()
	    {
		$this->assertFalse($this->object->toPostScript());
	    } //end testToPostScriptFailure()


	/**
	 * Testing PostScript generation failure (missing image)
	 *
	 * @return void
	 */

	public function testToPostScript()
	    {
		$this->object->addEPS(file_get_contents(__DIR__ . "/PostagePaidAustralia.eps"));
		$this->assertThat(
		    false, $this->logicalNot($this->equalTo($this->object->toPostScript())),
		    "Failed to produce PostScript, check presence of LaTeX and relevant TeX libraries"
		);
	    } //end testToPostScript()


    } //end class

?>
