<?php
declare(strict_types=1);

namespace GreenLinks\Cache;

use GreenLinks\Cache\Exception\InvalidArgumentException;
use GreenLinks\Cache\Util\CurrentTime;

use Psr\Cache\CacheItemInterface;

use DateTimeInterface;
use DateInterval;

use function base64_decode;
use function unserialize;
use function gettype;
use function sprintf;
use function is_int;

class Item implements CacheItemInterface
{
    private string $key;

    /**
     * @var mixed
     */
    private $value;

    private ?int $expiration;

    private bool $isHit;

    public static function createHit(string $key, string $value, ?int $expiration): self
    {
        $value = unserialize(base64_decode($value));
        $key   = base64_decode($key);

        return new self($key, $value, $expiration, true);
    }

    public static function createMiss(string $key): self
    {
        return new self($key, null, null, false);
    }

    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function get()
    {
        return $this->value;
    }

    public function isHit(): bool
    {
        return $this->isHit;
    }

    public function set($value): self
    {
        $this->value = $value;

        return $this;
    }

    public function expiresAt($expiration): self
    {
        switch (true) {
            case $expiration instanceof DateTimeInterface:
                $expiration = $expiration->getTimestamp();

                if (CurrentTime::getNow() >= $expiration) {
                    throw new InvalidArgumentException('Expiration must be in the future');
                }
                break;

            case null === $expiration:
                break;

            default:
                throw new InvalidArgumentException(sprintf(
                    '%s::%s argument must be an object implementing the %s interface or null, got "%s".',
                    __CLASS__,
                    __FUNCTION__,
                    DateTimeInterface::class,
                    gettype($expiration)
                ));
        }

        $this->expiration = $expiration;

        return $this;
    }

    public function expiresAfter($time): self
    {
        switch (true) {
            case $time instanceof DateInterval:
                if ($time->invert) {
                    throw new InvalidArgumentException('Expires after cannot be in the past.');
                }

                $time = $time->s;
                break;

            case is_int($time):
                if ($time <= 0) {
                    throw new InvalidArgumentException('Expires after must be in the future.');
                }
                break;

            case null === $time:
                $this->expiration = null;

                return $this;

            default:
                throw new InvalidArgumentException(sprintf(
                    '%s::%s argument must be a %s object, integer, or null, got "%s".',
                    __CLASS__,
                    __FUNCTION__,
                    DateInterval::class,
                    gettype($time)
                ));
        }

        $this->expiration = CurrentTime::getNow() + $time;

        return $this;
    }

    public function getExpiration(): ?int
    {
        return $this->expiration;
    }

    private function __construct(string $key, $value, ?int $expiration, bool $isHit)
    {
        $this->key        = $key;
        $this->value      = $value;
        $this->expiration = $expiration;
        $this->isHit      = $isHit;
    }
}
