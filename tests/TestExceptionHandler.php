<?php

namespace KodePandai\ApiResponseScramble\Tests;

use KodePandai\ApiResponse\ApiExceptionHandler;
use Orchestra\Testbench\Exceptions\Handler;
use Throwable;

class TestExceptionHandler extends Handler
{
    public function render($request, Throwable $e)
    {
        return ApiExceptionHandler::render($e, $request);
    }
}
