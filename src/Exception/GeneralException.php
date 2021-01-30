<?php
declare(strict_types=1);

namespace GreenLinks\Cache\Exception;

use Psr\Cache\CacheException;

use Exception;

class GeneralException extends Exception implements CacheException
{
}
