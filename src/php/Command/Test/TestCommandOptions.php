<?php

declare(strict_types=1);

namespace Phel\Command\Test;

use Phel\Lang\TypeFactory;
use Phel\Printer\Printer;

final class TestCommandOptions
{
    public const FILTER = 'filter';

    private string $filter = '';

    public static function empty(): self
    {
        return new self();
    }

    public static function fromArray(array $options): self
    {
        $self = new self();
        $self->filter = $options[self::FILTER] ?? '';

        return $self;
    }

    public function asPhelHashMap(): string
    {
        $filter = empty($this->filter) ? 'nil' : sprintf('"%s"', $this->filter);

        $optionsMap = TypeFactory::getInstance()
            ->persistentMapFromKVs(':filter', $filter);

        return Printer::nonReadable()->print($optionsMap);
    }
}
