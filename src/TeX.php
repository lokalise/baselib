<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\BaseLib
 */

namespace Logics\Foundation\BaseLib;

/**
 * Class for TeX to PostScript conversion
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2016-08-17 16:44:24 +0000 (Wed, 17 Aug 2016) $ $Revision: 341 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/baselib/tags/0.1.6/src/TeX.php $
 */

class TeX extends Root
    {
	/**
	 * LaTeX
	 *
	 * @requiredcommand /usr/bin/latex texlive-latex
	 *
	 * @untranslatable /usr/bin/latex
	 */
	const LATEX = "/usr/bin/latex";

	/**
	 * DVIPS utility
	 *
	 * @requiredcommand /usr/bin/dvips texlive-dvips
	 *
	 * @untranslatable /usr/bin/dvips
	 */
	const DVIPS = "/usr/bin/dvips";

	/**
	 * TeX document
	 *
	 * @var string
	 */
	private $_tex;

	/**
	 * EPS images
	 *
	 * @var array
	 */
	private $_eps;

	/**
	 * Construct TeX to PostScript converter
	 *
	 * @param string $tex TeX article to be printed. May have embedded images supplied through addEPS() method.
	 *                    Images must be referred as $epsN$ where N is image sequence number.
	 *
	 * @return void
	 */

	public function __construct($tex)
	    {
		$this->_tex = $tex;
		$this->_eps = array();
	    } //end __construct()


	/**
	 * Add EPS image to accompany TeX article
	 *
	 * @param string $eps EPS image
	 *
	 * @return void
	 */

	public function addEPS($eps)
	    {
		$this->_eps[] = $eps;
	    } //end addEPS()


	/**
	 * Generate PostScript document from TeX article
	 *
	 * @return string PostScript document
	 *
	 * @untranslatable .eps
	 * @untranslatable $eps
	 * @untranslatable .tex
	 * @untranslatable .dvi
	 * @untranslatable .aux
	 * @untranslatable .log
	 * @untranslatable .ps
	 * @untranslatable cd
	 * @untranslatable -halt-on-error -interaction=batchmode
	 * @untranslatable 2> /dev/null
	 * @untranslatable -o
	 */

	public function toPostScript()
	    {
		$hash     = md5(uniqid(mt_rand(), true));
		$tex      = $this->_tex;
		$epsfiles = array();
		foreach ($this->_eps as $idx => $eps)
		    {
			$epshash    = md5(uniqid(mt_rand(), true));
			$epsfile    = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $epshash . ".eps";
			$epsfiles[] = $epsfile;
			$tex        = str_replace('$eps' . ($idx + 1) . '$', $epsfile, $tex);
			file_put_contents($epsfile, $eps);
		    }

		$texfile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $hash . ".tex";
		$dvifile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $hash . ".dvi";
		$auxfile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $hash . ".aux";
		$logfile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $hash . ".log";
		file_put_contents($texfile, $tex);
		$psfile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $hash . ".ps";
		$tmpdir = sys_get_temp_dir();

		exec("cd " . $tmpdir . "; " . self::LATEX . " -halt-on-error -interaction=batchmode " . $texfile . " 2> /dev/null");
		if (file_exists($dvifile) === true)
		    {
			exec(self::DVIPS . " " . $dvifile . " -o " . $psfile . " 2> /dev/null");
		    }

		if (file_exists($psfile) === true)
		    {
			$ps = file_get_contents($psfile);
			unlink($psfile);
		    }
		else
		    {
			$ps = false;
		    }

		$this->_unlinkFile($texfile);
		$this->_unlinkFile($dvifile);
		$this->_unlinkFile($auxfile);
		$this->_unlinkFile($logfile);

		foreach ($epsfiles as $epsfile)
		    {
			$this->_unlinkFile($epsfile);
		    }

		return $ps;
	    } //end toPostScript()


	/**
	 * Unlink file if it does exist
	 *
	 * @param string $file File name to unlink
	 *
	 * @return void
	 */

	private function _unlinkFile($file)
	    {
		if (file_exists($file) === true)
		    {
			unlink($file);
		    }
	    } //end _unlinkFile()


    } //end class

?>
