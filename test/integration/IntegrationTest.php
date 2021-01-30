<?php
declare(strict_types=1);

namespace Test\Integration;

use GreenLinks\Cache\Util\CurrentTime;
use GreenLinks\Cache\Pool;

use PHPUnit\Framework\TestCase;

use function strtotime;

abstract class IntegrationTest extends TestCase
{
    private Pool $pool;

    protected function setUp(): void
    {
        parent::setUp();

        $now = strtotime('2021-01-01 00:00:00');

        CurrentTime::setNow($now);

        $this->pool = Pool::create(__DIR__ . '/../test-cache');

        $this->assertTrue($this->pool->clear());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->pool->clear();
    }

    protected function fetchPool(): Pool
    {
        return $this->pool;
    }
}
