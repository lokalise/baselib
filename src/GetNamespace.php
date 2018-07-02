<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\BaseLib
 */

namespace Logics\Foundation\BaseLib;

/**
 * Get namespace used in PHP file
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:44:24 +0000 (Wed, 17 Aug 2016) $ $Revision: 341 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/baselib/tags/0.1.6/src/GetNamespace.php $
 */

trait GetNamespace
    {

	/**
	 * Get namespace from PHP file
	 *
	 * @param string $file PHP file name
	 *
	 * @return string File namespace
	 */

	private static function _getFileNamespace($file)
	    {
		$tokens = token_get_all(file_get_contents($file));

		$namespace = "";
		foreach ($tokens as $idx => $token)
		    {
			if (isset($token[0]) === true && $token[0] === T_NAMESPACE)
			    {
				$idx++;
				do
				    {
					$namespace .= $tokens[$idx][1];
					$idx++;
				    } while (is_array($tokens[$idx]) !== false || $tokens[$idx] !== ";");

				$namespace = trim($namespace);
				break;
			    }
		    }

		return $namespace;
	    } //end _getFileNamespace()


    } //end trait

?>
