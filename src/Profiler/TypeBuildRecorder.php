<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Profiler;

use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Type\Decorator\TypeDecoratorInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Type\TypeNode;

/**
 * Reports each type a mapper builds to the profiler and returns the type
 * as is, the last decorator of the mapper.
 */
final class TypeBuildRecorder implements TypeDecoratorInterface
{
    public function __construct(
        /**
         * Name of the mapper in the profiler
         *
         * @var non-empty-string
         */
        private readonly string $name,
        private readonly ProfilerInterface $profiler,
    ) {}

    public function decorate(TypeInterface $type, TypeNode $node, BuildContext $context): TypeInterface
    {
        $this->profiler->recordType($this->name, $context->printer->print($node), $type::class);

        return $type;
    }
}
