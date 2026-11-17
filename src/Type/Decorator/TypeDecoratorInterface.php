<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Decorator;

use TypeLang\Mapper\Context\BuildContext;
use TypeLang\Mapper\Type\TypeInterface;
use TypeLang\Type\TypeNode;

/**
 * Wraps a built type, such as to preprocess the input of all enums. Applied
 * once per type before the repository keeps it.
 *
 * ```
 * public function decorate(
 *     TypeInterface $type,
 *     TypeNode $node,
 *     BuildContext $context,
 * ): TypeInterface {
 *     if (!$type instanceof BackedEnumType) {
 *         return $type;
 *     }
 *
 *     return new TrimmingType($type);
 * }
 * ```
 */
interface TypeDecoratorInterface
{
    /**
     * Returns the type as is in case of it is not intercepted. The type can
     * be chosen by its node (a name, a class) or by its built form.
     *
     * @template TArg
     * @param TypeInterface<TArg> $type
     * @return TypeInterface<TArg>
     */
    public function decorate(TypeInterface $type, TypeNode $node, BuildContext $context): TypeInterface;
}
