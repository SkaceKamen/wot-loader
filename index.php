<?php
set_time_limit(0);

require __DIR__ . '/vendor/autoload.php';

function e($number, $msg, $file, $line, $vars) {
	$type = $number;
	switch($number) {
		case E_ERROR:
		case E_USER_ERROR: $type = "error"; break;
		case E_WARNING:
		case E_USER_WARNING: $type = "warning"; break;
		case E_NOTICE:
		case E_USER_NOTICE: $type = "notice"; break;
	}
	
	echo "$type: $msg ($file:$line)\r\n";
	debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
	echo "\r\n";
}
set_error_handler('e');

$reader = new Loader\Reader(new Loader\Config\Reader('./values.php'));
$reader->run($_GET);