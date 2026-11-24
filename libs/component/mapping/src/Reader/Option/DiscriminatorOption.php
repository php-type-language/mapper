<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Option;

use TypeLang\Mapper\Mapping\DiscriminatorMap;
use TypeLang\Mapper\Mapping\Draft\ClassDraft;
use TypeLang\Mapper\Mapping\Exception\InvalidOptionValueException;
use TypeLang\Mapper\Mapping\Exception\UnknownKeyException;
use TypeLang\Mapper\Mapping\Metadata\DiscriminatorMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Mapper\Mapping\Reader\Source\AttributeFactory;
use TypeLang\Mapper\Mapping\Reader\Source\ConfigSection;
use TypeLang\Mapper\Mapping\Reader\Source\ErrorCollector;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContext;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContextFactory;
use TypeLang\Parser\TypeParserInterface;
use TypeLang\Type\NamedTypeNode;
use TypeLang\Type\TypeNode;

/**
 * Polymorphism of a class: {@see DiscriminatorMap} and the `discriminator`
 * key with the `field`, `map` and `otherwise` keys. The types of the map are
 * parsed by the place: with the imports of the file of the attribute, as
 * written in the config. A type of the map is a class, another one is a
 * type with a failure.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping\Reader
 */
final class DiscriminatorOption implements ClassOptionInterface
{
    private const KEY = 'discriminator';

    /**
     * Keys of the config of {@see self::KEY}
     */
    private const KEYS = ['field', 'map', 'otherwise'];

    public function __construct(
        /**
         * @var TypeParserInterface<TypeNode, string>
         */
        private readonly TypeParserInterface $parser,
    ) {}

    public function readClassAttributes(
        \ReflectionClass $class,
        ClassDraft $metadata,
        SourceContextFactory $contexts,
        ErrorCollector $errors,
    ): ClassDraft {
        $attributes = $class->getAttributes(DiscriminatorMap::class);

        if ($attributes === []) {
            return $metadata;
        }

        $context = $contexts->createFromClass($class);

        foreach (AttributeFactory::create($attributes, DiscriminatorMap::class, $context, $errors) as $attribute) {
            $discriminator = $this->create(
                field: $attribute->field,
                map: $attribute->map,
                otherwise: $attribute->otherwise,
                context: $context,
                placeOf: static fn(): SourceContext => $context,
                errors: $errors,
            );

            if ($discriminator !== null) {
                $metadata = $metadata->withDiscriminator($discriminator);
            }
        }

        return $metadata;
    }

    public function getClassKeys(): array
    {
        return [self::KEY];
    }

    public function readClassConfig(
        \ReflectionClass $class,
        ConfigSection $config,
        ClassDraft $metadata,
        ErrorCollector $errors,
    ): ClassDraft {
        if (!$config->has(self::KEY)) {
            return $metadata;
        }

        $values = $config->get(self::KEY);
        $context = $config->createContext(self::KEY);

        if (!\is_array($values)) {
            $errors->addError(
                InvalidOptionValueException::becauseValueIsNotMap(self::KEY, $class->name, $context->source),
            );

            return $metadata;
        }

        foreach ($values as $name => $_) {
            if (!\in_array($name, self::KEYS, true)) {
                $errors->addError(UnknownKeyException::becauseKeyIsUnknown(
                    $name,
                    $class->name,
                    $config->createContext(self::KEY, $name)->source,
                ));
            }
        }

        $discriminator = $this->create(
            field: $values['field'] ?? null,
            map: $values['map'] ?? null,
            otherwise: $values['otherwise'] ?? null,
            context: $context,
            placeOf: static fn(int|string ...$keys): SourceContext => $config->createContext(self::KEY, ...$keys),
            errors: $errors,
        );

        if ($discriminator === null) {
            return $metadata;
        }

        return $metadata->withDiscriminator($discriminator);
    }

    /**
     * Returns {@see null} in case of a value is wrong, its error is added.
     *
     * @param SourceContext $context place of the whole discriminator
     * @param \Closure(int|string...): SourceContext $placeOf returns the place
     *        of a value by its keys in the discriminator: `map`, `map.circle`
     */
    private function create(
        mixed $field,
        mixed $map,
        mixed $otherwise,
        SourceContext $context,
        \Closure $placeOf,
        ErrorCollector $errors,
    ): ?DiscriminatorMetadata {
        if (!\is_string($field) || $field === '') {
            $place = $placeOf('field');
            $errors->addError(InvalidOptionValueException::becauseValueIsNotNonEmptyString(
                'field',
                $place->target,
                $place->source,
            ));

            return null;
        }

        if (!\is_array($map) || $map === []) {
            $place = $placeOf('map');
            $errors->addError(InvalidOptionValueException::becauseValueIsNotNonEmptyMap(
                'map',
                $place->target,
                $place->source,
            ));

            return null;
        }

        $types = [];

        foreach ($map as $tag => $type) {
            $place = $placeOf('map', $tag);

            if (!\is_string($type) || $type === '') {
                $errors->addError(InvalidOptionValueException::becauseValueIsNotNonEmptyString(
                    'map',
                    $place->target,
                    $place->source,
                ));

                return null;
            }

            $types[$tag] = self::checkClass($context->parseType($this->parser, $type), $type, $context);
        }

        return new DiscriminatorMetadata(
            field: $field,
            map: $types,
            source: $context->source,
            default: $this->findDefault($otherwise, $context, $placeOf('otherwise'), $errors),
        );
    }

    /**
     * Returns the type of the map as is in case of it names a class or an
     * interface without arguments, a type with a failure otherwise.
     */
    private static function checkClass(TypeMetadata $type, string $definition, SourceContext $context): TypeMetadata
    {
        $node = $type->type;

        if ($node === null) {
            return $type;
        }

        if ($node instanceof NamedTypeNode && $node->arguments === null && $node->fields === null) {
            $class = $node->name->toClassString();

            if ($class !== null && (\class_exists($class) || \interface_exists($class))) {
                return $type;
            }
        }

        $message = \sprintf('type "%s" of the map is not a class', \addcslashes($definition, '"'));

        return TypeMetadata::createFromError($message, $context->source);
    }

    /**
     * @param SourceContext $context place the type is parsed in
     * @param SourceContext $place place of the value of `otherwise`
     */
    private function findDefault(
        mixed $otherwise,
        SourceContext $context,
        SourceContext $place,
        ErrorCollector $errors,
    ): ?TypeMetadata {
        if ($otherwise === null) {
            return null;
        }

        if (!\is_string($otherwise) || $otherwise === '') {
            $errors->addError(InvalidOptionValueException::becauseValueIsNotNonEmptyString(
                'otherwise',
                $place->target,
                $place->source,
            ));

            return null;
        }

        return $context->parseType($this->parser, $otherwise);
    }
}
