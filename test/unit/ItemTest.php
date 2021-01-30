<?php
declare(strict_types=1);

namespace Test\Unit;

use GreenLinks\Cache\Exception\InvalidArgumentException;
use GreenLinks\Cache\Item;

use DateInterval;
use DateTime;

use function strtotime;

class ItemTest extends UnitTestCase
{
    private Item $item;

    /**
     * @test
     */
    public function it_should_return_a_key(): void
    {
        $key = $this->item->getKey();

        $this->assertSame('KEY_HIT', $key);
    }

    /**
     * @test
     */
    public function it_should_return_a_value(): void
    {
        $value = $this->item->get();

        $this->assertSame('VALUE', $value);
    }

    /**
     * @test
     */
    public function it_should_return_true_if_cache_hit(): void
    {
        $result = $this->item->isHit();

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function it_should_return_false_if_cache_miss(): void
    {
        $item   = Item::createMiss('KEY');
        $result = $item->isHit();

        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function it_should_set_a_value(): void
    {
        $result1 = $this->item->set('NEW VALUE');

        $this->assertSame($this->item, $result1);

        $result2 = $this->item->get();

        $this->assertSame('NEW VALUE', $result2);
    }

    /**
     * @test
     * @dataProvider providerExpiresAt
     */
    public function it_should_set_an_expiry($expected, $expiration): void
    {
        $result1 = $this->item->expiresAt($expiration);

        $this->assertSame($this->item, $result1);

        $result2 = $this->item->getExpiration();

        $this->assertSame($expected, $result2);
    }

    public function providerExpiresAt(): array
    {
        $timestamp = strtotime('2021-01-01 00:00:30');
        $dateTime = (new DateTime)->setTimestamp($timestamp);

        return [
            'DateTime object' => [$timestamp, $dateTime],
            'null'            => [null, null],
        ];
    }

    /**
     * @test
     */
    public function it_should_not_set_expiration_in_the_past(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $timestamp = strtotime('2020-12-31 23:59:59');
        $dateTime  = (new DateTime)->setTimestamp($timestamp);

        $this->item->expiresAt($dateTime);
    }

    /**
     * @test
     */
    public function it_should_not_set_expires_at_with_an_invalid_type(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->item->expiresAt('INVALID');
    }

    /**
     * @test
     * @dataProvider providerAfter
     */
    public function it_should_set_an_expiry_after($expected, $expireAfter): void
    {
        $result1 = $this->item->expiresAfter($expireAfter);

        $this->assertSame($this->item, $result1);

        $result2 = $this->item->getExpiration();

        $this->assertSame($expected, $result2);
    }

    public function providerAfter(): array
    {
        $expected = strtotime('2021-01-01 01:00:00');

        return [
            'interval' => [$expected, new DateInterval('PT3600S')],
            'integer'  => [$expected, 3600],
            'null'     => [null, null],
        ];
    }

    /**
     * @test
     */
    public function it_should_not_set_an_expiry_after_with_an_invalid_type(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->item->expiresAfter('INVALID');
    }

    /**
     * @test
     * @dataProvider providerInvalidExpireAfter
     */
    public function it_should_not_set_expires_after_if_the_time_will_be_in_the_past($expireAfter): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->item->expiresAfter($expireAfter);
    }

    public function providerInvalidExpireAfter(): array
    {
        $interval = new DateInterval('PT3600S');
        $interval->invert = true;

        return [
            'inverted interval' => [$interval],
            'negative integer'  => [-1],
            'zero'              => [0],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $key        = base64_encode('KEY_HIT');
        $value      = base64_encode(serialize('VALUE'));
        $expiration = strtotime('2021-01-01 00:00:30');

        $this->item = Item::createHit($key, $value, $expiration);
    }
}
