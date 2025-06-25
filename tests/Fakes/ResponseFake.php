<?php
namespace App;

class Response
{
    public static function json($data, int $statusCode = 200): void {}
    public static function error(string $message, int $statusCode = 400): void {
        throw new \RuntimeException($message, $statusCode);
    }
}
