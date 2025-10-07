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
        // Performs Name conversions if the required type is found
        // in the same namespace as the declared dependency.
        $statement = $this->resolveFromCurrentNamespace($statement, $context);

        $uses = $this->references->getUseStatements($context);

        // Additionally performs Name conversions if the required
        // type was specified in "use" statement.
        return $this->typeResolver->resolveWith($statement, $uses);
    }

    /**
     * @param \ReflectionClass<object> $class
     */
    private function resolveFromCurrentNamespace(TypeStatement $statement, \ReflectionClass $class): TypeStatement
    {
        return $this->typeResolver->resolve(
            type: $statement,
            transform: static function (Name $name) use ($class): ?Name {
                $namespace = $class->getNamespaceName();

                // Replace "namespace\ClassName" sequences to current
                // namespace of the class.
                if (!$name->isSimple()) {
                    $first = $name->getFirstPart();

                    if ($first->toLowerString() === 'namespace') {
                        // Return name AS IS in case of namespace is global
                        if ($namespace === '') {
                            return $name->slice(1);
                        }

                        return (new Name($namespace))
                            ->withAdded($name->slice(1));
                    }
                }

                if ($namespace !== '' && self::entryExists($namespace . '\\' . $name->toString())) {
                    return (new Name($namespace))
                        ->withAdded($name);
                }

                return null;
            },
        );
    }

    private static function entryExists(string $fqn): bool
    {
        return \class_exists($fqn)
            || \interface_exists($fqn, false);
    }
}
