<?php

declare(strict_types=1);

namespace Phel\Lang\Collections\Map;

use Iterator;

use function count;

/**
 * @template K
 * @template V
 *
 * @implements Iterator<K, V>
 */
final class HashCollisionNodeIterator implements Iterator
{
    private int $index = 0;

    private readonly int $entriesCount;

    /**
     * @param array{K,V,K,V} $entries
     */
    public function __construct(
        private readonly array $entries,
    ) {
        $this->entriesCount = count($this->entries);
    }

    /**
     * @return V
     */
    public function current(): mixed
    {
        /** @var V $result */
        $result = $this->entries[$this->index + 1];
        return $result;
    }

    public function next(): void
    {
        $this->index += 2;
    }

    public function valid(): bool
    {
        return $this->index < $this->entriesCount;
    }

    public function rewind(): void
    {
        $this->index = 0;
    }

    /**
     * @return K
     */
    public function key(): mixed
    {
        /** @var K $result */
        $result = $this->entries[$this->index];
        return $result;
    }
}
