<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Extractor;

use TypeLang\Mapper\Mapping\Metadata\DiscriminatorMetadata;
use TypeLang\Mapper\Mapping\Metadata\TypeMetadata;
use TypeLang\Type\NamedTypeNode;

/**
 * Extracts the tags of the classes from the map of a discriminator.
 *
 * ```
 * $index = DiscriminatorTagExtractor::extractIndex($discriminator);
 *
 * DiscriminatorTagExtractor::extract($index, Circle::class);      // 'circle'
 * DiscriminatorTagExtractor::extract($index, CircleProxy::class); // 'circle'
 * ```
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper
 */
final class DiscriminatorTagExtractor
{
    /**
     * Returns the class named by the type, {@see null} in case of the type
     * is not a name of a class or an interface.
     *
     * @return class-string|null
     */
    public static function extractClass(TypeMetadata $type): ?string
    {
        $node = $type->type;

        if (!$node instanceof NamedTypeNode || $node->arguments !== null || $node->fields !== null) {
            return null;
        }

        $name = $node->name->toClassString();

        if ($name === null || (!\class_exists($name) && !\interface_exists($name))) {
            return null;
        }

        return (new \ReflectionClass($name))->name;
    }

    /**
     * Returns the tags of the map by the classes of its types, the first tag
     * of a class wins.
     *
     * @return array<class-string, int|string>
     */
    public static function extractIndex(DiscriminatorMetadata $discriminator): array
    {
        $tags = [];

        foreach ($discriminator->map as $tag => $type) {
            $class = self::extractClass($type);

            if ($class !== null) {
                $tags[$class] ??= $tag;
            }
        }

        return $tags;
    }

    /**
     * Returns the tag of the class or of its nearest mapped parent, such as
     * for a proxy of a mapped class.
     *
     * @param array<class-string, int|string> $index
     * @param class-string $class
     */
    public static function extract(array $index, string $class): int|string|null
    {
        $tag = $index[$class] ?? null;

        if ($tag !== null) {
            return $tag;
        }

        $nearest = null;

        foreach ($index as $candidate => $candidateTag) {
            if (\is_a($class, $candidate, true) && ($nearest === null || \is_subclass_of($candidate, $nearest))) {
                $nearest = $candidate;
                $tag = $candidateTag;
            }
        }

        return $tag;
    }
}
