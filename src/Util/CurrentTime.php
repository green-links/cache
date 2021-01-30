<?php
declare(strict_types=1);

namespace GreenLinks\Cache\Util;

use function time;

/**
 * static class for setting/getting the current unix timestamp.
 *
 * Used to set the time for testing purposes.
 *     It should not be used in production.
 */
class CurrentTime
{
    private static ?int $now = null;

    public static function setNow(int $now): void
    {
        self::$now = $now;
    }

    public static function getNow(): int
    {
        return self::$now ?? self::$now = time();
    }

    private function __construct()
    {
    }
}
