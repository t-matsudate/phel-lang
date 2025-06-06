<?php

declare(strict_types=1);

namespace Phel\Printer\TypePrinter;

use Phel\Lang\Collections\HashSet\PersistentHashSetInterface;
use Phel\Printer\PrinterInterface;

/**
 * @implements TypePrinterInterface<PersistentHashSetInterface>
 */
final readonly class PersistentHashSetPrinter implements TypePrinterInterface
{
    public function __construct(private PrinterInterface $printer)
    {
    }

    /**
     * @param PersistentHashSetInterface $form
     */
    public function print(mixed $form): string
    {
        $values = [];
        foreach ($form->toPhpArray() as $elem) {
            $values[] = $this->printer->print($elem);
        }

        return '(set' . ($values !== [] ? ' ' : '') . implode(' ', $values) . ')';
    }
}
