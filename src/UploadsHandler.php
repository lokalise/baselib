<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\BaseLib
 */

namespace Logics\Foundation\BaseLib;

/**
 * Class for overriding uploads functions
 *
 * @author    Anastasia Bashkirtseva <anastasia@bashkirtseva.com>
 * @copyright 2013-2017 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2017-03-19 14:30:05 +0000 (Sun, 19 Mar 2017) $ $Revision: 363 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/baselib/tags/0.1.6/src/UploadsHandler.php $
 */

class UploadsHandler
    {

	/**
	 * Tells whether the file was uploaded via HTTP POST
	 *
	 * @param string $filename The filename being checked.
	 *
	 * @return bool
	 */

	static public function isUploadedFile($filename)
	    {
		return is_uploaded_file($filename);
	    } //end isUploadedFile()


	/**
	 * Moves an uploaded file to a new location
	 *
	 * @param string $filename    The filename of the uploaded file.
	 * @param string $destination The destination of the moved file.
	 *
	 * @return bool
	 */

	static public function moveUploadedFile($filename, $destination)
	    {
		return move_uploaded_file($filename, $destination);
	    } //end moveUploadedFile()


    } //end class

?>
