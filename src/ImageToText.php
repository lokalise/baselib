<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\BaseLib
 */

namespace Logics\Foundation\BaseLib;

use \Exception;
use \Imagick;

/**
 * Extracts the textual data from the provided image.
 *
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:44:24 +0000 (Wed, 17 Aug 2016) $ $Revision: 341 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/baselib/tags/0.1.6/src/ImageToText.php $
 *
 * @requiredcommand /usr/bin/tesseract tesseract
 *
 * @untranslatable /usr/bin/tesseract
 */

trait ImageToText
    {

	/**
	 * Name of Tesseract executable
	 *
	 * @var string
	 */
	static private $_tesseract = "/usr/bin/tesseract";

	/**
	 * Extracts the textual data from the provided image.
	 *
	 * @param string $encodedimage Encoded image
	 *
	 * @return string
	 *
	 * @throws Exception Cannot process image to text
	 *
	 * @exceptioncode EXCEPTION_CANNOT_RUN_TESSERACT
	 * @exceptioncode EXCEPTION_ERROR_READING_TEXT_FILE
	 *
	 * @untranslatable > /dev/null 2>&1
	 */

	private function _getTextFromImage($encodedimage)
	    {
		$image   = new Imagick();
		$decoded = base64_decode($encodedimage);
		$image->readimageblob($decoded);

		$textfilename  = sys_get_temp_dir() . "/" . time() . uniqid();
		$imagefilename = sys_get_temp_dir() . "/" . time() . uniqid() . ".png";
		$image->writeImage($imagefilename);

		$cmdpngtotxt = self::$_tesseract . " " . $imagefilename . " " . $textfilename . " > /dev/null 2>&1";
		exec($cmdpngtotxt, $output, $retval);
		unset($output);
		if ($retval !== 0)
		    {
			throw new Exception(
			    _("Error while executing shell command") . " \"" . $cmdpngtotxt . "\", " . _("status code") . ": " . $retval,
			    EXCEPTION_CANNOT_RUN_TESSERACT
			);
		    } //end if

		$textfilename .= ".txt";
		if (file_exists($textfilename) === true)
		    {
			$text = file_get_contents($textfilename);
		    }
		else
		    {
			throw new Exception(_("Error while reading text file") . " " . $cmdpngtotxt, EXCEPTION_ERROR_READING_TEXT_FILE);
		    } //end if

		return $text;
	    } //end _getTextFromImage()


    } //end trait

?>
