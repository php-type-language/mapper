<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\Option;

use TypeLang\Mapper\Mapping\Draft\MemberDraft;
use TypeLang\Mapper\Mapping\Exception\MissingDependencyException;
use TypeLang\Mapper\Mapping\Reader\Access\AccessDirection;
use TypeLang\Mapper\Mapping\Reader\Source\ConfigSection;
use TypeLang\Mapper\Mapping\Reader\Source\ErrorCollector;
use TypeLang\Mapper\Mapping\Reader\Source\SourceContext;

/**
 * One option of a property read from its attributes and from its config,
 * such as the skip conditions. A wrong value is an error added to the
 * collector, the property is returned without it.
 *
 * @internal this is an internal library class, please do not use it in your code
 * @psalm-internal TypeLang\Mapper\Mapping\Reader
 */
interface PropertyOptionInterface
{
    /**
     * Returns the attributes of a property the option reads and the
     * direction of each, {@see null} for both. An attribute of a property
     * that is not mapped or of a direction the member does not take part in
     * is an error.
     *
     * @return array<class-string, AccessDirection|null>
     */
    public function getPropertyAttributes(): array;

    /**
     * @throws MissingDependencyException in case of the attributes need a
     *         package that is not installed
     */
    public function readPropertyAttributes(
        \ReflectionProperty $property,
        MemberDraft $metadata,
        SourceContext $context,
        ErrorCollector $errors,
    ): MemberDraft;

    /**
     * Returns the keys of a property config the option reads.
     *
     * @return list<non-empty-string>
     */
    public function getPropertyKeys(): array;

    /**
     * @throws MissingDependencyException in case of the config needs a
     *         package that is not installed
     */
    public function readPropertyConfig(
        ConfigSection $config,
        MemberDraft $metadata,
        ErrorCollector $errors,
    ): MemberDraft;
}
