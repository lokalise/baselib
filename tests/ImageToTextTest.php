<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\BaseLib
 */

namespace Logics\Tests\Foundation\BaseLib;

use \Exception;
use \Logics\Foundation\BaseLib\ImageToText;
use \PHPUnit_Framework_TestCase;

/**
 * Test for TeX class
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2017-01-08 08:57:43 +0000 (Sun, 08 Jan 2017) $ $Revision: 353 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/baselib/tags/0.1.6/tests/ImageToTextTest.php $
 *
 * @runTestsInSeparateProcesses
 *
 * @donottranslate
 */

class ImageToTextTest extends PHPUnit_Framework_TestCase
    {

	use ImageToText;

	/**
	 * Testing text extraction.
	 *
	 * @return void
	 */

	public function testCanRecognizeTextFromImage()
	    {
		$text = $this->_getTextFromImage(file_get_contents(__DIR__ . "/encodedimage.txt"));
		$this->assertContains("+79055294992", $text);
	    } //end testCanRecognizeTextFromImage()


	/**
	 * Testing tesseract absence.
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_CANNOT_RUN_TESSERACT
	 */

	public function testThrowsAnExceptionWhenCannotLaunchTesseract()
	    {
		defined("EXCEPTION_CANNOT_RUN_TESSERACT") || define("EXCEPTION_CANNOT_RUN_TESSERACT", 1);

		self::$_tesseract = "/usr/bin/false";

		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_CANNOT_RUN_TESSERACT);
		$this->_getTextFromImage(file_get_contents(__DIR__ . "/encodedimage.txt"));
	    } //end testThrowsAnExceptionWhenCannotLaunchTesseract()


	/**
	 * Testing tesseract failure.
	 *
	 * @return void
	 *
	 * @exceptioncode EXCEPTION_ERROR_READING_TEXT_FILE
	 */

	public function testThrowsAnExceptionWhenTesseractFailsToRecognizeTheText()
	    {
		defined("EXCEPTION_ERROR_READING_TEXT_FILE") || define("EXCEPTION_ERROR_READING_TEXT_FILE", 1);

		self::$_tesseract = "/usr/bin/true";

		$this->expectException(Exception::CLASS);
		$this->expectExceptionCode(EXCEPTION_ERROR_READING_TEXT_FILE);
		$this->_getTextFromImage(file_get_contents(__DIR__ . "/encodedimage.txt"));
	    } //end testThrowsAnExceptionWhenTesseractFailsToRecognizeTheText()


    } //end class

?>
