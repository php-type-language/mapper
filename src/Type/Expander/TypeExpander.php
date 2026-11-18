<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Expander;

use TypeLang\Mapper\Context\ExpandContext;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Exception\TypeExpansionException;
use TypeLang\Mapper\Type\Repository\InspectableTypeRepositoryInterface;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Type\Constant\ConstMaskTypeNode;
use TypeLang\Type\Constant\ConstTypeNode;
use TypeLang\Type\LogicalTypeNode;
use TypeLang\Type\Name;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\Shape\FieldListNode;
use TypeLang\Type\Template\TypedTemplateArgumentNode;
use TypeLang\Type\TypeNode;
use TypeLang\Type\WrappingTypeNode;

/**
 * Expands the names and the constants of a type into the types of the data
 * they take, by the first expander supporting the built type of each. The
 * type keeps how it is written, a name no expander supports stays as is.
 *
 * A type wrapped by a decorator is expanded by the expanders of the wrapper
 * first, then as it is built: an enum trimmed by a decorator is still the
 * union of its values.
 *
 * The limits are common to the expansions of all types: the shapes of an
 * expansion are cut to the maximum number of fields and lose their fields
 * at the maximum depth, the types nested deeper are `mixed`.
 *
 * ```
 * $expander->expand($listOfStatuses); // list<'new'|'paid'>
 * $expander->expand($listOfMoney);    // list<App\Money>
 * $expander->expand($user);           // object{id: int, ...}
 * ```
 */
final class TypeExpander
{
    public function __construct(
        /**
         * Built types of the names and the constants
         */
        private readonly InspectableTypeRepositoryInterface $types,
        /**
         * Expanders in the order they are tried
         *
         * @var list<TypeExpanderInterface>
         */
        private readonly array $expanders,
        /**
         * Level the shapes of the expansions lose their fields at, such as
         * the fields of a class
         *
         * @var int<0, max>
         */
        private readonly int $maxDepth = 1,
        /**
         * Number of fields a shape of an expansion keeps, the others are
         * shown as `...`, {@see null} keeps all of them
         *
         * @var int<1, max>|null
         */
        private readonly ?int $maxFields = null,
    ) {}

    /**
     * Creates the expander with the expanders of the user tried before the
     * default ones.
     *
     * @param list<TypeExpanderInterface> $expanders
     * @param int<0, max> $maxDepth
     * @param int<1, max>|null $maxFields
     */
    public static function createDefault(
        InspectableTypeRepositoryInterface $types,
        array $expanders = [],
        int $maxDepth = 1,
        ?int $maxFields = null,
    ): self {
        return new self($types, [
            ...$expanders,
            new ClassTypeExpander(),
            new DiscriminatorTypeExpander(),
            new EnumTypeExpander(),
            new DateTimeTypeExpander(),
            new LiteralTypeExpander(),
            new UnionTypeExpander(),
        ], $maxDepth, $maxFields);
    }

    /**
     * Returns the type itself in case of nothing in it is expanded, such as
     * with no expanders, a new node otherwise. The given type is not
     * changed. A type nested deeper than the maximum depth is `mixed`, the
     * shape it belongs to loses its fields.
     *
     * @param int<0, max> $depth level of the type, `0` for the given type
     *        itself, one more for each nested expansion
     * @throws TypeExpansionException in case of an expander fails or the type
     *         of a name cannot be built
     */
    public function expand(TypeNode $type, int $depth = 0): TypeNode
    {
        if ($this->expanders === []) {
            return $type;
        }

        if ($depth > $this->maxDepth) {
            return new NamedTypeNode(new Name('mixed'));
        }

        return $this->rewrite($type, $depth);
    }

    /**
     * Returns the expansion of the built type by the first expander
     * supporting it, {@see null} in case of none does.
     *
     * @param TypeInterface<mixed> $type
     * @param int<0, max> $depth
     * @throws TypeExpansionException in case of an expander fails
     */
    public function expandType(TypeInterface $type, int $depth = 0): ?TypeNode
    {
        $context = new ExpandContext($this, $depth);

        foreach ($this->expanders as $expander) {
            try {
                $expansion = $expander->tryExpand($type, $context);
            } catch (TypeExpansionException $e) {
                throw $e;
            } catch (\Throwable $e) {
                throw TypeExpansionException::becauseTypeCannotBeExpanded($e);
            }

            if ($expansion !== null) {
                return $this->limit($expansion, $depth);
            }
        }

        return null;
    }

    /**
     * Returns the expansion with its shapes without fields at the maximum
     * depth and with the fields cut to the maximum number above it. The
     * fields of a shape are expansions of their own, already limited.
     *
     * @param int<0, max> $depth
     */
    private function limit(TypeNode $expansion, int $depth): TypeNode
    {
        if (!$expansion instanceof NamedTypeNode || $expansion->fields === null) {
            return $this->mapNested($expansion, fn(TypeNode $nested): TypeNode => $this->limit($nested, $depth));
        }

        if ($depth >= $this->maxDepth) {
            return new NamedTypeNode($expansion->name, $expansion->arguments);
        }

        if ($this->maxFields === null || \count($expansion->fields) <= $this->maxFields) {
            return $expansion;
        }

        return new NamedTypeNode($expansion->name, $expansion->arguments, new FieldListNode(
            \array_slice($expansion->fields->children, 0, $this->maxFields),
            isSealed: false,
        ));
    }

    /**
     * Returns the type itself in case of nothing in it is expanded, a new
     * node otherwise. The given type is not changed.
     *
     * @param int<0, max> $depth
     * @throws TypeExpansionException in case of an expander fails
     */
    private function rewrite(TypeNode $type, int $depth): TypeNode
    {
        $rewrite = fn(TypeNode $nested): TypeNode => $this->rewrite($nested, $depth);

        if ($type instanceof NamedTypeNode && $type->fields !== null) {
            return $this->mapFields($type, $rewrite);
        }

        if ($type instanceof NamedTypeNode || $type instanceof ConstTypeNode || $type instanceof ConstMaskTypeNode) {
            $expansion = $this->expandBuilt($type, $depth);

            if ($expansion !== null) {
                return $expansion;
            }
        }

        return $this->mapNested($type, $rewrite);
    }

    /**
     * Returns the type with the types of its fields mapped, the type itself
     * in case of none is changed.
     *
     * @param \Closure(TypeNode): TypeNode $map
     */
    private function mapFields(NamedTypeNode $type, \Closure $map): NamedTypeNode
    {
        $mapped = [];

        foreach ($type->fields ?? [] as $index => $field) {
            $value = $map($field->type);

            if ($value !== $field->type) {
                $mapped[$index] = $value;
            }
        }

        if ($mapped === []) {
            return $type;
        }

        $copy = clone $type;

        foreach ($mapped as $index => $value) {
            $field = $copy->fields?->children[$index] ?? null;

            \assert($field !== null);

            $field->type = $value;
        }

        return $copy;
    }

    /**
     * Returns the type with the types nested in it mapped: the type of a
     * wrapper, the members of a union or an intersection, the template
     * arguments of a name. The type itself in case of none is changed.
     *
     * @param \Closure(TypeNode): TypeNode $map
     */
    private function mapNested(TypeNode $type, \Closure $map): TypeNode
    {
        if ($type instanceof WrappingTypeNode) {
            $value = $map($type->type);

            if ($value === $type->type) {
                return $type;
            }

            $copy = clone $type;
            $copy->type = $value;

            return $copy;
        }

        if ($type instanceof LogicalTypeNode) {
            return self::mapMembers($type, $map);
        }

        if ($type instanceof NamedTypeNode) {
            return self::mapArguments($type, $map);
        }

        return $type;
    }

    /**
     * @param \Closure(TypeNode): TypeNode $map
     */
    private static function mapMembers(LogicalTypeNode $type, \Closure $map): LogicalTypeNode
    {
        $mapped = [];

        foreach ($type as $index => $member) {
            $value = $map($member);

            if ($value !== $member) {
                $mapped[$index] = $value;
            }
        }

        if ($mapped === []) {
            return $type;
        }

        $copy = clone $type;

        foreach ($mapped as $index => $value) {
            $copy->children[$index] = $value;
        }

        return $copy;
    }

    /**
     * @param \Closure(TypeNode): TypeNode $map
     */
    private static function mapArguments(NamedTypeNode $type, \Closure $map): NamedTypeNode
    {
        $mapped = [];

        foreach ($type->arguments ?? [] as $index => $argument) {
            if (!$argument instanceof TypedTemplateArgumentNode) {
                continue;
            }

            $value = $map($argument->value);

            if ($value !== $argument->value) {
                $mapped[$index] = $value;
            }
        }

        if ($mapped === []) {
            return $type;
        }

        $copy = clone $type;

        foreach ($mapped as $index => $value) {
            $argument = $copy->arguments?->children[$index] ?? null;

            \assert($argument instanceof TypedTemplateArgumentNode);

            $argument->value = $value;
        }

        return $copy;
    }

    /**
     * Returns the expansion of the built type of the name or the constant,
     * of the type as it is built in case of the decorated one is not
     * supported. {@see null} in case of no expander supports them or the
     * name is not a type, such as `max` of `int<0, max>`: no builder
     * supports it, the error of the definition has no cause.
     *
     * @param int<0, max> $depth
     * @throws TypeExpansionException in case of an expander fails or the
     *         building of the type fails
     */
    private function expandBuilt(TypeNode $type, int $depth): ?TypeNode
    {
        try {
            $built = $this->types->getType($type);
        } catch (TypeDefinitionException $e) {
            if ($e->getPrevious() === null) {
                return null;
            }

            throw TypeExpansionException::becauseTypeCannotBeExpanded($e);
        } catch (\Throwable $e) {
            throw TypeExpansionException::becauseTypeCannotBeExpanded($e);
        }

        $expansion = $this->expandType($built, $depth);

        if ($expansion !== null) {
            return $expansion;
        }

        $undecorated = $this->types->findUndecoratedType($type);

        if ($undecorated === null) {
            return null;
        }

        return $this->expandType($undecorated, $depth);
    }
}
