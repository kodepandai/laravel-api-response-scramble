<?php

namespace KodePandai\ApiResponseScramble\Tests;

use KodePandai\ApiResponseScramble\ApiResponseScrambleServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

/**
 * @see https://packages.tools/testbench/basic/testcase.html
 */
class TestCase extends BaseTestCase
{
    /**
     * @param  \Illuminate\Foundation\Application  $app
     */
    protected function getPackageProviders($app): array
    {
        return [ApiResponseScrambleServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \KodePandai\ApiResponseScramble\Tests\TestExceptionHandler::class,
        );
    }
}
