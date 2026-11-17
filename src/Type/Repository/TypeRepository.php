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
use TypeLang\Printer\PrettyTypePrinter;
use TypeLang\Printer\PrinterOptions;
use TypeLang\Printer\TypePrinterInterface;
use TypeLang\Type\TypeNode;

/**
 * Builds types by the first builder supporting the node, wraps them by the
 * decorators and keeps them.
 */
final class TypeRepository implements TypeRepositoryInterface
{
    private readonly BuildContext $build;

    /**
     * @var array<string, TypeInterface<mixed>>
     */
    private array $types = [];

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
        private readonly array $decorators = [],
        /**
         * Runtime side of the classes, the same object as the one of the
         * context of the conversion
         */
        ClassAccessors $accessors = new ClassAccessors(),
        /**
         * Prints the canonical description of a type, the key of the type
         */
        private readonly TypePrinterInterface $printer = new PrettyTypePrinter(
            new PrinterOptions(maxInlineShapeFields: \PHP_INT_MAX),
        ),
    ) {
        $this->build = new BuildContext($this, $metadata, $accessors, $this->printer);
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
            $type = $this->buildType($node);
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

    /**
     * Removes the types kept after the mark from the cache.
     *
     * @param int<0, max> $mark
     */
    private function forgetBuiltSince(int $mark): void
    {
        foreach (\array_splice($this->built, $mark) as $key) {
            unset($this->types[$key]);
        }
    }

    /**
     * Any other failure of a builder or a decorator, such as wrong metadata
     * of a class, becomes the error of the declaration.
     *
     * @return TypeInterface<mixed>
     * @throws TypeDefinitionException in case of no builder supports the node
     *         or the type cannot be built
     */
    private function buildType(TypeNode $node): TypeInterface
    {
        try {
            $type = $this->findInBuilders($node)
                ?? throw TypeDefinitionException::becauseTypeIsNotSupported($this->printer->print($node));

            foreach ($this->decorators as $decorator) {
                $type = $decorator->decorate($type, $node, $this->build);
            }
        } catch (NotInterceptableExceptionInterface $e) {
            throw $e;
        } catch (\Exception $e) {
            throw TypeDefinitionException::becauseTypeCannotBeBuilt($this->printer->print($node), $e);
        }

        return $type;
    }

    /**
     * @return TypeInterface<mixed>|null
     * @throws TypeDefinitionException in case of a builder supports the node,
     *         but it is declared wrong
     */
    private function findInBuilders(TypeNode $node): ?TypeInterface
    {
        foreach ($this->builders as $builder) {
            $type = $builder->tryBuild($node, $this->build);

            if ($type !== null) {
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
