<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

use TypeLang\Mapper\Context\Context;
use TypeLang\Mapper\Hook\AfterDenormalizationHookInterface;
use TypeLang\Mapper\Hook\AfterNormalizationHookInterface;
use TypeLang\Mapper\Hook\BeforeDenormalizationHookInterface;
use TypeLang\Mapper\Hook\BeforeNormalizationHookInterface;
use TypeLang\Mapper\Mapping\Metadata\ClassMetadata;

/**
 * Hook of one class at every point: runs the given closures and records the
 * names of the points it is run at.
 */
final class HookStub implements
    BeforeDenormalizationHookInterface,
    AfterDenormalizationHookInterface,
    BeforeNormalizationHookInterface,
    AfterNormalizationHookInterface
{
    /**
     * @var list<non-empty-string>
     */
    public array $calls = [];

    public function __construct(
        /**
         * @var class-string
         */
        private readonly string $class,
        /**
         * @var (\Closure(mixed): mixed)|null
         */
        private readonly ?\Closure $beforeDenormalize = null,
        /**
         * @var (\Closure(object, mixed): void)|null
         */
        private readonly ?\Closure $afterDenormalize = null,
        /**
         * @var (\Closure(object): object)|null
         */
        private readonly ?\Closure $beforeNormalize = null,
        /**
         * @var (\Closure(object, array<mixed>): array<mixed>)|null
         */
        private readonly ?\Closure $afterNormalize = null,
    ) {}

    public function isSupported(ClassMetadata $metadata): bool
    {
        return $metadata->name === $this->class;
    }

    public function beforeDenormalize(ClassMetadata $metadata, mixed $data, Context $context): mixed
    {
        $this->calls[] = 'beforeDenormalize';

        if ($this->beforeDenormalize === null) {
            return $data;
        }

        return ($this->beforeDenormalize)($data);
    }

    public function afterDenormalize(ClassMetadata $metadata, object $object, mixed $data, Context $context): void
    {
        $this->calls[] = 'afterDenormalize';

        if ($this->afterDenormalize !== null) {
            ($this->afterDenormalize)($object, $data);
        }
    }

    public function beforeNormalize(ClassMetadata $metadata, object $object, Context $context): object
    {
        $this->calls[] = 'beforeNormalize';

        if ($this->beforeNormalize === null) {
            return $object;
        }

        /** @phpstan-ignore-next-line return.type the closure keeps the class */
        return ($this->beforeNormalize)($object);
    }

    public function afterNormalize(ClassMetadata $metadata, object $object, array $data, Context $context): array
    {
        $this->calls[] = 'afterNormalize';

        if ($this->afterNormalize === null) {
            return $data;
        }

        return ($this->afterNormalize)($object, $data);
    }
}
