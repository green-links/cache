<?php
declare(strict_types=1);

namespace Test\Integration;

use GreenLinks\Cache\Item;

use DateTime;

class GetMultipleTest extends IntegrationTest
{
    /**
     * @test
     */
    public function it_should_get_multiple_items(): void
    {
        $pool  = $this->fetchPool();
        $item1 = $pool->getItem('KEY1');

        $this->assertInstanceOf(Item::class, $item1);
        $this->assertSame('KEY1', $item1->getKey());
        $this->assertFalse($item1->isHit());
        $this->assertNull($item1->get());
        $this->assertNull($item1->getExpiration());

        $expiration = new DateTime('2021-01-01 01:00:00');

        $item1
            ->set('VALUE')
            ->expiresAt($expiration);

        $this->assertTrue($pool->save($item1));

        $results = $pool
            ->refresh()
            ->getItems(['KEY1', 'KEY2']);

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertArrayHasKey(0, $results);
        $this->assertArrayHasKey(1, $results);

        $this->assertInstanceOf(Item::class, $results[0]);
        $this->assertSame('KEY1', $results[0]->getKey());
        $this->assertTrue($results[0]->isHit());
        $this->assertSame('VALUE', $results[0]->get());
        $this->assertSame($expiration->getTimestamp(), $results[0]->getExpiration());

        $this->assertInstanceOf(Item::class, $results[1]);
        $this->assertSame('KEY2', $results[1]->getKey());
        $this->assertFalse($results[1]->isHit());
        $this->assertNull($results[1]->get());
        $this->assertNull($results[1]->getExpiration());
    }
}
