<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Don't wrap tests in database transactions - keep data persistent for inspection
        // By not using RefreshDatabase, data persists
    }
}
