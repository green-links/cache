<?php
declare(strict_types=1);

namespace Test\Unit\Util\Item;

use GreenLinks\Cache\Util\FileManager;
use GreenLinks\Cache\Util\Item\Loader;
use GreenLinks\Cache\Util\Item\Store;
use GreenLinks\Cache\Item;

use Test\Unit\UnitTestCase;

use Prophecy\Prophecy\ObjectProphecy;

class StoreTest extends UnitTestCase
{
    private ObjectProphecy $fileManager;

    private ObjectProphecy $loader;

    private Store $store;

    /**
     * @test
     */
    public function it_should_get_an_item(): void
    {
        $item1 = $this->newItem('KEY1');
        $item2 = $this->newItem('KEY2');

        $this
            ->loader
            ->load('cache_564635948.php')
            ->willReturn([
                'KEY1' => $item1,
                'KEY2' => $item2
            ]);

        $result = $this->store->get('KEY1');

        $this->assertSame($item1, $result);
    }

    /**
     * @test
     */
    public function it_should_return_a_cache_miss_item_when_loader_could_not_find_item(): void
    {
        $item2 = $this->newItem('KEY2');

        $this
            ->loader
            ->load('cache_564635948.php')
            ->willReturn([
                'KEY2' => $item2,
            ]);

        $result = $this->store->get('KEY1');

        $this->assertInstanceOf(Item::class, $result);
        $this->assertSame('KEY1', $result->getKey());
        $this->assertFalse($result->isHit());
        $this->assertNull($result->get());
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function it_should_set_an_item(): void
    {
        $item1 = $this->newItem('KEY1');
        $item2 = $this->newItem('KEY2');

        $this
            ->loader
            ->load('cache_3098474646.php')
            ->willReturn([
                'KEY1' => $item1
            ]);

        $this
            ->fileManager
            ->writeTo('cache_3098474646.php', $item1, $item2)
            ->shouldBeCalled();

        $this->store->set($item2);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function it_should_remove_an_item_from_a_file(): void
    {
        $item1 = $this->newItem('KEY1');
        $item2 = $this->newItem('KEY2');

        $this
            ->loader
            ->load('cache_564635948.php')
            ->willReturn([
                'KEY1' => $item1,
                'KEY2' => $item2
            ]);

        $this
            ->fileManager
            ->writeTo('cache_564635948.php', $item2)
            ->shouldBeCalled();

        $this->store->remove('KEY1');
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function it_should_delete_a_file_if_removing_the_last_item_from_it(): void
    {
        $item1 = $this->newItem('KEY1');

        $this
            ->loader
            ->load('cache_564635948.php')
            ->willReturn([
                'KEY1' => $item1,
            ]);

        $this
            ->fileManager
            ->delete('cache_564635948.php')
            ->shouldBeCalled();

        $this->store->remove('KEY1');
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function it_should_clear_the_cache(): void
    {
        $this
            ->fileManager
            ->clear()
            ->shouldBeCalled();

        $this->store->removeAll();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->fileManager = $this->newMock(FileManager::class);
        $this->loader      = $this->newMock(Loader::class);
        $this->store       = new Store($this->loader->reveal(), $this->fileManager->reveal());
    }
}
