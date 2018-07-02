<?php

/**
 * PHP version 5.6
 *
 * @package Logics\Foundation\BaseLib
 */

namespace Logics\Foundation\BaseLib;

use \Exception;
use \mysqli;

/**
 * ErrorHandler class
 *
 * @author    Vladimir Bashkirtsev <vladimir@bashkirtsev.com>
 * @copyright 2013-2016 Vladimir Bashkirtsev
 * @license   https://opensource.org/licenses/MIT MIT License
 * @version   SVN: $Date: 2017-01-21 17:06:20 +0000 (Sat, 21 Jan 2017) $ $Revision: 355 $
 * @link      $HeadURL: https://open.logics.net.au/foundation/baselib/tags/0.1.6/src/ErrorLogger.php $
 *
 * @optionalconst LOGGER_DISABLE true If set to true then error logging is reverted to standard PHP behaviour
 *
 * @untranslatable LOGGER_DISABLE
 * @untranslatable errorHandler
 * @untranslatable exceptionHandler
 * @untranslatable shutdownHandler
 * @untranslatable display_errors
 */

class ErrorLogger
    {

	/**
	 * Handler for PHP errors
	 *
	 * @param int    $errno      The level of the error raised
	 * @param string $errstr     The error message
	 * @param string $errfile    The filename that the error was raised in
	 * @param int    $errline    The line number the error was raised at
	 * @param array  $errcontext Array which points to the active symbol table at the point where the error occurred
	 *
	 * @return boolean true if script should continue execution
	 *
	 * @untranslatable Contains unserializable data
	 */

	public static function errorHandler($errno, $errstr, $errfile, $errline, array $errcontext)
	    {
		if (self::_suppressLogging($errcontext, $errstr, $errfile) === false)
		    {
			$backtrace = debug_backtrace();
			foreach ($errcontext as $idx => $value)
			    {
				if (self::_serializable($value) === false)
				    {
					$errcontext[$idx] = "Contains unserializable data";
				    }
			    }

			self::_log($errno, $errstr, $errfile, $errline, $backtrace, $errcontext);
		    }

		$nonfatalerrors = array(
				   E_NOTICE,
				   E_WARNING,
				   E_DEPRECATED,
				   E_USER_NOTICE,
				   E_USER_WARNING,
				   E_USER_DEPRECATED,
				   E_CORE_WARNING,
				   E_COMPILE_WARNING,
				   E_STRICT,
				  );
		if (in_array($errno, $nonfatalerrors) === true)
		    {
			return true;
		    }
		else
		    {
			exit();
		    }
	    } //end errorHandler()


	/**
	 * Check if error should be suppressed
	 *
	 * @param array  $context Array which points to the active symbol table at the point where the error occurred
	 * @param string $error   The error message
	 * @param string $file    The filename that the error was raised in
	 *
	 * @return boolean true if error should not be logged
	 *
	 * @optionalconst LOGGER_SUPPRESS      "Too many connections" Regular expression to match in order to suppress logging
	 * @optionalconst LOGGER_SUPPRESS_FILE "MySQLdatabase.php"    Regular expression to match with file name in order to suppress logging from that file
	 *
	 * @untranslatable LOGGER_SUPPRESS
	 * @untranslatable LOGGER_SUPPRESS_FILE
	 */

	private static function _suppressLogging(array $context, $error, $file)
	    {
		return (isset($context["suppressLogging"]) === true && preg_match("/" . $context["suppressLogging"] . "/", $error) > 0) ||
		       (defined("LOGGER_SUPPRESS") === true && preg_match("/" . LOGGER_SUPPRESS . "/", $error) > 0) ||
		       (defined("LOGGER_SUPPRESS_FILE") === true && preg_match("/" . LOGGER_SUPPRESS_FILE . "/", $file) > 0);
	    } //end _suppressLogging()


	/**
	 * Handler for uncaught exceptions
	 *
	 * @param Exception $e Uncaught exception to log
	 *
	 * @return void
	 *
	 * @untranslatable exception
	 */

	public static function exceptionHandler(Exception $e)
	    {
		self::_log("exception", get_class($e) . ": " . $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTrace(), null);
	    } //end exceptionHandler()


	/**
	 * Handler for fatal errors unhandled otherwise
	 *
	 * @return void
	 */

	public static function shutdownHandler()
	    {
		$error = error_get_last();
		if ($error !== null && $error["message"] !== "")
		    {
			$backtrace = debug_backtrace();
			self::_log($error["type"], $error["message"], $error["file"], $error["line"], $backtrace, null);
		    }
	    } //end shutdownHandler()


	/**
	 * Logger of errors. Attempts to log to MySQL, failing that to file
	 *
	 * @param string $type      The level of the error raised
	 * @param string $message   The error message
	 * @param string $file      The filename that the error was raised in
	 * @param int    $line      The line number the error was raised at
	 * @param array  $backtrace Backtrace to the error
	 * @param mixed  $context   Array which points to the active symbol table at the point where the error occurred
	 *
	 * @return void
	 *
	 * @requiredconst LOGGER_HOST "" Logger database host
	 * @requiredconst LOGGER_DB   "" Logger database name
	 * @requiredconst LOGGER_USER "" Logger database user name
	 * @requiredconst LOGGER_PASS "" Logger database password
	 *
	 * @untranslatable LOGGER_HOST
	 * @untranslatable LOGGER_DB
	 * @untranslatable LOGGER_USER
	 * @untranslatable LOGGER_PASS
	 * @untranslatable SET NAMES 'UTF8'
	 * @untranslatable ROLLBACK
	 * @untranslatable b
	 */

	private static function _log($type, $message, $file, $line, array $backtrace, $context)
	    {
		$backtrace = self::_cleantrace($backtrace);

		if (defined("LOGGER_HOST") === true && defined("LOGGER_DB") === true && defined("LOGGER_USER") === true && defined("LOGGER_PASS") === true)
		    {
			$link = mysqli_connect(LOGGER_HOST, LOGGER_USER, LOGGER_PASS);
		    }
		else
		    {
			$link = false;
		    }

		if ($link === false)
		    {
			self::_logFile($type, $message, $file, $line, $backtrace, $context);
		    }
		else
		    {
			if (mysqli_select_db($link, LOGGER_DB) === true)
			    {
				self::_createTables($link);

				$serializedbacktrace = serialize($backtrace);
				$serializedcontext   = serialize($context);
				$error               = array(
							"type"    => $type,
							"message" => $message,
							"file"    => $file,
							"line"    => $line,
						       );

				$errorhash     = md5(serialize($error));
				$backtracehash = md5($serializedbacktrace);
				$contexthash   = md5($serializedcontext);

				mysqli_query($link, "SET NAMES 'UTF8'");
				mysqli_query($link, "START TRANSACTION");
				mysqli_query($link,
				    "INSERT INTO `Errors` SET " .
				    "`id` = '" . mysqli_real_escape_string($link, $errorhash) . "', " .
				    "`Type` = '" . mysqli_real_escape_string($link, $type) . "', " .
				    "`Message` = '" . mysqli_real_escape_string($link, $message) . "', " .
				    "`File` = '" . mysqli_real_escape_string($link, $file) . "', " .
				    "`Line` = '" . mysqli_real_escape_string($link, $line) . "'"
				);
				$stmt = mysqli_prepare($link,
				    "INSERT INTO `Backtraces` SET " .
				    "`id` = '" . mysqli_real_escape_string($link, $backtracehash) . "', " .
				    "`Backtrace` = ?"
				);
				mysqli_stmt_bind_param($stmt, "b", $serializedbacktrace);
				$stmt->send_long_data(0, $serializedbacktrace);
				mysqli_stmt_execute($stmt);
				$stmt = mysqli_prepare($link,
				    "INSERT INTO `Contexts` SET " .
				    "`id` = '" . mysqli_real_escape_string($link, $contexthash) . "', " .
				    "`Context` = ?"
				);
				mysqli_stmt_bind_param($stmt, "b", $serializedcontext);
				$stmt->send_long_data(0, $serializedcontext);
				mysqli_stmt_execute($stmt);
				$result = mysqli_query($link,
				    "INSERT INTO `Log` SET " .
				    "`DateTime` = NOW(), " .
				    "`Error` = '" . mysqli_real_escape_string($link, $errorhash) . "', " .
				    "`Backtrace` = '" . mysqli_real_escape_string($link, $backtracehash) . "', " .
				    "`Context` = '" . mysqli_real_escape_string($link, $contexthash) . "'"
				);

				if ($result === false)
				    {
					mysqli_query($link, "ROLLBACK");
					self::_logFile($type, $message, $file, $line, $backtrace, $context);
				    }
				else
				    {
					mysqli_query($link, "COMMIT");
				    }
			    }
			else
			    {
				self::_logFile($type, $message, $file, $line, $backtrace, $context);
			    } //end if

			mysqli_close($link);
		    } //end if
	    } //end _log()


	/**
	 * Create tables if needed
	 *
	 * @param mysqli $link MySQLi link resource
	 *
	 * @return void
	 */

	private static function _createTables(mysqli $link)
	    {
		mysqli_query(
		    $link,
		    "CREATE TABLE IF NOT EXISTS `Errors` (" .
		    "`id` char(32) NOT NULL," .
		    "`Type` char(64) NOT NULL," .
		    "`Message` longtext NOT NULL," .
		    "`File` text NOT NULL," .
		    "`Line` int(11) NOT NULL," .
		    "PRIMARY KEY (`id`)" .
		    ") ENGINE=InnoDB DEFAULT CHARSET=utf8;"
		);
		mysqli_query(
		    $link,
		    "CREATE TABLE IF NOT EXISTS `Backtraces` (" .
		    "`id` char(32) NOT NULL," .
		    "`Backtrace` longblob NOT NULL," .
		    "PRIMARY KEY (`id`)" .
		    ") ENGINE=InnoDB DEFAULT CHARSET=utf8;"
		);
		mysqli_query(
		    $link,
		    "CREATE TABLE IF NOT EXISTS `Contexts` (" .
		    "`id` char(32) NOT NULL," .
		    "`Context` longblob NOT NULL," .
		    "PRIMARY KEY (`id`)" .
		    ") ENGINE=InnoDB DEFAULT CHARSET=utf8;"
		);
		mysqli_query(
		    $link,
		    "CREATE TABLE IF NOT EXISTS `Log` (" .
		    "`DateTime` datetime NOT NULL," .
		    "`Error` char(32) NOT NULL," .
		    "`Backtrace` char(32) NOT NULL," .
		    "`Context` char(32) NOT NULL," .
		    "KEY `Error` (`Error`(32))," .
		    "FOREIGN KEY (`Error`) REFERENCES `Errors`(`id`) ON DELETE CASCADE, " .
		    "FOREIGN KEY (`Backtrace`) REFERENCES `Backtraces`(`id`) ON DELETE RESTRICT, " .
		    "FOREIGN KEY (`Context`) REFERENCES `Contexts`(`id`) ON DELETE RESTRICT" .
		    ") ENGINE=InnoDB DEFAULT CHARSET=utf8;"
		);
	    } //end _createTables()


	/**
	 * Logger of errors to a text file
	 *
	 * @param string $type      The level of the error raised
	 * @param string $message   The error message
	 * @param string $file      The filename that the error was raised in
	 * @param int    $line      The line number the error was raised at
	 * @param array  $backtrace Backtrace to the error
	 * @param array  $context   Array which points to the active symbol table at the point where the error occurred
	 *
	 * @return void
	 *
	 * @optionalconst LOGGER_FILE "/tmp/php-errorlog" File for PHP error log
	 *
	 * @untranslatable LOGGER_FILE
	 * @untranslatable /var/log/php-errorlog
	 * @untranslatable a
	 * @untranslatable Y-m-d H:i:s
	 */

	private static function _logFile($type, $message, $file, $line, array $backtrace, array $context)
	    {
		$filename = ((defined("LOGGER_FILE") === true) ? LOGGER_FILE : "/var/log/php-errorlog");
		$f        = fopen($filename, "a");
		if ($f !== false)
		    {
			fwrite(
			    $f,
			    date("Y-m-d H:i:s") . "\t" .
			    $type . "\t" .
			    $message . "\t" .
			    $file . "\t" .
			    $line . "\t" .
			    urlencode(serialize($backtrace)) . "\t" .
			    urlencode(serialize($context)) . "\n"
			);
			fclose($f);
		    }
	    } //end _logFile()


	/**
	 * Get safe debug backtrace - without objects or arguments which cannot be serialized
	 *
	 * @param mixed $backtrace Unsanitized original backtrace
	 *
	 * @return array Sanitized debug backtrace
	 */

	private static function _cleantrace($backtrace)
	    {
		foreach ($backtrace as $idx => $trace)
		    {
			if (isset($trace["object"]) === true && self::_serializable($trace["object"]) === false)
			    {
				unset($backtrace[$idx]["object"]);
			    }

			if (isset($trace["args"]) === true && self::_serializable($trace["args"]) === false)
			    {
				unset($backtrace[$idx]["args"]);
			    }
		    }

		return $backtrace;
	    } //end _cleantrace()


	/**
	 * Check if data is serializable
	 *
	 * @param mixed $data Data to check
	 *
	 * @return boolean True if data can be serialized, false if cannot
	 */

	private static function _serializable($data)
	    {
		try
		    {
			serialize($data);
			$serializable = true;
		    }
		catch (Exception $e)
		    {
			$serializable = false;
		    }

		return $serializable;
	    } //end _serializable()


    } //end class

if (defined("LOGGER_DISABLE") === false)
    {
	set_error_handler(array(ErrorLogger::CLASS, "errorHandler"));
	set_exception_handler(array(ErrorLogger::CLASS, "exceptionHandler"));
	register_shutdown_function(array(ErrorLogger::CLASS, "shutdownHandler"));
	error_reporting(0);
	ini_set("display_errors", "0");
    }

?>
