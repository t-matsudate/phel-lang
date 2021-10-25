<?php

declare(strict_types=1);

namespace Phel\Build\Compile;

use Phel\Build\Extractor\NamespaceExtractorInterface;
use Phel\Compiler\Compiler\CompileOptions;
use Phel\Compiler\CompilerFacadeInterface;

final class FileCompiler implements FileCompilerInterface
{
    private CompilerFacadeInterface $compilerFacade;

    private NamespaceExtractorInterface $namespaceExtractor;

    public function __construct(
        CompilerFacadeInterface $compilerFacade,
        NamespaceExtractorInterface $namespaceExtractor
    ) {
        $this->compilerFacade = $compilerFacade;
        $this->namespaceExtractor = $namespaceExtractor;
    }

    public function compileFile(string $src, string $dest, bool $enableSourceMaps): CompiledFile
    {
        $phelCode = file_get_contents($src);

        $options = new CompileOptions();
        $options->setSource($src)->setEnabledSourceMaps($enableSourceMaps);
        $result = $this->compilerFacade->compile($phelCode, $options);

        file_put_contents($dest, "<?php\n" . $result->getCode());
        file_put_contents(str_replace('.php', '.phel', $dest), $phelCode);
        if ($enableSourceMaps) {
            file_put_contents($dest . '.map', $result->getSourceMap());
        } elseif (file_exists($dest . '.map')) {
            @unlink($dest . '.map');
        }

        $namespaceInfo = $this->namespaceExtractor->getNamespaceFromFile($src);

        return new CompiledFile(
            $src,
            $dest,
            $namespaceInfo->getNamespace()
        );
    }
}
