<?php
declare(strict_types=1);

namespace GreenLinks\Cache\Exception;

class InvalidArgumentException extends \InvalidArgumentException implements \Psr\Cache\InvalidArgumentException
{
}
