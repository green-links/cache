<?php
declare(strict_types=1);

namespace GreenLinks\Cache\Util;

use GreenLinks\Cache\Item;

use GreenLinks\FileSystem\SrcManager;

use function base64_encode;
use function array_merge;
use function array_map;
use function serialize;
use function implode;
use function sprintf;

class FileManager
{
    private SrcManager $srcManager;

    public function __construct(SrcManager $srcManager)
    {
        $this->srcManager = $srcManager;
    }

    public function read(string $path): array
    {
        if (!$this->srcManager->exists($path)) {
            return [];
        }

        return $this->srcManager->run($path);
    }

    public function writeTo(string $path, Item ...$items): void
    {
        $lines = array_map(function (Item $item): string {
            $key        = base64_encode($item->getKey());
            $value      = base64_encode(serialize($item->get()));
            $expiration = $item->getExpiration();

            $line = sprintf(
                '%s::createHit("%s", "%s", %d),',
                Item::class,
                $key,
                $value,
                $expiration
            );

            return $line;
        }, $items);

        $start = '<?php return [';
        $end   = '];';
        $lines = array_merge([$start], $lines, [$end]);
        $src   = implode('', $lines);

        $this->srcManager->write($path, $src);
    }

    public function delete(string $path): void
    {
        $this->remove($path);
    }

    public function clear(): void
    {
        $this->remove();
    }

    private function remove(string $path = ''): void
    {
        if ($this->srcManager->exists($path)) {
            $this->srcManager->delete($path);
        }
    }
}
