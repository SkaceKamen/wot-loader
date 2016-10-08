<?php
namespace Loader\Logger;

use Psr\Log\AbstractLogger;

/**
 * Basic text based logger.
 * @package Loader\Logger
 */
class Text extends AbstractLogger
{
	public function log($level, $message, array $context = array()) {
		echo ucfirst($level) . ': ' . $this->interpolate($message, $context);
	}

	private function interpolate($message, array $context = array()) {
		// build a replacement array with braces around the context keys
		$replace = array();
		foreach ($context as $key => $val) {
			// check that the value can be casted to string
			if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
				$replace['{' . $key . '}'] = $val;
			}
		}

		// interpolate replacement values into the message and return
		return strtr($message, $replace);
	}
}