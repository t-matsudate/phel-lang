<?php

declare(strict_types=1);

namespace Phel\Compiler\Ast;

use Phel\Compiler\NodeEnvironmentInterface;
use Phel\Lang\SourceLocation;

final class CallNode extends AbstractNode
{
    private AbstractNode $fn;

    /** @var AbstractNode[] */
    private array $arguments;

    /**
     * @param AbstractNode[] $arguments
     */
    public function __construct(NodeEnvironmentInterface $env, AbstractNode $fn, $arguments, ?SourceLocation $sourceLocation = null)
    {
        parent::__construct($env, $sourceLocation);
        $this->fn = $fn;
        $this->arguments = $arguments;
    }

    public function getFn(): AbstractNode
    {
        return $this->fn;
    }

    /**
     * @return AbstractNode[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }
}
