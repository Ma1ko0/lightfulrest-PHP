<?php

include_once __DIR__ . "/../src/Core/Logger.php";

use PHPUnit\Framework\TestCase;
use App\Logger;

final class LoggerTest extends TestCase
{
    private string $tempLogDir;

    protected function setUp(): void
    {
        $this->tempLogDir = __DIR__ . '/../temp_logs';
        if (!is_dir($this->tempLogDir)) {
            mkdir($this->tempLogDir, 0777, true);
        }

        Logger::setLogDirectory($this->tempLogDir);
        // Reset log level to include all by default
        Logger::setLevel(ERROR | WARN | INFO);
    }

    protected function tearDown(): void
    {
        $files = glob($this->tempLogDir . '/*.log');
        foreach ($files as $file) {
            unlink($file);
        }
        rmdir($this->tempLogDir);
    }

    public function testLoggingWritesError(): void
    {
        Logger::setLevel(ERROR);
        Logger::logging("Test error message", ERROR, false);

        $logFile = $this->tempLogDir . '/' . date('Y-m-d') . '.log';
        $this->assertFileExists($logFile);

        $contents = file_get_contents($logFile);
        $this->assertStringContainsString("ERROR", $contents);
        $this->assertStringContainsString("Test error message", $contents);
    }

    public function testLoggingDoesNotWriteWhenLevelFiltered(): void
    {
        Logger::setLevel(WARN); // ERROR not included
        Logger::logging("This should not appear", ERROR);

        $logFile = $this->tempLogDir . '/' . date('Y-m-d') . '.log';
        $this->assertFileDoesNotExist($logFile);
    }

    public function testLoggingIncludesBacktraceWhenRequested(): void
    {
        Logger::setLevel(ERROR);
        Logger::logging("Backtrace test", ERROR, true);

        $logFile = $this->tempLogDir . '/' . date('Y-m-d') . '.log';
        $contents = file_get_contents($logFile);

        // Check that backtrace info (file:line) is appended
        $this->assertMatchesRegularExpression('/\.php:\d+$/', trim($contents));
    }
}
