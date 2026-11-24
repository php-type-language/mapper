<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Option;

use TypeLang\Mapper\Mapping\Draft\MemberDraft;
use TypeLang\Mapper\Mapping\Exception\InvalidOptionValueException;
use TypeLang\Mapper\Mapping\MapGetter;
use TypeLang\Mapper\Mapping\MapSetter;
use TypeLang\Mapper\Mapping\MapType;
use TypeLang\Mapper\Mapping\Reader\Access\AccessDirection;
use TypeLang\Mapper\Mapping\Reader\Source\AttributeFactory;
use TypeLang\Mapper\Mapping\Reader\Source\ConfigSection;
use TypeLang\Mapper\Mapping\Reader\Source\ErrorCollector;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContext;
use TypeLang\Parser\TypeParserInterface;
use TypeLang\Type\TypeNode;

/**
 * Type of both directions of a property and the strictness of its
 * denormalization: {@see MapType} and the `type` and `strict` keys. The
 * type of a getter, a setter or a hook is given by {@see MapGetter} and
 * {@see MapSetter}. The `strict` key of a member that is not denormalized
 * is an error, `strict` of {@see MapType} is not applied to it.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping\Reader
 */
final class TypeOption implements PropertyOptionInterface
{
    public const TYPE_KEY = 'type';

    public const STRICT_KEY = 'strict';

    public function __construct(
        /**
         * @var TypeParserInterface<TypeNode, string>
         */
        private readonly TypeParserInterface $parser,
    ) {}

    public function getPropertyAttributes(): array
    {
        return [MapType::class => null];
    }

    public function readPropertyAttributes(
        \ReflectionProperty $property,
        MemberDraft $metadata,
        SourceContext $context,
        ErrorCollector $errors,
    ): MemberDraft {
        $attributes = AttributeFactory::create(
            $property->getAttributes(MapType::class),
            MapType::class,
            $context,
            $errors,
        );

        foreach ($attributes as $attribute) {
            $metadata = $metadata->withType($context->parseType($this->parser, $attribute->type));

            if ($attribute->strict !== null && $metadata->write !== null) {
                $metadata = $metadata->withStrict($attribute->strict);
            }
        }

        return $metadata;
    }

    public function getPropertyKeys(): array
    {
        return [self::TYPE_KEY, self::STRICT_KEY];
    }

    public function readPropertyConfig(
        ConfigSection $config,
        MemberDraft $metadata,
        ErrorCollector $errors,
    ): MemberDraft {
        if ($config->has(self::TYPE_KEY)) {
            $metadata = $this->readConfigType($config, $metadata, $errors);
        }

        if (!$config->has(self::STRICT_KEY)) {
            return $metadata;
        }

        return self::readConfigStrict($config, $metadata, $errors);
    }

    private function readConfigType(ConfigSection $config, MemberDraft $metadata, ErrorCollector $errors): MemberDraft
    {
        $value = $config->get(self::TYPE_KEY);
        $context = $config->createContext(self::TYPE_KEY);

        if (\is_string($value) && $value !== '') {
            return $metadata->withType($context->parseType($this->parser, $value));
        }

        $errors->addError(InvalidOptionValueException::becauseValueIsNotNonEmptyString(
            self::TYPE_KEY,
            $context->target,
            $context->source,
        ));

        return $metadata;
    }

    private static function readConfigStrict(
        ConfigSection $config,
        MemberDraft $metadata,
        ErrorCollector $errors,
    ): MemberDraft {
        $isStrict = $config->get(self::STRICT_KEY);
        $context = $config->createContext(self::STRICT_KEY);

        if (!\is_bool($isStrict)) {
            $errors->addError(InvalidOptionValueException::becauseValueIsNotBoolean(
                self::STRICT_KEY,
                $context->target,
                $context->source,
            ));

            return $metadata;
        }

        if ($metadata->write === null) {
            $errors->addError(InvalidOptionValueException::becauseDirectionIsMissing(
                self::STRICT_KEY,
                AccessDirection::Write->getParticiple(),
                $context->target,
                $context->source,
            ));

            return $metadata;
        }

        return $metadata->withStrict($isStrict);
    }
}
