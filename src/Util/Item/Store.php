<?php
declare(strict_types=1);

namespace GreenLinks\Cache\Util\Item;

use GreenLinks\Cache\Util\FileManager;
use GreenLinks\Cache\Item;

use function array_values;
use function array_merge;
use function sprintf;
use function crc32;

class Store
{
    private FileManager $fileManager;

    private Loader $loader;

    private array $items = [];

    public function __construct(Loader $loader, FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
        $this->loader      = $loader;
    }

    public function get(string $key): Item
    {
        if (!isset($this->items[$key])) {
            $file = $this->fetchFileName($key);

            $this->load($file);

            if (!isset($this->items[$key])) {
                $this->items[$key] = Item::createMiss($key);
            }
        }

        return $this->items[$key];
    }

    public function set(Item $item): void
    {
        $file  = $this->fetchFileName($item->getKey());
        $items = $this->load($file);
        $key   = $item->getKey();

        $this->items[$key] = $item;
        $items[$key]       = $item;

        $this->fileManager->writeTo($file, ...array_values($items));
    }

    public function remove(string $key): void
    {
        $file  = $this->fetchFileName($key);
        $items = $this->load($file);

        if (isset($items[$key])) {
            unset($items[$key]);

            if (empty($items)) {
                $this->fileManager->delete($file);
            } else {
                $this->fileManager->writeTo($file, ...array_values($items));
            }
        }

        if (isset($this->items[$key])) {
            unset($this->items[$key]);
        }
    }

    public function removeAll(): void
    {
        $this
            ->refresh()
            ->fileManager
            ->clear();
    }

    public function refresh(): self
    {
        $this->items = [];

        return $this;
    }

    private function load(string $file): array
    {
        $items = $this->loader->load($file);

        $this->items = array_merge($items, $this->items);

        return $items;
    }

    private function fetchFileName(string $key): string
    {
        return sprintf('cache_%d.php', crc32($key));
    }
}
