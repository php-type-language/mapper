<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Meta\Reader;

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
     * @throws \InvalidArgumentException
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

    private function findBuildTargetAttribute(\ReflectionParameter $param): ?InjectTarget
    {
        $attributes = $param->getAttributes(InjectTarget::class, \ReflectionAttribute::IS_INSTANCEOF);

        foreach ($attributes as $attribute) {
            return $attribute->newInstance();
        }

        return null;
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function getParameterMetadata(\ReflectionParameter $param): ParameterMetadata
    {
        $attribute = $this->findBuildTargetAttribute($param);

        $metadata = match (true) {
            $attribute instanceof TargetSealedShapeFlag
                => $this->getSealedShapeFlagParameterMetadata($param),
            $attribute instanceof TargetTypeName
                => $this->getTypeNameParameterMetadata($param),
            $attribute instanceof TargetShapeFields
                => $this->getShapeFieldsParameterMetadata($param),
            $attribute instanceof TargetTemplateArgument,
            $attribute === null
                => $this->getTemplateParameterMetadata($param, $attribute),
            default => throw new \InvalidArgumentException(\sprintf(
                'Unsupported attribute of type %s',
                $attribute::class,
            )),
        };

        if ($param->isDefaultValueAvailable()) {
            $metadata = $metadata->withDefaultValue(
                value: $param->getDefaultValue(),
            );
        }

        return $metadata;
    }

    private function getTypeNameParameterMetadata(\ReflectionParameter $param): TypeNameParameterMetadata
    {
        return new TypeNameParameterMetadata(
            // @phpstan-ignore-next-line : Reflection position value cannot be less than 0
            position: $param->getPosition(),
            name: $param->getName(),
        );
    }

    private function getSealedShapeFlagParameterMetadata(\ReflectionParameter $param): SealedShapeFlagParameterMetadata
    {
        return new SealedShapeFlagParameterMetadata(
            // @phpstan-ignore-next-line : Reflection position value cannot be less than 0
            position: $param->getPosition(),
            name: $param->getName(),
        );
    }

    private function getShapeFieldsParameterMetadata(\ReflectionParameter $param): ShapeFieldsParameterMetadata
    {
        return new ShapeFieldsParameterMetadata(
            // @phpstan-ignore-next-line : Reflection position value cannot be less than 0
            position: $param->getPosition(),
            name: $param->getName(),
        );
    }

    private function getTemplateParameterMetadata(
        \ReflectionParameter $param,
        ?TargetTemplateArgument $attr
    ): TemplateParameterMetadata {
        $metadata = new TemplateParameterMetadata(
            // @phpstan-ignore-next-line : Reflection position value cannot be less than 0
            position: $param->getPosition(),
            name: $param->getName(),
        );

        if ($attr !== null) {
            foreach ($attr->allowedIdentifiers as $identifier) {
                $metadata = $metadata->withAllowedIdentifier($identifier);
            }
        }

        return $metadata;
    }
}
