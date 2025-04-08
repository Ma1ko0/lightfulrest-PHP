<?php

namespace App;

class Logger
{
	/**
	 * logging
	 *
	 * @param string $message
	 * @param int $type
	 * @param boolean $backtrace
	 */
	public static function logging($message, $type = ERROR, $backtrace = false) {
		global $logginglevel;

		if ($logginglevel & $type === 0) {
			return;
		}

		switch ($type) {
			case ERROR:
				$typ = "ERROR";
				break;
			case WARN:
				$typ = "WARN";
				break;
			case INFO:
				$typ = "INFO";
				break;
			default:
				$typ = "UNKNOWN";
		}

		$message = str_replace("\r\n", " ", $message);
		$line = date("Y.m.d H:i:s") . "\t" . $typ . "\t" . session_id() . "\t" . $message;

		if ($backtrace) {
			$trace = debug_backtrace();
			$datei = $trace[0]["file"];
			$zeile = $trace[0]["line"];
			$line .= "\t" . $datei . ":" . $zeile;
		}

		$loggingfile = __DIR__ . "/../../logs/" . date("Y/Ymd") . ".log";

		// create folder
		$folder = dirname($loggingfile);

		if (!is_dir($folder)) {
			mkdir($folder, 0777, true);
		}
		file_put_contents($loggingfile, $line . "\r\n", FILE_APPEND | LOCK_EX);
	}
}
