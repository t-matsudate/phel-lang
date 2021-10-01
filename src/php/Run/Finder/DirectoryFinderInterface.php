<?php

declare(strict_types=1);

namespace Phel\Run\Finder;

interface DirectoryFinderInterface
{
    /**
     * @return list<string>
     */
    public function getSourceDirectories(): array;

    /**
     * @return list<string>
     */
    public function getTestDirectories(): array;

    /**
     * @return list<string>
     */
    public function getVendorSourceDirectories(): array;
}
