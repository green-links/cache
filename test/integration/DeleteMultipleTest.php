<?php
declare(strict_types=1);

namespace Test\Integration;

use GreenLinks\Cache\Item;

use DateTime;

class DeleteMultipleTest extends IntegrationTest
{
    /**
     * @test
     */
    public function it_should_delete_multiple(): void
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

        $result = $pool
            ->refresh()
            ->deleteItems(['KEY1', 'KEY2']);

        $this->assertTrue($result);
    }
}
