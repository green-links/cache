<?php
declare(strict_types=1);

namespace GreenLinks\Cache;

use GreenLinks\Cache\Exception\InvalidArgumentException;
use GreenLinks\Cache\Exception\GeneralException;
use GreenLinks\Cache\Util\CurrentTime;
use GreenLinks\Cache\Util\FileManager;
use GreenLinks\Cache\Util\Item\Loader;
use GreenLinks\Cache\Util\Item\Store;

use GreenLinks\FileSystem\SrcManager;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\CacheItemInterface;

use Throwable;

use function array_reduce;
use function class_exists;
use function array_walk;
use function array_map;
use function get_class;
use function is_object;
use function is_string;
use function sprintf;

class Pool implements CacheItemPoolInterface
{
    private const CLASSES = [
        CurrentTime::class => __DIR__ . '/Util/CurrentTime.php',
        FileManager::class => __DIR__ . '/Util/FileManager.php',
        Loader::class      => __DIR__ . '/Util/Item/Loader.php',
        Store::class       => __DIR__ . '/Util/Item/Store.php',
    ];

    private static bool $init = false;

    private array $deferred = [];

    private Store $store;

    public static function create(string $rootDir): self
    {
        // Let's give the autoloader a break by loading the classes
        // that are always required ourselves.
        if (!self::$init) {
            foreach (self::CLASSES as $classPath => $filePath) {
                if (!class_exists($classPath, false)) {
                    require $filePath;
                }
            }

            self::$init = true;
        }

        $srcManager  = new SrcManager($rootDir);
        $fileManager = new FileManager($srcManager);
        $loader      = new Loader($fileManager);
        $store       = new Store($loader, $fileManager);

        return new self($store);
    }

    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    public function getItems(array $keys = []): array
    {
        $this->assertStringArray($keys);

        return array_map(function (string $key) {
            return $this->get($key);
        }, $keys);
    }

    public function hasItem($key): bool
    {
        return $this->getItem($key)->isHit();
    }

    public function getItem($key): CacheItemInterface
    {
        $this->assertString($key);

        return $this->get($key);
    }

    public function deleteItems(array $keys): bool
    {
        $this->assertStringArray($keys);

        return array_reduce($keys, function (bool $success, string $key): bool {
            try {
                $this->store->remove($key);
            } catch (Throwable $e) {
                return false;
            }

            return $success;
        }, true);
    }

    public function deleteItem($key): bool
    {
        $this->assertString($key);

        try {
            $this->store->remove($key);
        } catch (Throwable $e) {
            return false;
        }

        return true;
    }

    public function save(CacheItemInterface $item): bool
    {
        $item = $this->assertItem($item);

        try {
            $this->store->set($item);
        } catch (Throwable $e) {
            return false;
        }

        return true;
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        $item = $this->assertItem($item);

        $this->deferred[] = $item;

        return true;
    }

    public function commit(): bool
    {
        try {
            array_walk($this->deferred, function (Item $item): void {
                $this->store->set($item);
            });
        } catch (Throwable $e) {
            return false;
        }

        return true;
    }

    public function clear(): bool
    {
        try {
            $this->store->removeAll();
        } catch (Throwable $e) {
            return false;
        }

        return true;
    }

    public function refresh(): self
    {
        $this->store->refresh();

        return $this;
    }

    private function get(string $key)
    {
        try {
            return $this->store->get($key);
        } catch (Throwable $e) {
            throw new GeneralException(sprintf(
                'An unexpected error has occurred: "%s"',
                $e->getMessage()
            ), 0, $e);
        }
    }

    private function assertItem(CacheItemInterface $item): Item
    {
        if (!$item instanceof Item) {
            throw new InvalidArgumentException(sprintf(
                'Expected instance of "%s", got "%s".',
                Item::class,
                get_class($item)
            ));
        }

        return $item;
    }

    private function assertStringArray(array $value): void
    {
        array_walk($value, function ($value) {
            if (!is_string($value)) {
                throw new InvalidArgumentException(sprintf(
                    'Expected array of strings, found non-string element "%s".',
                    $this->fetchType($value)
                ));
            }
        });
    }

    private function assertString($value): void
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException(sprintf(
                'Expected string, got "%s".',
                $this->fetchType($value)
            ));
        }
    }

    private function fetchType($value): string
    {
        return is_object($value) ? get_class($value) : gettype($value);
    }
}
