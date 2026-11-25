<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Source;

use TypeLang\Mapper\Mapping\Exception\MetadataException;
use TypeLang\Mapper\Mapping\Metadata\SourceInfo;
use TypeLang\Parser\TypeResolver;

/**
 * Creates the source contexts of properties of one reader.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping\Reader
 */
final class SourceContextFactory
{
    /**
     * Resolvers of the names written in a class or trait body by the names of
     * the body, of `self` and of `static`
     *
     * @var array<string, TypeResolver>
     */
    private array $resolvers = [];

    /**
     * Returns the class or trait whose body declares the property.
     *
     * @return \ReflectionClass<object>
     */
    public function findScope(\ReflectionProperty $property): \ReflectionClass
    {
        return self::getTraitScope($property->getDeclaringClass(), $property->name);
    }

    /**
     * Creates the place of a property declared in PHP code, the types are
     * resolved by the file declaring the property, `self` is the class
     * declaring it and `static` is the class it is read from.
     *
     * @param \ReflectionClass<object> $class class the property is read from
     */
    public function createFromReflection(\ReflectionClass $class, \ReflectionProperty $property): SourceContext
    {
        $scope = $this->findScope($property);
        $owner = $class->name;

        // a private property is named by the class declaring it
        if ($property->isPrivate()) {
            $owner = $property->class;
        }

        return new SourceContext(
            target: MetadataException::formatPropertyTarget($owner, $property->name),
            source: SourceInfo::createFromReflection($scope),
            resolver: fn(): TypeResolver => $this->getResolver($scope, $property->getDeclaringClass(), $class),
        );
    }

    /**
     * Creates the place of a value of the method, such as of its attribute,
     * the types are resolved by the file declaring the method.
     *
     * @param \ReflectionClass<object> $class class the method is read from
     */
    public function createFromMethod(\ReflectionClass $class, \ReflectionMethod $method): SourceContext
    {
        $scope = self::getMethodScope($method->getDeclaringClass(), $method);

        return new SourceContext(
            target: $class->name . '::' . $method->name . '()',
            source: SourceInfo::createFromReflection($method),
            resolver: fn(): TypeResolver => $this->getResolver($scope, $method->getDeclaringClass(), $class),
        );
    }

    /**
     * Creates the place of a value of the class itself, such as of its
     * attribute, the types are resolved by its file.
     *
     * @param \ReflectionClass<object> $class
     */
    public function createFromClass(\ReflectionClass $class): SourceContext
    {
        return new SourceContext(
            target: $class->name,
            source: SourceInfo::createFromReflection($class),
            resolver: fn(): TypeResolver => $this->getResolver($class, $class, $class),
        );
    }

    /**
     * Returns the resolver of the names written in the body: by the imports
     * and the namespace of its file, a name of the namespace in case of such
     * a class exists, `self` and `parent` by the declaring class, `static`
     * by the class the metadata is read for.
     *
     * @param \ReflectionClass<object> $scope class or trait whose file
     *        contains the type
     * @param \ReflectionClass<object> $self class of `self`
     * @param \ReflectionClass<object> $static class of `static`
     */
    private function getResolver(
        \ReflectionClass $scope,
        \ReflectionClass $self,
        \ReflectionClass $static,
    ): TypeResolver {
        $key = $scope->name . "\0" . $self->name . "\0" . $static->name;

        return $this->resolvers[$key] ??= self::createResolver($scope, $self, $static);
    }

    /**
     * @param \ReflectionClass<object> $scope
     * @param \ReflectionClass<object> $self
     * @param \ReflectionClass<object> $static
     */
    private static function createResolver(
        \ReflectionClass $scope,
        \ReflectionClass $self,
        \ReflectionClass $static,
    ): TypeResolver {
        $resolver = (new TypeResolver())->withTypeImportsFromClass($scope);

        if ($scope->isAnonymous() || $self->isAnonymous() || $static->isAnonymous()) {
            return $resolver;
        }

        return $resolver
            ->withNamespace($scope->getNamespaceName())
            ->withClasses($self->name, $static->name);
    }

    /**
     * @param \ReflectionClass<object> $class
     * @return \ReflectionClass<object>
     */
    private static function getTraitScope(\ReflectionClass $class, string $property): \ReflectionClass
    {
        foreach ($class->getTraits() as $trait) {
            if ($trait->hasProperty($property)) {
                return self::getTraitScope($trait, $property);
            }
        }

        return $class;
    }

    /**
     * Returns the class or trait whose body declares the method, also of an
     * alias of a trait method: the trait whose lines contain the method, the
     * class in case of no trait contains it.
     *
     * @param \ReflectionClass<object> $class
     * @return \ReflectionClass<object>
     */
    private static function getMethodScope(\ReflectionClass $class, \ReflectionMethod $method): \ReflectionClass
    {
        foreach ($class->getTraits() as $trait) {
            $scope = self::getMethodScope($trait, $method);

            if (self::isDeclaredIn($method, $scope)) {
                return $scope;
            }
        }

        return $class;
    }

    /**
     * @param \ReflectionClass<object> $scope
     */
    private static function isDeclaredIn(\ReflectionMethod $method, \ReflectionClass $scope): bool
    {
        $line = $method->getStartLine();

        return $method->getFileName() === $scope->getFileName()
            && $line >= $scope->getStartLine()
            && $line <= $scope->getEndLine();
    }
}
