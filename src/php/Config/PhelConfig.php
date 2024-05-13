<?php

declare(strict_types=1);

namespace Phel\Config;

use JsonSerializable;
use Phel\Build\BuildConfig;
use Phel\Command\CommandConfig;
use Phel\Filesystem\FilesystemConfig;
use Phel\Formatter\FormatterConfig;
use Phel\Interop\InteropConfig;

final class PhelConfig implements JsonSerializable
{
    /** @var list<string> */
    private array $srcDirs = ['src/phel'];

    /** @var list<string> */
    private array $testDirs = ['tests/phel'];

    private string $vendorDir = 'vendor';

    private string $errorLogFile = 'data/error.log';

    private PhelExportConfig $exportConfig;

    private PhelBuildConfig $buildConfig;

    /** @var list<string> */
    private array $ignoreWhenBuilding = ['src/phel/local.phel'];

    /** @var list<string> */
    private array $noCacheWhenBuilding = [];

    private bool $keepGeneratedTempFiles = false;

    /** @var list<string> */
    private array $formatDirs = ['src', 'tests'];

    public function __construct()
    {
        $this->exportConfig = new PhelExportConfig();
        $this->buildConfig = new PhelBuildConfig();
    }

    public function getSrcDirs(): array
    {
        return $this->srcDirs;
    }

    /**
     * @param list<string> $list
     */
    public function setSrcDirs(array $list): self
    {
        $this->srcDirs = $list;

        return $this;
    }

    public function getTestDirs(): array
    {
        return $this->testDirs;
    }

    /**
     * @param list<string> $list
     */
    public function setTestDirs(array $list): self
    {
        $this->testDirs = $list;

        return $this;
    }

    public function getVendorDir(): string
    {
        return $this->vendorDir;
    }

    public function setVendorDir(string $dir): self
    {
        $this->vendorDir = $dir;

        return $this;
    }

    public function getExportConfig(): PhelExportConfig
    {
        return $this->exportConfig;
    }

    public function setExportConfig(PhelExportConfig $exportConfig): self
    {
        $this->exportConfig = $exportConfig;

        return $this;
    }

    public function getErrorLogFile(): string
    {
        return $this->errorLogFile;
    }

    public function setErrorLogFile(string $filepath): self
    {
        $this->errorLogFile = $filepath;

        return $this;
    }

    public function getBuildConfig(): PhelBuildConfig
    {
        return $this->buildConfig;
    }

    /**
     * @deprecated use `setBuild(PhelBuildConfig)`
     */
    public function setOut(PhelBuildConfig $buildConfig): self
    {
        return $this->setBuildConfig($buildConfig);
    }

    public function setBuildConfig(PhelBuildConfig $buildConfig): self
    {
        $this->buildConfig = $buildConfig;

        return $this;
    }

    public function getIgnoreWhenBuilding(): array
    {
        return $this->ignoreWhenBuilding;
    }

    /**
     * @param list<string> $list
     */
    public function setIgnoreWhenBuilding(array $list): self
    {
        $this->ignoreWhenBuilding = $list;

        return $this;
    }

    public function isKeepGeneratedTempFiles(): bool
    {
        return $this->keepGeneratedTempFiles;
    }

    public function setKeepGeneratedTempFiles(bool $flag): self
    {
        $this->keepGeneratedTempFiles = $flag;

        return $this;
    }

    public function getFormatDirs(): array
    {
        return $this->formatDirs;
    }

    /**
     * @param list<string> $list
     */
    public function setFormatDirs(array $list): self
    {
        $this->formatDirs = $list;

        return $this;
    }

    /**
     * @param list<string> $list
     */
    public function setNoCacheWhenBuilding(array $list): self
    {
        $this->noCacheWhenBuilding = $list;
        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            CommandConfig::SRC_DIRS => $this->srcDirs,
            CommandConfig::TEST_DIRS => $this->testDirs,
            CommandConfig::VENDOR_DIR => $this->vendorDir,
            CommandConfig::ERROR_LOG_FILE => $this->errorLogFile,
            CommandConfig::BUILD_CONFIG => $this->buildConfig->jsonSerialize(),
            InteropConfig::EXPORT_CONFIG => $this->exportConfig->jsonSerialize(),
            BuildConfig::IGNORE_WHEN_BUILDING => $this->ignoreWhenBuilding,
            BuildConfig::NO_CACHE_WHEN_BUILDING => $this->noCacheWhenBuilding,
            FilesystemConfig::KEEP_GENERATED_TEMP_FILES => $this->keepGeneratedTempFiles,
            FormatterConfig::FORMAT_DIRS => $this->formatDirs,
        ];
    }
}
