<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\BaseLib
 */

namespace Logics\Foundation\BaseLib;

/**
 * XMLWriter capable to suspend/resume indentation
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2017-02-26 09:34:44 +0000 (Sun, 26 Feb 2017) $ $Revision: 358 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/baselib/tags/0.1.6/src/XMLWriter.php $
 */

class XMLWriter extends \XMLWriter
    {

	/**
	 * Indentation enabled or disabled
	 *
	 * @var bool
	 */
	private $_indent;

	/**
	 * Indentation suspended
	 *
	 * @var bool
	 */
	private $_suspend;

	/**
	 * State of indentation mode at suspend time
	 *
	 * @var bool
	 */
	private $_saved;

	/**
	 * Toggle indentation on/off
	 *
	 * @param bool $indent Whether indentation is enabled
	 *
	 * @return void
	 */

	public function setIndent($indent)
	    {
		parent::setIndent($indent);
		$this->_indent  = $indent;
		$this->_suspend = false;
	    } //end setIndent()


	/**
	 * Suspend indentation
	 *
	 * @return void
	 */

	public function suspendIndent()
	    {
		if ($this->_suspend === false)
		    {
			$this->_saved = $this->_indent;
			$this->setIndent(false);
			$this->_suspend = true;
		    }
	    } //end suspendIndent()


	/**
	 * Resume indentation
	 *
	 * @return void
	 */

	public function resumeIndent()
	    {
		if ($this->_suspend === true)
		    {
			if ($this->_saved === true)
			    {
				parent::text("\n");
			    }

			$this->setIndent($this->_saved);
			$this->_suspend = false;
		    }
	    } //end resumeIndent()


    } //end class

?>
