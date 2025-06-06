<?php

declare(strict_types=1);

namespace PhelTest\Unit\Lang\Collections\LinkedList;

use Phel\Lang\Collections\Exceptions\IndexOutOfBoundsException;
use Phel\Lang\Collections\LinkedList\EmptyList;
use Phel\Lang\Collections\LinkedList\PersistentList;
use Phel\Lang\Collections\LinkedList\PersistentListInterface;
use Phel\Lang\Collections\Map\PersistentMapInterface;
use Phel\Lang\TypeFactory;
use PhelTest\Unit\Lang\Collections\ModuloHasher;
use PhelTest\Unit\Lang\Collections\SimpleEqualizer;
use PHPUnit\Framework\TestCase;

final class PersistentListTest extends TestCase
{
    public function test_cons_on_empty_list(): void
    {
        $list = PersistentList::empty(new ModuloHasher(), new SimpleEqualizer())->cons('foo');

        $this->assertInstanceOf(PersistentList::class, $list);
        $this->assertCount(1, $list);
        $this->assertSame('foo', $list->get(0));
    }

    public function test_cons_on_list(): void
    {
        $list = PersistentList::empty(new ModuloHasher(), new SimpleEqualizer())
            ->cons('foo')
            ->cons('bar');

        $this->assertInstanceOf(PersistentList::class, $list);
        $this->assertCount(2, $list);
        $this->assertSame('bar', $list->get(0));
        $this->assertSame('foo', $list->get(1));
    }

    public function test_from_empty_array(): void
    {
        $list = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), []);

        $this->assertCount(0, $list);
        $this->assertInstanceOf(EmptyList::class, $list);
    }

    public function test_from_array(): void
    {
        $list = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo', 'bar']);

        $this->assertCount(2, $list);
        $this->assertSame('foo', $list->get(0));
        $this->assertSame('bar', $list->get(1));
    }

    public function test_pop_with_rest(): void
    {
        $list = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo', 'bar'])
            ->pop();

        $this->assertCount(1, $list);
        $this->assertSame('bar', $list->get(0));
    }

    public function test_pop_without_rest(): void
    {
        $list = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo'])
            ->pop();

        $this->assertInstanceOf(EmptyList::class, $list);
        $this->assertCount(0, $list);
    }

    public function test_get(): void
    {
        $list = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo', 'bar', 'foobar']);

        $this->assertSame('bar', $list->get(1));
    }

    public function test_get_negative_number(): void
    {
        $this->expectException(IndexOutOfBoundsException::class);

        $list = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo', 'bar', 'foobar']);
        $list->get(-1);
    }

    public function test_get_out_of_bound(): void
    {
        $this->expectException(IndexOutOfBoundsException::class);

        $list = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo', 'bar', 'foobar']);
        $list->get(3);
    }

    public function test_equals_other_type(): void
    {
        $list = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo', 'bar', 'foobar']);

        $this->assertFalse($list->equals(['foo', 'bar', 'foobar']));
    }

    public function test_equals_different_length(): void
    {
        $a = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo', 'bar', 'foobar']);
        $b = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo', 'bar']);

        $this->assertFalse($a->equals($b));
        $this->assertFalse($b->equals($a));
    }

    public function test_equals_different_values(): void
    {
        $a = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo', 'bar', 'foobar']);
        $b = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo', 'foobar', 'bar']);

        $this->assertFalse($a->equals($b));
        $this->assertFalse($b->equals($a));
    }

    public function test_equals_same_values(): void
    {
        $a = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo', 'bar', 'foobar']);
        $b = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo', 'bar', 'foobar']);

        $this->assertTrue($a->equals($b));
        $this->assertTrue($b->equals($a));
    }

    public function test_hash(): void
    {
        $list = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), [2]);

        $this->assertSame(33, $list->hash());
    }

    public function test_iterator(): void
    {
        $xs = ['foo', 'bar', 'foobar'];
        $list = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), $xs);

        $result = [];
        foreach ($list as $index => $value) {
            $result[$index] = $value;
        }

        $this->assertSame($xs, $result);
    }

    public function test_first(): void
    {
        $list = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo']);

        $this->assertSame('foo', $list->first());
    }

    public function test_rest(): void
    {
        $list = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo']);

        $this->assertEquals(PersistentList::empty(new ModuloHasher(), new SimpleEqualizer()), $list->rest());
    }

    public function test_cdr(): void
    {
        $list = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo']);

        $this->assertNotInstanceOf(PersistentListInterface::class, $list->cdr());
    }

    public function test_cdr2(): void
    {
        $list = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo', 'bar']);

        $this->assertEquals(PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['bar']), $list->cdr());
    }

    public function test_add_meta_data(): void
    {
        $meta = TypeFactory::getInstance()->emptyPersistentMap();
        $list = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo', 'bar']);
        $listWithMeta = $list->withMeta($meta);

        $this->assertNotInstanceOf(PersistentMapInterface::class, $list->getMeta());
        $this->assertEquals($meta, $listWithMeta->getMeta());
    }

    public function test_concat(): void
    {
        $list1 = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo', 'bar']);
        $list2 = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foobar']);

        $list = $list1->concat($list2);
        $this->assertSame(['foo', 'bar', 'foobar'], $list->toArray());
    }

    public function test_offset_exists(): void
    {
        $list = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo', 'bar']);

        $this->assertArrayNotHasKey(-1, $list);
        $this->assertArrayHasKey(0, $list);
        $this->assertArrayHasKey(1, $list);
        $this->assertArrayNotHasKey(2, $list);
    }

    public function test_offset_get(): void
    {
        $list = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo', 'bar']);

        $this->assertSame('foo', $list[0]);
        $this->assertSame('bar', $list[1]);
    }

    public function test_contains(): void
    {
        $list = PersistentList::fromArray(new ModuloHasher(), new SimpleEqualizer(), ['foo', 'bar']);

        $this->assertFalse($list->contains(-1));
        $this->assertTrue($list->contains(0));
        $this->assertTrue($list->contains(1));
        $this->assertFalse($list->contains(2));
    }
}
