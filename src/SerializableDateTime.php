<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\BaseLib
 */

namespace Logics\Foundation\BaseLib;

/**
 * Serializable DateTime
 *
 * @author    Anastasia Bashkirtseva <anastasia@bashkirtseva.com>
 * @copyright 2013-2018 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-18 02:14:24 +0930 (Thu, 18 Aug 2016) $ $Revision: 341 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/baselib/trunk/src/SerializableDateTime.php $
 */

class SerializableDateTime extends \DateTime
    {

	/**
	 * DateTime string
	 *
	 * @var string
	 */
	private $_datetime;

	/**
	 * Decide how it will react when it is treated like a string
	 *
	 * @return string
	 *
	 * @untranslatable c
	 */

	public function __toString()
	    {
		return $this->format("c");
	    } //end __toString()


	/**
	 * Prepared for serialization
	 *
	 * @return array
	 *
	 * @untranslatable c
	 * @untranslatable _datetime
	 */

	public function __sleep()
	    {
		$this->_datetime = $this->format("c");
		return array("_datetime");
	    } //end __sleep()


	/**
	 * Prepared for unserialization
	 *
	 * @return void
	 */

	public function __wakeup()
	    {
		$this->__construct($this->_datetime);
	    } //end __wakeup()


    } //end class

?>
