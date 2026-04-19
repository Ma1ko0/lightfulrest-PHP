<?php

declare(strict_types=1);

namespace App;

if (!defined("NONE")) {
    define("NONE", 1);
}
if (!defined("ERROR")) {
    define("ERROR", 2);
}
if (!defined("WARN")) {
    define("WARN", 4);
}
if (!defined("INFO")) {
    define("INFO", 8);
}

class Logger
{
    private static string $logDir = __DIR__ . '/../../logs';
    private static int $level = ERROR | WARN | INFO;

    public static function setLevel(int $level): void
    {
        self::$level = $level;
    }

    public function logging(string $message, int $type = ERROR, bool $backtrace = false): void
    {
        if ((self::$level & $type) === 0) {
            return;
        }

        // Rotate logs once per day
        static $rotatedToday = false;
        if (!$rotatedToday) {
            $this->rotateLogs();
            $rotatedToday = true;
        }

        $typeStr = match ($type) {
            ERROR => 'ERROR',
            WARN  => 'WARN',
            INFO  => 'INFO',
            DEBUG => 'DEBUG',
            default => 'UNKNOWN',
        };

        $message = str_replace(["\r\n", "\n"], ' ', $message);
        $session = session_status() === PHP_SESSION_ACTIVE ? session_id() : '-';

        $line = sprintf(
            "%s\t%s\t%s\t%s",
            date("Y.m.d H:i:s"),
            $typeStr,
            $session,
            $message,
        );

        if ($backtrace) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
            $line .= sprintf("\t%s:%d", $trace["file"], $trace["line"]);
        }

        $logFile = static::getLogFilePath();
        file_put_contents($logFile, $line . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    public static function setLogDirectory(string $dir): void
    {
        self::$logDir = $dir;
    }
    public function getLogFilePath(): string
    {
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0777, true);
        }
        return self::$logDir . '/' . date('Y-m-d') . '.log';
    }

    private function rotateLogs(): void
    {
        $files = glob(self::$logDir . '/*.log');
        $threshold = strtotime('-30 days');

        foreach ($files as $file) {
            if (filemtime($file) < $threshold) {
                unlink($file);
            }
        }
    }

    public static function parseLogLevel(string $logLevelString): int
    {
        $map = [
            'ERROR' => ERROR,
            'WARN'  => WARN,
            'INFO'  => INFO,
            'DEBUG' => DEBUG,
            'NONE' => NONE,
        ];

        $levels = explode(",", strtoupper($logLevelString));
        $bitmask = 0;

        foreach ($levels as $level) {
            $bitmask |= $map[trim($level)] ?? 0;
        }

        return $bitmask;
    }
}
