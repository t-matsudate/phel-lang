<?php

declare(strict_types=1);

namespace Phel\Lang\Collections\Map;

use Phel\Lang\EqualizerInterface;
use Phel\Lang\HasherInterface;
use Traversable;

use function array_key_exists;
use function count;

/**
 * @template TKey of mixed
 * @template TValue of mixed
 *
 * @implements HashMapNodeInterface<TKey, TValue>
 */
final readonly class IndexedNode implements HashMapNodeInterface
{
    /**
     * @param list<array{
     *     0: TKey|null,
     *     1: TValue|HashMapNodeInterface<TKey, TValue>,
     * }> $objects
     */
    public function __construct(
        private HasherInterface $hasher,
        private EqualizerInterface $equalizer,
        private array $objects,
    ) {
    }

    public static function empty(HasherInterface $hasher, EqualizerInterface $equalizer): self
    {
        return new self($hasher, $equalizer, []);
    }

    /**
     * @param TKey $key
     * @param TValue $value
     *
     * @return HashMapNodeInterface<TKey, TValue>
     */
    public function put(int $shift, int $hash, mixed $key, mixed $value, Box $addedLeaf): HashMapNodeInterface
    {
        $index = $this->mask($hash, $shift);
        if (isset($this->objects[$index])) {
            [$currentKey, $currentValue] = $this->objects[$index];

            if ($currentKey === null) {
                return $this->addToChild($index, $shift, $hash, $key, $value, $addedLeaf);
            }

            /** @var TValue $currentValue */
            if ($this->equalizer->equals($key, $currentKey)) {
                return $this->updateKey($index, $currentValue, $value);
            }

            $addedLeaf->setValue(true);
            $newObjects = $this->objects;
            /** @var TKey $currentKey */
            /** @var TValue $currentValue */
            $newObjects[$index] = [
                null,
                $this->createNode($shift + 5, $currentKey, $currentValue, $hash, $key, $value),
            ];

            return new self($this->hasher, $this->equalizer, $newObjects);
        }

        return $this->insertNewKey($index, $shift, $hash, $key, $value, $addedLeaf);
    }

    /**
     * @param mixed $key
     */
    public function remove(int $shift, int $hash, $key): ?HashMapNodeInterface
    {
        $index = $this->mask($hash, $shift);
        if (!isset($this->objects[$index])) {
            return $this;
        }

        [$currentKey, $currentValue] = $this->objects[$index];

        if ($currentKey === null) {
            /** @var HashMapNodeInterface $node */
            $node = $currentValue;
            $n = $node->remove($shift + 5, $hash, $key);

            if ($n === $node) {
                return $this;
            }

            if ($n instanceof HashMapNodeInterface) {
                $newObjects = $this->objects;
                $newObjects[$index][1] = $n;
                return new self($this->hasher, $this->equalizer, $newObjects);
            }

            if (count($this->objects) === 1) {
                return null;
            }

            $newObjects = $this->objects;
            unset($newObjects[$index]);
            return new self($this->hasher, $this->equalizer, $newObjects);
        }

        if ($this->equalizer->equals($key, $currentKey)) {
            if (count($this->objects) === 1) {
                return null;
            }

            $newObjects = $this->objects;
            unset($newObjects[$index]);
            return new self($this->hasher, $this->equalizer, $newObjects);
        }

        return $this;
    }

    /**
     * @param mixed $key
     * @param mixed $notFound
     *
     * @return ?mixed
     */
    public function find(int $shift, int $hash, $key, $notFound)
    {
        $index = $this->mask($hash, $shift);
        if (!isset($this->objects[$index])) {
            return $notFound;
        }

        [$currentKey, $currentValue] = $this->objects[$index];

        if ($currentKey === null) {
            /** @var HashMapNodeInterface $node */
            $node = $currentValue;

            return $node->find($shift + 5, $hash, $key, $notFound);
        }

        if ($this->equalizer->equals($key, $currentKey)) {
            return $currentValue;
        }

        return $notFound;
    }

    public function getIterator(): Traversable
    {
        return new IndexedNodeIterator($this->objects);
    }

    /**
     * @param TKey $key1
     * @param TValue $value1
     * @param TKey $key2
     * @param TValue $value2
     *
     * @return HashMapNodeInterface<TKey, TValue>
     */
    private function createNode(int $shift, mixed $key1, mixed $value1, int $key2Hash, mixed $key2, mixed $value2): HashMapNodeInterface
    {
        $key1Hash = $this->hasher->hash($key1);
        if ($key1Hash === $key2Hash) {
            return new HashCollisionNode($this->hasher, $this->equalizer, $key1Hash, 2, [$key1, $value1, $key2, $value2]);
        }

        $addedLeaf = new Box(null);
        return self::empty($this->hasher, $this->equalizer)
            ->put($shift, $key1Hash, $key1, $value1, $addedLeaf)
            ->put($shift, $key2Hash, $key2, $value2, $addedLeaf);
    }

    /**
     * @param TValue $currentValue
     * @param TValue $newValue
     *
     * @return HashMapNodeInterface<TKey, TValue>
     */
    private function updateKey(int $index, mixed $currentValue, mixed $newValue): HashMapNodeInterface
    {
        if ($this->equalizer->equals($newValue, $currentValue)) {
            return $this;
        }

        $newObjects = $this->objects;
        $newObjects[$index][1] = $newValue;
        return new self($this->hasher, $this->equalizer, $newObjects);
    }

    /**
     * @param TKey $key
     * @param TValue $value
     *
     * @return HashMapNodeInterface<TKey, TValue>
     */
    private function addToChild(int $idx, int $shift, int $hash, mixed $key, mixed $value, Box $addedLeaf): HashMapNodeInterface
    {
        /** @var HashMapNodeInterface $childNode */
        $childNode = $this->objects[$idx][1];
        $newChild = $childNode->put($shift + 5, $hash, $key, $value, $addedLeaf);
        if ($childNode === $newChild) {
            // Nothing changed
            return $this;
        }

        $newObjects = $this->objects;
        $newObjects[$idx][1] = $newChild;
        return new self($this->hasher, $this->equalizer, $newObjects);
    }

    /**
     * @param TKey $key
     * @param TValue $value
     *
     * @return HashMapNodeInterface<TKey, TValue>
     */
    private function insertNewKey(int $idx, int $shift, int $hash, mixed $key, mixed $value, Box $addedLeaf): HashMapNodeInterface
    {
        if (count($this->objects) >= 16) {
            return $this->splitNode($idx, $shift, $hash, $key, $value, $addedLeaf);
        }

        return $this->addNewKeyToNode($idx, $key, $value, $addedLeaf);
    }

    /**
     * @param TKey $key
     * @param TValue $value
     *
     * @return HashMapNodeInterface<TKey, TValue>
     */
    private function splitNode(int $idx, int $shift, int $hash, mixed $key, mixed $value, Box $addedLeaf): HashMapNodeInterface
    {
        $nodes = []; // array_fill(0, 32, null);
        $empty = self::empty($this->hasher, $this->equalizer);
        $nodes[$idx] = $empty->put($shift + 5, $hash, $key, $value, $addedLeaf);
        for ($i = 0; $i < 32; ++$i) {
            if (array_key_exists($i, $this->objects)) {
                /** @var TValue $v */
                [$k, $v] = $this->objects[$i];
                $nodes[$i] = ($k === null) ? $v : $empty->put($shift + 5, $this->hasher->hash($k), $k, $v, $addedLeaf);
            }
        }

        return new ArrayNode($this->hasher, $this->equalizer, count($this->objects) + 1, $nodes);
    }

    /**
     * @param TKey $key
     * @param TValue $value
     *
     * @return IndexedNode<TKey, TValue>
     */
    private function addNewKeyToNode(int $idx, mixed $key, mixed $value, Box $addedLeaf): self
    {
        $newObjects = $this->objects;
        $newObjects[$idx] = [$key, $value];
        $addedLeaf->setValue(true);
        return new self($this->hasher, $this->equalizer, $newObjects);
    }

    private function mask(int $hash, int $shift): int
    {
        return $hash >> $shift & 0x01f;
    }
}
