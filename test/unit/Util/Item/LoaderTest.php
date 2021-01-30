<?php
declare(strict_types=1);

namespace Test\Unit\Util\Item;

use GreenLinks\Cache\Util\FileManager;
use GreenLinks\Cache\Util\Item\Loader;

use Test\Unit\UnitTestCase;

use Prophecy\Prophecy\ObjectProphecy;

use function strtotime;

class LoaderTest extends UnitTestCase
{
    private ObjectProphecy $fileManager;

    private Loader $loader;

    /**
     * @test
     */
    public function it_should_load_keys(): void
    {
        $item1 = $this->newItem('KEY1');
        $item2 = $this->newItem('KEY2');

        $this
            ->fileManager
            ->read('PATH')
            ->willReturn([
                'KEY1' => $item1,
                'KEY2' => $item2,
            ]);

        $result = $this->loader->load('PATH');

        $this->assertSame($result, [
            'KEY1' => $item1,
            'KEY2' => $item2,
        ]);
    }

    /**
     * @test
     */
    public function it_should_return_an_empty_array_if_no_keys_could_be_loaded(): void
    {
        $this
            ->fileManager
            ->read('PATH')
            ->willReturn([]);

        $result = $this->loader->load('PATH');

        $this->assertSame([], $result);
    }

    /**
     * @test
     */
    public function it_should_rewrite_a_key_file_if_some_keys_are_invalid(): void
    {
        $item1 = $this->newItem('KEY1', 'VALUE1', strtotime('2021-01-01 00:00:01'));
        $item2 = $this->newItem('KEY2', 'VALUE2', strtotime('2020-12-31 59:59:59'));

        $this
            ->fileManager
            ->read('PATH')
            ->willReturn([
                'KEY1' => $item1,
                'KEY2' => $item2
            ]);

        $this
            ->fileManager
            ->writeTo('PATH', $item1)
            ->shouldBeCalled();

        $result = $this->loader->load('PATH');

        $this->assertSame([
            'KEY1' => $item1,
        ], $result);
    }

    public function it_should_delete_a_key_file_if_all_keys_are_expired(): void
    {
        $item1 = $this->newItem('KEY1', 'VALUE1', strtotime('2020-12-31 59:59:59'));
        $item2 = $this->newItem('KEY2', 'VALUE2', strtotime('2020-12-31 59:59:59'));

        $this
            ->fileManager
            ->read('PATH')
            ->willReturn([
                'KEY1' => $item1,
                'KEY2' => $item2
            ]);

        $this
            ->fileManager
            ->delete('PATH')
            ->shouldBeCalled();

        $result = $this->loader->load('PATH');

        $this->assertSame([], $result);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->fileManager = $this->newMock(FileManager::class);
        $this->loader      = new Loader($this->fileManager->reveal());
    }
}
