<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Repository;

use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Context\ClassAccessors;
use TypeLang\Mapper\Exception\NotInterceptableExceptionInterface;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Mapping\Provider\MetadataProviderInterface;
use TypeLang\Mapper\Type\Builder\TypeBuilderInterface;
use TypeLang\Mapper\Type\Decorator\TypeDecoratorInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Printer\TypePrinterInterface;
use TypeLang\Type\TypeNode;

/**
 * Builds types by the first builder supporting the node, wraps them by the
 * decorators and keeps them.
 */
final class TypeRepository implements InspectableTypeRepositoryInterface
{
    private readonly BuildContext $build;

    /**
     * @var array<string, TypeInterface<mixed>>
     */
    private array $types = [];

    /**
     * Types as they are built by the keys of the types the decorators change
     *
     * @var array<string, TypeInterface<mixed>>
     */
    private array $undecorated = [];

    /**
     * Builders of the types by the keys of the types
     *
     * @var array<string, TypeBuilderInterface<mixed>>
     */
    private array $buildersOfTypes = [];

    /**
     * References to the types being built, by the keys of the types
     *
     * @var array<string, list<RecursiveTypeReference>>
     */
    private array $building = [];

    /**
     * Keys of the types kept since the outermost type started building,
     * removed from the cache in case of the building fails
     *
     * @var list<string>
     */
    private array $built = [];

    public function __construct(
        /**
         * @var list<TypeBuilderInterface<mixed>>
         */
        private readonly array $builders,
        MetadataProviderInterface $metadata,
        /**
         * Applied in the order of the list to each built type
         *
         * @var list<TypeDecoratorInterface>
         */
        private readonly array $decorators,
        /**
         * Runtime side of the classes, the same object as the one of the
         * context of the conversion
         */
        ClassAccessors $accessors,
        /**
         * Prints the canonical description of a type, the key of the type,
         * it must keep all fields of shapes
         */
        private readonly TypePrinterInterface $printer,
    ) {
        $this->build = new BuildContext(
            types: $this,
            metadata: $metadata,
            accessors: $accessors,
            printer: $this->printer
        );
    }

    public function getType(TypeNode $node): TypeInterface
    {
        $key = $this->createKey($node);

        if (isset($this->types[$key])) {
            return $this->types[$key];
        }

        if (isset($this->building[$key])) {
            return $this->building[$key][] = new RecursiveTypeReference();
        }

        $this->building[$key] = [];
        $mark = \count($this->built);

        try {
            $type = $this->buildType($node, $key);
        } catch (\Throwable $e) {
            unset($this->building[$key]);
            $this->forgetBuiltSince($mark);

            throw $e;
        }

        /** @var list<RecursiveTypeReference> */
        $references = $this->building[$key];
        unset($this->building[$key]);

        foreach ($references as $reference) {
            $reference->resolve($type);
        }

        $this->types[$key] = $type;
        $this->built[] = $key;

        if ($this->building === []) {
            $this->built = [];
        }

        return $type;
    }

    public function findUndecoratedType(TypeNode $node): ?TypeInterface
    {
        return $this->undecorated[$this->createKey($node)] ?? null;
    }

    public function findBuilder(TypeNode $node): ?TypeBuilderInterface
    {
        return $this->buildersOfTypes[$this->createKey($node)] ?? null;
    }

    /**
     * Removes the types kept after the mark from the cache.
     *
     * @param int<0, max> $mark
     */
    private function forgetBuiltSince(int $mark): void
    {
        foreach (\array_splice($this->built, $mark) as $key) {
            unset($this->types[$key], $this->undecorated[$key], $this->buildersOfTypes[$key]);
        }
    }

    /**
     * Any other failure of a builder or a decorator, such as wrong metadata
     * of a class, becomes the error of the declaration. The type as it is
     * built is kept by the key in case of a decorator changes it.
     *
     * @return TypeInterface<mixed>
     * @throws TypeDefinitionException in case of no builder supports the node
     *         or the type cannot be built
     */
    private function buildType(TypeNode $node, string $key): TypeInterface
    {
        try {
            $built = $this->findInBuilders($node, $key)
                ?? throw TypeDefinitionException::becauseTypeIsNotSupported($this->printer->print($node));
            $type = $built;

            foreach ($this->decorators as $decorator) {
                $type = $decorator->decorate($type, $node, $this->build);
            }
        } catch (NotInterceptableExceptionInterface $e) {
            throw $e;
        } catch (\Exception $e) {
            throw TypeDefinitionException::becauseTypeCannotBeBuilt($this->printer->print($node), $e);
        }

        if ($type !== $built) {
            $this->undecorated[$key] = $built;
        }

        return $type;
    }

    /**
     * Returns the type by the first builder supporting the node, the builder
     * is kept by the key.
     *
     * @return TypeInterface<mixed>|null
     * @throws TypeDefinitionException in case of a builder supports the node,
     *         but it is declared wrong
     */
    private function findInBuilders(TypeNode $node, string $key): ?TypeInterface
    {
        foreach ($this->builders as $builder) {
            $type = $builder->tryBuild($node, $this->build);

            if ($type !== null) {
                $this->buildersOfTypes[$key] = $builder;

                return $type;
            }
        }

        return null;
    }

    /**
     * Returns the canonical description of the type, a fully qualified
     * name is written without the leading `\`: `\App\User` and `App\User`
     * are one type. Quoted strings, such as the literal `'\Foo'`, are kept
     * as is.
     */
    private function createKey(TypeNode $node): string
    {
        return (string) \preg_replace(
            '/(?:\'(?:[^\'\\\\]|\\\\.)*\'|"(?:[^"\\\\]|\\\\.)*")(*SKIP)(*FAIL)|(?<![\w\\\\])\\\\(?=\w)/s',
            '',
            $this->printer->print($node),
        );
    }
}
