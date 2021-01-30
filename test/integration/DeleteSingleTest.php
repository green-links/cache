<?php
declare(strict_types=1);

namespace Test\Integration;

use GreenLinks\Cache\Item;

use DateTime;

class DeleteSingleTest extends IntegrationTest
{
    /**
     * @test
     */
    public function it_should_delete_an_item_that_exists(): void
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
            ->deleteItem('KEY');

        $this->assertFalse($pool->hasItem('KEY'));
    }

    /**
     * @test
     */
    public function it_should_delete_an_item_that_does_not_exist(): void
    {
        $pool  = $this->fetchPool();

        $item2 = $pool
            ->refresh()
            ->deleteItem('KEY');

        $this->assertFalse($pool->hasItem('KEY'));
    }
}
