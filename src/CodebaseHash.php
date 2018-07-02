<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\BaseLib
 */

namespace Logics\Foundation\BaseLib;

use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;
use \RecursiveRegexIterator;
use \RegexIterator;

/**
 * Trait for getting the hash of files contained in the certain folder.
 *
 * @author    Alexander Shumilov <alex@logics.net.au>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:44:24 +0000 (Wed, 17 Aug 2016) $ $Revision: 341 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/baselib/tags/0.1.6/src/CodebaseHash.php $
 */

trait CodebaseHash
    {

	/**
	 * Returns overall hash for files in specified folder (recursive search).
	 *
	 * @param string $directory Scanned directory
	 * @param string $regexp    Optional regexp to specify certain files
	 *
	 * @return string
	 *
	 * @untranslatable /[^.].*(\.php|\.xsd)/
	 */

	static private function _getCodebaseHash($directory, $regexp = false)
	    {
		if ($regexp === false)
		    {
			$regexp = "/[^.].*(\.php|\.xsd)/";
		    }

		$directory = new RecursiveDirectoryIterator($directory);
		$iterator  = new RecursiveIteratorIterator($directory);
		$files     = new RegexIterator($iterator, $regexp, RecursiveRegexIterator::GET_MATCH);

		$hashes = array();
		foreach ($files as $file)
		    {
			$hashes[] = md5(file_get_contents($file[0]));
		    }

		sort($hashes);

		$s = "";
		foreach ($hashes as $hash)
		    {
			$s .= $hash;
		    }

		return md5($s);
	    } //end _getCodebaseHash()


    } //end trait

?>
