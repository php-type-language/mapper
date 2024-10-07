<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Driver;

use TypeLang\Mapper\Mapping\MapProperty;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;
use TypeLang\Mapper\Type\Repository\RepositoryInterface;

final class AttributeDriver extends LoadableDriver
{
    public function __construct(
        DriverInterface $delegate = new ReflectionDriver(),
    ) {
        parent::__construct($delegate);
    }

    #[\Override]
    protected function load(\ReflectionClass $reflection, ClassMetadata $class, RepositoryInterface $types): void
    {
        foreach ($reflection->getProperties() as $property) {
            $attribute = $this->findPropertyAttribute(
                property: $property,
                class: MapProperty::class,
            );

            if ($attribute === null) {
                continue;
            }

            $metadata = $class->getPropertyOrCreate($property->getName());

            if ($attribute->name !== null) {
                $metadata->setExportName($attribute->name);
            }

            if ($attribute->type !== null) {
                $metadata->setType($types->getByType(
                    type: $attribute->type,
                    class: $reflection,
                ));
            }
        }
    }

    /**
     * @template TAttribute of object
     *
     * @param class-string<TAttribute> $class
     *
     * @return TAttribute|null
     */
    private function findPropertyAttribute(\ReflectionProperty $property, string $class): ?object
    {
        $attributes = $property->getAttributes($class, \ReflectionAttribute::IS_INSTANCEOF);

        foreach ($attributes as $attribute) {
            /** @var TAttribute */
            return $attribute->newInstance();
        }

        return null;
    }
}
