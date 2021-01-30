<?php
declare(strict_types=1);

namespace Test\Integration;

use GreenLinks\Cache\Item;

use DateTime;

class HasTest extends IntegrationTest
{
    /**
     * @test
     */
    public function it_returns_true_if_an_item_is_in_the_cache(): void
    {
        $pool  = $this->fetchPool();
        $item1 = $pool->getItem('KEY');

        $this->assertInstanceOf(Item::class, $item1);
        $this->assertSame('KEY', $item1->getKey());
        $this->assertFalse($item1->isHit());
        $this->assertNull($item1->get());
        $this->assertNull($item1->getExpiration());

        $expiration = new DateTime('2021-01-01 01:00:00');

        $item1
            ->set('VALUE')
            ->expiresAt($expiration);

        $this->assertTrue($pool->save($item1));

        $result = $pool
            ->refresh()
            ->hasItem('KEY');

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function it_returns_false_if_an_item_is_not_in_the_cache(): void
    {
        $result = $this->fetchPool()->hasItem('KEY');

        $this->assertFalse($result);
    }
}
