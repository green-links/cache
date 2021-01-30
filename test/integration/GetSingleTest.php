<?php
declare(strict_types=1);

namespace Test\Integration;

use GreenLinks\Cache\Item;

use DateTime;

class GetSingleTest extends IntegrationTest
{
    /**
     * @test
     */
    public function it_should_get_an_item_that_does_not_exist(): void
    {
        $result = $this->fetchPool()->getItem('KEY');

        $this->assertInstanceOf(Item::class, $result);
        $this->assertSame('KEY', $result->getKey());
        $this->assertFalse($result->isHit());
        $this->assertNull($result->get());
        $this->assertNull($result->getExpiration());
    }

    /**
     * @test
     */
    public function it_should_get_an_item_that_does_exist(): void
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

        $item2 = $pool
            ->refresh()
            ->getItem('KEY');

        $this->assertInstanceOf(Item::class, $item2);
        $this->assertSame('KEY', $item2->getKey());
        $this->assertTrue($item2->isHit());
        $this->assertSame('VALUE', $item2->get());
        $this->assertSame($expiration->getTimestamp(), $item2->getExpiration());
    }
}
