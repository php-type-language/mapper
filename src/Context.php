<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

class Context
{
    /**
     * Default value for {@see $strictTypes} option.
     */
    public const STRICT_TYPES_DEFAULT_VALUE = true;

    /**
     * Default value for {@see $objectsAsArrays} option.
     */
    public const OBJECTS_AS_ARRAYS_DEFAULT_VALUE = true;

    public function __construct(
        /**
         * If this option contains {@see false}, then type conversion is
         * allowed during transformation.
         */
        protected readonly ?bool $strictTypes = null,
        /**
         * If this option contains {@see true}, then objects are converted to
         * associative arrays, otherwise anonymous {@see object} will be returned.
         */
        protected readonly ?bool $objectsAsArrays = null
    ) {}

    /**
     * Returns current {@see $strictTypes} option or default value
     * in case of option is not set.
     *
     * @api
     */
    public function isStrictTypesEnabled(): bool
    {
        return $this->strictTypes ?? self::STRICT_TYPES_DEFAULT_VALUE;
    }

    /**
     * Returns current {@see $objectsAsArrays} option or default value
     * in case of option is not set.
     *
     * @api
     */
    public function isObjectsAsArrays(): bool
    {
        return $this->objectsAsArrays ?? self::OBJECTS_AS_ARRAYS_DEFAULT_VALUE;
    }

    public function merge(?Context $context): self
    {
        if ($context === null) {
            return $this;
        }

        return new self(
            strictTypes: $context->strictTypes ?? $this->strictTypes,
            objectsAsArrays: $context->objectsAsArrays ?? $this->objectsAsArrays,
        );
    }
}
