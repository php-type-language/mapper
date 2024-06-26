<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Meta\Reader;

use TypeLang\Mapper\Exception\Definition\InvalidTypeArgumentException;
use TypeLang\Mapper\Exception\Definition\UnsupportedAttributeException;
use TypeLang\Mapper\Type\Attribute\InjectTarget;
use TypeLang\Mapper\Type\Attribute\TargetSealedShapeFlag;
use TypeLang\Mapper\Type\Attribute\TargetShapeFields;
use TypeLang\Mapper\Type\Attribute\TargetTemplateArgument;
use TypeLang\Mapper\Type\Attribute\TargetTypeName;
use TypeLang\Mapper\Type\Meta\ParameterMetadata;
use TypeLang\Mapper\Type\Meta\SealedShapeFlagParameterMetadata;
use TypeLang\Mapper\Type\Meta\ShapeFieldsParameterMetadata;
use TypeLang\Mapper\Type\Meta\TemplateParameterMetadata;
use TypeLang\Mapper\Type\Meta\TypeMetadata;
use TypeLang\Mapper\Type\Meta\TypeNameParameterMetadata;

final class AttributeReader implements ReaderInterface
{
    /**
     * @throws UnsupportedAttributeException
     * @throws InvalidTypeArgumentException
     */
    public function getTypeMetadata(\ReflectionClass $class): TypeMetadata
    {
        $metadata = new TypeMetadata($class->getName());

        $constructor = $class->getConstructor();

        if ($constructor !== null) {
            foreach ($constructor->getParameters() as $parameter) {
                $metadata = $metadata->withAddedParameter(
                    parameter: $this->getParameterMetadata($parameter),
                );
            }
        }

        return $metadata;
    }

    private function findBuildTargetAttribute(\ReflectionParameter $parameter): ?InjectTarget
    {
        $attributes = $parameter->getAttributes(InjectTarget::class, \ReflectionAttribute::IS_INSTANCEOF);

        foreach ($attributes as $attribute) {
            return $attribute->newInstance();
        }

        return null;
    }

    /**
     * @throws UnsupportedAttributeException
     * @throws InvalidTypeArgumentException
     */
    private function getParameterMetadata(\ReflectionParameter $parameter): ParameterMetadata
    {
        $attribute = $this->findBuildTargetAttribute($parameter);

        $metadata = match (true) {
            $attribute instanceof TargetSealedShapeFlag
                => $this->getSealedShapeFlagParameterMetadata($parameter, $attribute),
            $attribute instanceof TargetTypeName
                => $this->getTypeNameParameterMetadata($parameter, $attribute),
            $attribute instanceof TargetShapeFields
                => $this->getShapeFieldsParameterMetadata($parameter, $attribute),
            $attribute instanceof TargetTemplateArgument,
            $attribute === null
                => $this->getTemplateParameterMetadata($parameter, $attribute),
            default => throw UnsupportedAttributeException::fromAttributeName($attribute),
        };

        if ($parameter->isDefaultValueAvailable()) {
            $metadata = $metadata->withDefaultValue(
                value: $parameter->getDefaultValue(),
            );
        }

        return $metadata;
    }

    /**
     * @param non-empty-string $type
     */
    private function isType(\ReflectionParameter $parameter, string $type): bool
    {
        $actualType = $parameter->getType();

        return $actualType instanceof \ReflectionNamedType
            && $actualType->getName() === $type;
    }

    /**
     * @throws InvalidTypeArgumentException
     */
    private function getTypeNameParameterMetadata(
        \ReflectionParameter $parameter,
        TargetTypeName $attribute
    ): TypeNameParameterMetadata {
        if (!$this->isType($parameter, 'string')) {
            throw InvalidTypeArgumentException::fromParamReflection(
                param: $parameter,
                expected: 'string for type name injection',
                code: 1,
            );
        }

        return new TypeNameParameterMetadata(
            // @phpstan-ignore-next-line : Reflection position value cannot be less than 0
            position: $parameter->getPosition(),
            name: $parameter->getName(),
        );
    }

    /**
     * @throws InvalidTypeArgumentException
     */
    private function getSealedShapeFlagParameterMetadata(
        \ReflectionParameter $parameter,
        TargetSealedShapeFlag $attribute
    ): SealedShapeFlagParameterMetadata {
        if (!$this->isType($parameter, 'bool')) {
            throw InvalidTypeArgumentException::fromParamReflection(
                param: $parameter,
                expected: 'bool for sealed flag injection',
                code: 2,
            );
        }

        return new SealedShapeFlagParameterMetadata(
            // @phpstan-ignore-next-line : Reflection position value cannot be less than 0
            position: $parameter->getPosition(),
            name: $parameter->getName(),
        );
    }

    /**
     * @throws InvalidTypeArgumentException
     */
    private function getShapeFieldsParameterMetadata(
        \ReflectionParameter $parameter,
        TargetShapeFields $attribute
    ): ShapeFieldsParameterMetadata {
        if (!$this->isType($parameter, 'bool')) {
            throw InvalidTypeArgumentException::fromParamReflection(
                param: $parameter,
                expected: 'array<array-key, TypeInterface> for shape fields injection',
                code: 3,
            );
        }

        return new ShapeFieldsParameterMetadata(
            // @phpstan-ignore-next-line : Reflection position value cannot be less than 0
            position: $parameter->getPosition(),
            name: $parameter->getName(),
        );
    }

    private function getTemplateParameterMetadata(
        \ReflectionParameter $parameter,
        ?TargetTemplateArgument $attribute
    ): TemplateParameterMetadata {
        $metadata = new TemplateParameterMetadata(
            // @phpstan-ignore-next-line : Reflection position value cannot be less than 0
            position: $parameter->getPosition(),
            name: $parameter->getName(),
        );

        if ($attribute !== null) {
            foreach ($attribute->allowedIdentifiers as $identifier) {
                $metadata = $metadata->withAllowedIdentifier($identifier);
            }
        }

        return $metadata;
    }
}
