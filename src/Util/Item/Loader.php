<?php
declare(strict_types=1);

namespace GreenLinks\Cache\Util\Item;

use GreenLinks\Cache\Util\CurrentTime;
use GreenLinks\Cache\Util\FileManager;
use GreenLinks\Cache\Item;

use function array_reduce;
use function array_values;

class Loader
{
    private FileManager $fileManager;

    public function __construct(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    public function load(string $file): array
    {
        $items  = $this->fileManager->read($file);
        $update = false;

        $hits = array_reduce($items, function (array $items, Item $item) use (&$update): array {
            $expiration = $item->getExpiration();

            if ((null === $expiration) || ($expiration > CurrentTime::getNow())) {
                $items[$item->getKey()] = $item;
            } else {
                $update = true;
            }

            return $items;
        }, []);

        if ($update) {
            if (empty($hits)) {
                $this->fileManager->delete($file);
            } else {
                $this->fileManager->writeTo($file, ...array_values($hits));
            }
        }

        return $hits;
    }
}
