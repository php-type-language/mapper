<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader;

use TypeLang\Mapper\Mapping\Draft\AccessDraft;
use TypeLang\Mapper\Mapping\Draft\ClassDraft;
use TypeLang\Mapper\Mapping\Draft\MemberDraft;
use TypeLang\Mapper\Mapping\Metadata\Access\HookAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\Access\MethodAccessMetadata;
use TypeLang\Mapper\Mapping\Metadata\Access\SlotAccessMetadata;
use TypeLang\Mapper\Naming\NamingStrategyInterface;

/**
 * Names the fields of the given members without an explicit name by the
 * naming strategy of the class or, in case of the class has none, by the
 * given one. Adds no members.
 */
final class NamingStrategyMetadataReader implements MetadataReaderInterface
{
    public function __construct(
        /**
         * Strategy of the classes without their own, the names are kept in
         * case of {@see null}
         */
        private readonly ?NamingStrategyInterface $strategy = null,
    ) {}

    /**
     * A field is named by the strategy of the class declaring its member:
     * of the class itself, of a parent, otherwise of the mapper.
     */
    public function read(\ReflectionClass $class, ?ClassDraft $metadata = null): ClassDraft
    {
        $metadata ??= new ClassDraft($class->getName());

        foreach ($metadata->members as $member) {
            $strategy = $this->findStrategy(
                $class,
                $member,
                $metadata->namingStrategy,
                $metadata->parentNamingStrategies,
            );

            if ($strategy === null) {
                continue;
            }

            $member = $member
                ->withReadAccess(self::rename($member->read, $strategy))
                ->withWriteAccess(self::rename($member->write, $strategy));

            $metadata = $metadata->withMember($member);
        }

        return $metadata;
    }

    /**
     * @param \ReflectionClass<object> $class
     * @param NamingStrategyInterface|null $own strategy of the class
     * @param array<class-string, NamingStrategyInterface> $parents
     *        strategies of the parents
     */
    private function findStrategy(
        \ReflectionClass $class,
        MemberDraft $member,
        ?NamingStrategyInterface $own,
        array $parents,
    ): ?NamingStrategyInterface {
        $declaring = self::findDeclaringClass($class, $member);

        if ($declaring === null || $declaring === $class->name) {
            return $own ?? $this->strategy;
        }

        return $parents[$declaring] ?? $this->strategy;
    }

    /**
     * Returns the class declaring the property or the method of the member,
     * {@see null} for an access of the user.
     *
     * @param \ReflectionClass<object> $class
     */
    private static function findDeclaringClass(\ReflectionClass $class, MemberDraft $member): ?string
    {
        $access = $member->read->access ?? $member->write?->access;

        if ($access instanceof MethodAccessMetadata) {
            return $class->hasMethod($access->method) ? $class->getMethod($access->method)->class : null;
        }

        if (!$access instanceof SlotAccessMetadata && !$access instanceof HookAccessMetadata) {
            return null;
        }

        $owner = $access->class ?? $class->name;

        if (!\property_exists($owner, $access->property)) {
            return null;
        }

        return (new \ReflectionProperty($owner, $access->property))->class;
    }

    private static function rename(?AccessDraft $access, NamingStrategyInterface $strategy): ?AccessDraft
    {
        if ($access === null || $access->isNameExplicit) {
            return $access;
        }

        return $access->withName($strategy->rename($access->name), false);
    }
}
