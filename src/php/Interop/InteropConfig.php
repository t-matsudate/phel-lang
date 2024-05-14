<?php

declare(strict_types=1);

namespace Phel\Interop;

use Gacela\Framework\AbstractConfig;
use Phel\Config\PhelConfig;
use Phel\Config\PhelExportConfig;

final class InteropConfig extends AbstractConfig
{
    public const EXPORT_DIRECTORIES = PhelExportConfig::FROM_DIRECTORIES;

    public const EXPORT_NAMESPACE_PREFIX = PhelExportConfig::NAMESPACE_PREFIX;

    public const EXPORT_TARGET_DIRECTORY = PhelExportConfig::TARGET_DIRECTORY;

    private const DEFAULT_EXPORT_DIRECTORIES = ['src'];

    private const DEFAULT_EXPORT_NAMESPACE_PREFIX = 'PhelGenerated';

    private const DEFAULT_EXPORT_TARGET_DIRECTORY = 'src/PhelGenerated';

    private const DEFAULT_EXPORT = [
        self::EXPORT_DIRECTORIES => self::DEFAULT_EXPORT_DIRECTORIES,
        self::EXPORT_NAMESPACE_PREFIX => self::DEFAULT_EXPORT_NAMESPACE_PREFIX,
        self::EXPORT_TARGET_DIRECTORY => self::DEFAULT_EXPORT_TARGET_DIRECTORY,
    ];

    public function prefixNamespace(): string
    {
        return (string)($this->getExport()[self::EXPORT_NAMESPACE_PREFIX] ?? self::DEFAULT_EXPORT_NAMESPACE_PREFIX);
    }

    public function getExportTargetDirectory(): string
    {
        return (string)($this->getExport()[self::EXPORT_TARGET_DIRECTORY] ?? self::DEFAULT_EXPORT_TARGET_DIRECTORY);
    }

    /**
     * @return list<string>
     */
    public function getExportDirectories(): array
    {
        return array_map(
            fn (string $dir): string => $this->getAppRootDir() . '/' . $dir,
            $this->getExport()[self::EXPORT_DIRECTORIES] ?? self::DEFAULT_EXPORT_DIRECTORIES,
        );
    }

    private function getExport(): array
    {
        return $this->get(PhelConfig::EXPORT_CONFIG, self::DEFAULT_EXPORT);
    }
}
