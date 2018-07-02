<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\BaseLib
 */

namespace Logics\Foundation\BaseLib;

/**
 * DateInterval class with optimal ISO format
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-18 02:14:24 +0930 (Thu, 18 Aug 2016) $ $Revision: 341 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/baselib/trunk/src/Request.php $
 */

class DateInterval extends \DateInterval
    {

	/**
	 * Format DateInterval
	 *
	 * @param string $format Format DateInterval according to this string
	 *
	 * @return string
	 *
	 * @untranslatable T
	 * @untranslatable P
	 * @untranslatable PT0S
	 */

	public function format($format = false)
	    {
		if ($format === false)
		    {
			$date = $this->_getDate();
			$time = $this->_getTime();
			$time = (($time !== "" ) ? "T" : "") . $time;

			$period = "P" . $date . $time;
			if ($period === "P")
			    {
				return "PT0S";
			    }
			else
			    {
				return (($this->invert === 1) ? "-" : "") . $period;
			    }
		    }
		else
		    {
			return parent::format($format);
		    } //end if
	    } //end format()


	/**
	 * Express date period
	 *
	 * @return string
	 *
	 * @untranslatable Y
	 * @untranslatable M
	 * @untranslatable D
	 */

	private function _getDate()
	    {
		$date  = "";
		$date .= ($this->y > 0) ? $this->y . "Y" : "";
		$date .= ($this->m > 0) ? $this->m . "M" : "";
		$date .= ($this->d > 0) ? $this->d . "D" : "";

		return $date;
	    } //end _getDate()


	/**
	 * Express time period
	 *
	 * @return string
	 *
	 * @untranslatable H
	 * @untranslatable M
	 * @untranslatable S
	 */

	private function _getTime()
	    {
		$time  = "";
		$time .= ($this->h > 0) ? $this->h . "H" : "";
		$time .= ($this->i > 0) ? $this->i . "M" : "";
		$time .= ($this->s > 0) ? $this->s . "S" : "";

		return $time;
	    } //end _getTime()


    } //end class

?>
