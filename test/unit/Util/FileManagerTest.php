<?php
declare(strict_types=1);

namespace Test\Unit\Util;

use GreenLinks\Cache\Util\FileManager;
use GreenLinks\Cache\Item;

use GreenLinks\FileSystem\SrcManager;

use Test\Unit\UnitTestCase;

use Prophecy\Prophecy\ObjectProphecy;

class FileManagerTest extends UnitTestCase
{
    private ObjectProphecy $srcManager;

    private FileManager $fileManager;

    /**
     * @test
     */
    public function it_should_read_a_cache_file(): void
    {
        $this
            ->srcManager
            ->exists('PATH')
            ->willReturn(true);

        $this
            ->srcManager
            ->run('PATH')
            ->willReturn([
                $item1 = $this->newItem(),
                $item2 = $this->newItem(),
            ]);

        $result = $this->fileManager->read('PATH');

        $this->assertSame([$item1, $item2], $result);
    }

    /**
     * @test
     */
    public function it_should_return_an_empty_array_if_reading_a_file_that_does_not_exist(): void
    {
        $this
            ->srcManager
            ->exists('PATH')
            ->willReturn(false);

        $result = $this->fileManager->read('PATH');

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function it_should_write_to_a_file(): void
    {
        $item1 = $this->newItem('KEY1', 'VALUE', strtotime('2020-01-01 00:00:01'));
        $item2 = $this->newItem('KEY1', 'VALUE', strtotime('2021-01-01 00:00:01'));

        $src = '<?php return [GreenLinks\Cache\Item::createHit("S0VZMQ==", "czo'
            . '1OiJWQUxVRSI7", 1577836801),GreenLinks\Cache\Item::createHit("S0'
            . 'VZMQ==", "czo1OiJWQUxVRSI7", 1609459201),];';

        $this
            ->srcManager
            ->write('PATH', $src)
            ->shouldBeCalled();

        $this->fileManager->writeTo('PATH', $item1, $item2);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function it_should_delete_an_item_from_a_file(): void
    {
        $this
            ->srcManager
            ->exists('PATH')
            ->willReturn(true);

        $this
            ->srcManager
            ->delete('PATH')
            ->shouldBeCalled();

        $this->fileManager->delete('PATH');
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function it_should_clear_a_cache(): void
    {
        $this
            ->srcManager
            ->exists('')
            ->willReturn(true);

        $this
            ->srcManager
            ->delete('')
            ->shouldBeCalled();

        $this->fileManager->clear();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->srcManager  = $this->newMock(SrcManager::class);
        $this->fileManager = new FileManager($this->srcManager->reveal());
    }
}
