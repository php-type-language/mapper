<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Exception;

use TypeLang\Parser\Node\NodeInterface;

class TypeNotFoundException extends TypeException
{
    /**
     * @var int<0, max>
     */
    final public const ERROR_CODE_TYPE_NOT_FOUND = 0x01 + parent::ERROR_CODE_LAST;

    /**
     * @var int<0, max>
     */
    final public const ERROR_CODE_TYPE_NON_PARSABLE = 0x02 + parent::ERROR_CODE_LAST;

    /**
     * @var int<0, max>
     * @psalm-suppress InvalidConstantAssignmentValue
     */
    protected const ERROR_CODE_LAST = self::ERROR_CODE_TYPE_NON_PARSABLE;

    /**
     * @param non-empty-string $name
     */
    public static function fromNonFoundType(string $name): self
    {
        $message = \sprintf('Type "%s" not registered', $name);

        return new self($message, (int) self::ERROR_CODE_TYPE_NOT_FOUND);
    }

    /**
     * @param class-string<NodeInterface> $node
     */
    public static function fromNonParsableType(string $node): self
    {
        $message = \sprintf('Unsupported "%s" type node', $node);

        return new self($message, (int) self::ERROR_CODE_TYPE_NON_PARSABLE);
    }
}
