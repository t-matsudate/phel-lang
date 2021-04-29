<?php

declare(strict_types=1);

namespace PhelTest\Benchmark\Lang\Collections\Map;

use Phel\Lang\HasherInterface;

final class SimpleHasher implements HasherInterface
{
    public function hash($value): int
    {
        return $value;
    }
}
