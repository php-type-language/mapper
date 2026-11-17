<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Profiler;

use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Type\Decorator\TypeDecoratorInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Type\TypeNode;

/**
 * Reports each type a mapper builds to the collector and returns the type
 * as is, the last decorator of the mapper in the debug mode.
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
        private readonly MapperDataCollector $collector,
    ) {}

    public function decorate(TypeInterface $type, TypeNode $node, BuildContext $context): TypeInterface
    {
        $this->collector->collectType($this->name, $context->printer->print($node), $type::class);

        return $type;
    }
}
