<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Tests\Foundation\BaseLib
 */

namespace Logics\Tests\Foundation\BaseLib;

use \Logics\Foundation\BaseLib\RollingAuth;

/**
 * Test helper for RollingAuth class
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:44:24 +0000 (Wed, 17 Aug 2016) $ $Revision: 341 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/baselib/tags/0.1.6/tests/RollingAuthHelper.php $
 */

class RollingAuthHelper extends RollingAuth
    {

	/**
	 * Public proxy to checkKey()
	 *
	 * @param string $id  Client ID
	 * @param string $key MD5 hash of the shared key combined with current date as md5(date("Ymd") . $sharedkey)
	 *
	 * @return bool
	 */

	public function testCheckKey($id, $key)
	    {
		return $this->checkKey($id, $key);
	    } //end testCheckKey()


    } //end class

?>
