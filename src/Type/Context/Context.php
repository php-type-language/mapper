<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Context;

use TypeLang\Mapper\Path\Entry\ObjectEntry;

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

    /**
     * Default value for {@see $detailedTypes} option.
     */
    public const DETAILED_TYPES_DEFAULT_VALUE = true;

    public function __construct(
        /**
         * If this option contains {@see false}, then type conversion is
         * allowed during transformation.
         */
        protected ?bool $strictTypes = null,
        /**
         * If this option contains {@see true}, then objects are converted to
         * associative arrays, otherwise anonymous {@see ObjectEntry} will be returned.
         */
        protected ?bool $objectsAsArrays = null,
        /**
         * If this option contains {@see true}, then all composite types will
         * be displayed along with detailed fields/values.
         */
        protected ?bool $detailedTypes = null
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

    /**
     * Returns current {@see $detailedTypes} option or default value
     * in case of option is not set.
     *
     * @api
     */
    public function isDetailedTypes(): bool
    {
        return $this->detailedTypes ?? self::DETAILED_TYPES_DEFAULT_VALUE;
    }

    public function with(?Context $context): self
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
