<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reference;

use TypeLang\Mapper\Mapping\Reference\Reader\ReferencesReaderInterface;
use TypeLang\Parser\Node\Name;
use TypeLang\Parser\Node\Stmt\TypeStatement;
use TypeLang\Parser\TypeResolver;

/**
 * Responsible for finding and replacing all external name dependencies
 * in a statement.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping
 */
final class ReferencesResolver
{
    /**
     * @var non-empty-lowercase-string
     */
    private const CURRENT_NAMESPACE = 'namespace';

    private readonly TypeResolver $typeResolver;

    public function __construct(
        private readonly ReferencesReaderInterface $references,
    ) {
        $this->typeResolver = new TypeResolver();
    }

    /**
     * If the passed statement contains class names (including interfaces,
     * enums, etc.), then finds the real FQN (Full Qualified Name) for this name
     * and replaces all names with FQN.
     *
     * @param \ReflectionClass<object> $context
     */
    public function resolve(TypeStatement $statement, \ReflectionClass $context): TypeStatement
    {
        // Fetch all "use" statements from the class
        $uses = $this->formatUseStatements(
            uses: $this->references->getUseStatements($context),
        );

        return $this->typeResolver->resolve($statement, function (Name $name) use ($context, $uses): ?Name {
            if ($name->isFullQualified() || $name->isBuiltin()) {
                return null;
            }

            return $this->tryFromUseStatements($name, $uses)
                ?? $this->fromCurrentNamespace($name, $context);
        });
    }

    /**
     * @param array<int|non-empty-string, non-empty-string> $uses
     */
    private function tryFromUseStatements(Name $name, array $uses): ?Name
    {
        $suffix = (string) $name->getLastPart();

        if (isset($uses[$suffix])) {
            return new Name($uses[$suffix]);
        }

        return null;
    }

    /**
     * @param array<int|non-empty-string, non-empty-string> $uses
     *
     * @return array<non-empty-string, non-empty-string>
     */
    private function formatUseStatements(array $uses): array
    {
        $result = [];

        foreach ($uses as $alias => $fqn) {
            if (\is_string($alias)) {
                $result[$alias] = $fqn;
                continue;
            }

            $nameOffset = \strrpos($fqn, '\\');

            if ($nameOffset === false) {
                $result[$fqn] = $fqn;
                continue;
            }

            dd($nameOffset);
        }

        return $result;
    }

    /**
     * @param \ReflectionClass<object> $class
     */
    private function fromCurrentNamespace(Name $name, \ReflectionClass $class): ?Name
    {
        // Replace "namespace\ClassName" sequences to current namespace of the class.
        $first = $name->getFirstPart();

        if ($first->toLowerString() === self::CURRENT_NAMESPACE) {
            $name = $name->slice(1);
        }

        $namespace = $class->getNamespaceName();

        if ($namespace === '') {
            return $name;
        }

        return (new Name($namespace))
            ->withAdded($name);
    }
}
