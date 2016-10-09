<?php
namespace Loader;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

class ErrorHandler implements LoggerAwareInterface
{
	/** @var LoggerInterface $logger */
	private $logger;

	public function __construct() {
		$this->logger = new NullLogger();
	}

	/**
	 * Sets a logger instance on the object.
	 *
	 * @param LoggerInterface $logger
	 *
	 * @return null
	 */
	public function setLogger(LoggerInterface $logger) {
		$this->logger = $logger;
	}

	/**
	 * Hooks this error handler as php error handler.
	 */
	public function hook() {
		set_error_handler(array($this, 'handleError'));
	}

	/**
	 * Callback called when error occurs in php code.
	 * @param $number
	 * @param $msg
	 * @param $file
	 * @param $line
	 * @param $vars
	 */
	public function handleError($number, $msg, $file, $line, $vars) {
		$level = LogLevel::ERROR;

		switch ($number) {
			case E_WARNING:
			case E_USER_WARNING:
				$level = LogLevel::WARNING;
				break;
			case E_NOTICE:
			case E_USER_NOTICE:
				$level = LogLevel::NOTICE;
				break;
		}

		$this->logger->log($level, "$msg ($file:$line)");

		if ($number === E_ERROR || $number == E_USER_ERROR) {
			foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $trace) {
				$signature = @$trace['function'];
				if (isset($trace['class'])) {
					$signature = "{$trace['class']}::$signature";
				}

				$path = "-:-";
				if (isset($trace['file']))
					$path = "{$trace['file']}:{$trace['line']}";

				$this->logger->log($level, "\t$signature() $path");
			}
		}
	}
}