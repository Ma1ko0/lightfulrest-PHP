<?php

namespace App;

class Logger
{
	private static int $level = ERROR | WARN | INFO;

	public static function setLevel(int $level): void
	{
		self::$level = $level;
	}

	public static function logging(string $message, int $type = ERROR, bool $backtrace = false): void
	{
		if ((self::$level & $type) === 0) {
			return;
		}

		$typeStr = match ($type) {
			ERROR => 'ERROR',
			WARN  => 'WARN',
			INFO  => 'INFO',
			default => 'UNKNOWN'
		};

		$message = str_replace(["\r\n", "\n"], ' ', $message);
		$session = session_status() === PHP_SESSION_ACTIVE ? session_id() : '-';

		$line = sprintf(
			"%s\t%s\t%s\t%s",
			date("Y.m.d H:i:s"),
			$typeStr,
			$session,
			$message
		);

		if ($backtrace) {
			$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
			$line .= sprintf("\t%s:%d", $trace["file"], $trace["line"]);
		}

		$logFile = self::getLogFilePath();
		file_put_contents($logFile, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
	}

	private static function getLogFilePath(): string
	{
		$folder = __DIR__ . '/../../logs';
		if (!is_dir($folder)) {
			mkdir($folder, 0777, true);
		}
		return $folder . '/' . date('Y-m-d') . '.log';
	}
}
