<?php
declare(strict_types=1);

namespace Test\Integration;

use GreenLinks\Cache\Item;
use GreenLinks\Cache\Pool;

use DateTime;

class SaveDeferredTest extends IntegrationTest
{
    /**
     * @test
     */
    public function it_should_defer_saving_of_items(): void
    {
        $pool1  = $this->fetchPool();
        $item1 = $pool1->getItem('KEY');

        $this->assertInstanceOf(Item::class, $item1);
        $this->assertSame('KEY', $item1->getKey());
        $this->assertFalse($item1->isHit());
        $this->assertNull($item1->get());
        $this->assertNull($item1->getExpiration());

        $expiration = new DateTime('2021-01-01 01:00:00');

        $item1
            ->set('VALUE1')
            ->expiresAt($expiration);

        $this->assertTrue($pool1->save($item1));

        $item2 = $pool1
            ->getItem('KEY')
            ->set('VALUE2');

        $pool1->saveDeferred($item2);

        $pool2 = Pool::create(__DIR__ . '/../test-cache');
        $value = $pool2->getItem('KEY')->get();

        $this->assertSame('VALUE1', $value);

        $pool1->commit();

        $item3 = $pool2
            ->refresh()
            ->getItem('KEY');

        $this->assertInstanceOf(Item::class, $item3);
        $this->assertSame('KEY', $item3->getKey());
        $this->assertTrue($item3->isHit());
        $this->assertSame('VALUE2', $item3->get());
        $this->assertSame($expiration->getTimestamp(), $item3->getExpiration());
    }
}
