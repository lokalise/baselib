<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\BaseLib
 *
 * @untranslatable \ErrorLogger
 */

namespace Logics\Foundation\BaseLib;

use \Logics\Foundation\BaseLib\ErrorLogger;

// Each class inherited from Root class is supported by centralized error logging facility.
if (class_exists(ErrorLogger::CLASS) === false)
    {
	include "ErrorLogger.php";
    }

/**
 * Root class for all classes
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2017-01-21 17:06:20 +0000 (Sat, 21 Jan 2017) $ $Revision: 355 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/baselib/tags/0.1.6/src/Root.php $
 */

abstract class Root
    {
    } //end class

?>
