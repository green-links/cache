<?php
declare(strict_types=1);

namespace Test\Unit;

use GreenLinks\Cache\Util\CurrentTime;
use GreenLinks\Cache\Item;

use PHPUnit\Framework\TestCase as BaseTestCase;

use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;

use function strtotime;

abstract class UnitTestCase extends BaseTestCase
{
    private Prophet $prophet;

    protected function newItem(
        string $key = 'KEY',
        $value = 'VALUE',
        $expiration = null,
        bool $isHit = true
    ): Item {
        $item = $this->newMock(Item::class);

        $item
            ->getKey()
            ->willReturn($key);

        $item
            ->get()
            ->willReturn($value);

        $item
            ->getExpiration()
            ->willReturn($expiration);

        $item
            ->isHit()
            ->willReturn($isHit);

        return $item->reveal();
    }

    protected function newMock(string $classPath): ObjectProphecy
    {
        return $this->prophet->prophesize($classPath);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $now = strtotime('2021-01-01 00:00:00');

        CurrentTime::setNow($now);

        $this->prophet = new Prophet;
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->prophet->checkPredictions();
    }
}
