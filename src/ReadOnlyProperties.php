<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\BaseLib
 */

namespace Logics\Foundation\BaseLib;

/**
 * Read only access to object properties through magic __get and __isset methods
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-01-07 11:27:31 +1030 (Thu, 07 Jan 2016) $ $Revision: 129 $
 * @link      $HeadURL: http://svn.logics.net.au/foundation/web/MagicParams.php $
 */

trait ReadOnlyProperties
    {

	/**
	 * Read only properties
	 *
	 * @var array
	 */
	protected $readonlyproperties;

	/**
	 * Magic __get method to get value of read only property
	 *
	 * @param string $name Property name
	 *
	 * @return mixed Property value
	 */

	public function __get($name)
	    {
		if (isset($this->readonlyproperties[$name]) === true)
		    {
			return $this->readonlyproperties[$name];
		    }
	    } //end __get()


	/**
	 * Magic __isset method to check if read only property is set
	 *
	 * @param string $name Property name
	 *
	 * @return boolean True if is set
	 */

	public function __isset($name)
	    {
		return isset($this->readonlyproperties[$name]);
	    } //end __isset()


    } //end trait

?>
