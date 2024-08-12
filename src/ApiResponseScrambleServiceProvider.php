<?php

namespace KodePandai\ApiResponseScramble;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class ApiResponseScrambleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // pass
    }

    public function boot(): void
    {
        // TODO: ability to disable auto-merge, use custom config.
        $extensions = Config::get('scramble.extensions');
        Config::set('scramble.extensions', array_merge($extensions ?: [], [
            // TODO: simplify with only one or two operation extension
            \KodePandai\ApiResponseScramble\RequestBodyOperationExtension::class,
            \KodePandai\ApiResponseScramble\ResponseDataInferExtension::class,
            \KodePandai\ApiResponseScramble\ValidateExceptionInferExtension::class,
            \KodePandai\ApiResponseScramble\ResponseInvalidInferExtension::class,
            \KodePandai\ApiResponseScramble\ValidateExceptionToResponseExtension::class,
            \KodePandai\ApiResponseScramble\ResponseInvalidExceptionToResponseExtension::class,
        ]));
    }
}
