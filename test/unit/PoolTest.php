<?php
declare(strict_types=1);

namespace Test\Unit;

use GreenLinks\Cache\Exception\InvalidArgumentException;
use GreenLinks\Cache\Exception\GeneralException;
use GreenLinks\Cache\Util\Item\Store;
use GreenLinks\Cache\Pool;

use Psr\Cache\CacheItemInterface;

use Prophecy\Prophecy\ObjectProphecy;

use Error;

class PoolTest extends UnitTestCase
{
    private ObjectProphecy $store;

    private Pool $pool;

    /**
     * @test
     */
    public function it_should_get_an_item_with_a_string_key(): void
    {
        $item = $this->newItem();

        $this
            ->store
            ->get('KEY')
            ->willReturn($item);

        $result = $this->pool->getItem('KEY');

        $this->assertSame($item, $result);
    }

    /**
     * @test
     */
    public function it_should_not_get_an_item_with_a_non_string_key(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->pool->getItem(123);
    }

    /**
     * @test
     */
    public function it_should_throw_an_exception_if_an_error_occurs_when_getting_an_item(): void
    {
        $this->expectException(GeneralException::class);

        $this
            ->store
            ->get('KEY')
            ->willThrow(Error::class);

        $this->pool->getItem('KEY');
    }

    /**
     * @test
     */
    public function it_should_return_true_if_pool_has_an_item(): void
    {
        $item = $this->newItem();

        $this
            ->store
            ->get('KEY')
            ->willReturn($item);

        $result = $this->pool->hasItem('KEY');

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function it_should_return_false_if_pool_does_not_have_an_item(): void
    {
        $item = $this->newItem('KEY', 'VALUE', null, false);

        $this
            ->store
            ->get('KEY')
            ->willReturn($item);

        $result = $this->pool->hasItem('KEY');

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function it_should_throw_an_exception_if_has_item_is_called_with_a_non_string(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->pool->hasItem(123);
    }

    /**
     * @test
     */
    public function it_should_throw_an_exception_if_an_error_occurs_when_determining_if_the_pool_has_an_item(): void
    {
        $this->expectException(GeneralException::class);

        $this
            ->store
            ->get('KEY')
            ->willThrow(Error::class);

        $this->pool->hasItem('KEY');
    }

    /**
     * @test
     */
    public function it_should_get_items(): void
    {
        $item1 = $this->newItem();
        $item2 = $this->newItem();

        $this
            ->store
            ->get('KEY1')
            ->willReturn($item1);

        $this
            ->store
            ->get('KEY2')
            ->willReturn($item2);

        $result = $this->pool->getItems(['KEY1', 'KEY2']);

        $this->assertSame([$item1, $item2], $result);
    }

    /**
     * @test
     */
    public function it_should_throw_an_exception_if_getting_items_with_a_non_string_array(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->pool->getItems([['abc', 123]]);
    }

    /**
     * @test
     */
    public function it_should_throw_a_general_exception_if_an_error_occurs_while_reading_the_cache(): void
    {
        $this->expectException(GeneralException::class);

        $this
            ->store
            ->get('KEY1')
            ->willThrow(Error::class);

        $this->pool->getItems(['KEY1', 'KEY2']);
    }

    /**
     * @test
     */
    public function it_should_delete_an_item(): void
    {
        $this
            ->store
            ->remove('KEY')
            ->shouldBeCalled();

        $result = $this->pool->deleteItem('KEY');

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function it_should_throw_an_exception_if_deleting_a_non_string_key(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->pool->deleteItem(123);
    }

    /**
     * @test
     */
    public function it_should_return_false_if_an_error_occurred_while_deleting_an_item(): void
    {
        $this
            ->store
            ->remove('KEY')
            ->willThrow(Error::class);

        $result = $this->pool->deleteItem('KEY');

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function it_should_delete_items(): void
    {
        $this
            ->store
            ->remove('KEY1')
            ->shouldBeCalled();

        $this
            ->store
            ->remove('KEY2')
            ->shouldBeCalled();

        $result = $this->pool->deleteItems(['KEY1', 'KEY2']);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function it_should_throw_an_exception_if_deleting_items_with_a_non_string_array(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->pool->deleteItems(['KEY1', 123]);
    }

    /**
     * @test
     */
    public function it_should_return_false_if_an_error_occurs_when_deleting_items(): void
    {
        $this
            ->store
            ->remove('KEY1')
            ->shouldBeCalled();

        $this
            ->store
            ->remove('KEY2')
            ->willThrow(Error::class);

        $result = $this->pool->deleteItems(['KEY1', 'KEY2']);

        $this->assertFalse($result);
    }

    public function it_should_save_an_item(): void
    {
        $item = $this->newItem();

        $this
            ->store
            ->set($item)
            ->shouldBeCalled();

        $result = $this->pool->save($item);

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function it_should_throw_an_exception_if_set_is_called_with_a_non_item(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $item = $this->newMock(CacheItemInterface::class)->reveal();

        $this->pool->save($item);
    }

    /**
     * @test
     */
    public function it_should_return_false_if_an_item_could_not_be_saved(): void
    {
        $item = $this->newItem();

        $this
            ->store
            ->set($item)
            ->willThrow(Error::class);

        $result = $this->pool->save($item);

        $this->assertFalse($result);
    }

    // todo: deferred, and commit

    /**
     * @test
     */
    public function it_should_clear_a_pool(): void
    {
        $this
            ->store
            ->removeAll()
            ->shouldBeCalled();

        $result = $this->pool->clear();

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function it_should_return_false_if_the_pool_could_not_be_cleared(): void
    {
        $this
            ->store
            ->removeAll()
            ->willThrow(Error::class);

        $result = $this->pool->clear();

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function it_should_persist_and_commit_items(): void
    {
        $item1 = $this->newItem();
        $item2 = $this->newItem();

        $result1 = $this->pool->saveDeferred($item1);
        $result2 = $this->pool->saveDeferred($item2);

        $this->assertTrue($result1);
        $this->assertTrue($result2);

        $this
            ->store
            ->set($item1)
            ->shouldBeCalled();

        $this
            ->store
            ->set($item2)
            ->shouldBeCalled();

        $result3 = $this->pool->commit();

        $this->assertTrue($result3);
    }

    /**
     * @test
     */
    public function it_should_throw_an_exception_if_deferring_the_saving_of_a_non_item(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $item = $this->newMock(CacheItemInterface::class)->reveal();

        $this->pool->saveDeferred($item);
    }

    /**
     * @test
     */
    public function it_should_return_false_if_it_could_not_commit_a_cached_item(): void
    {
        $item1 = $this->newItem();
        $item2 = $this->newItem();

        $result1 = $this->pool->saveDeferred($item1);
        $result2 = $this->pool->saveDeferred($item2);

        $this->assertTrue($result1);
        $this->assertTrue($result2);

        $this
            ->store
            ->set($item1)
            ->shouldBeCalled();

        $this
            ->store
            ->set($item2)
            ->WillThrow(Error::class);

        $result3 = $this->pool->commit();

        $this->assertFalse($result3);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->store = $this->newMock(Store::class);
        $this->pool  = new Pool($this->store->reveal());
    }

    private function newStringable(string $value = 'VALUE'): Stringable
    {
        return new Stringable($value);
    }
}
