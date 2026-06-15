<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $compiledPath = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'fifa2026-views-'.Str::random(16);
        File::ensureDirectoryExists($compiledPath);
        config(['view.compiled' => $compiledPath]);
        $this->app->forgetInstance('blade.compiler');
    }
}
