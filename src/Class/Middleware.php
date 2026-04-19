<?php

declare(strict_types=1);

use App\Request;

abstract class Middleware
{
    abstract public function handle(Request $request, callable $next);
}
