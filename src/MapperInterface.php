<?php

declare(strict_types=1);

namespace TypeLang\Mapper;

/**
 * Converts values by types in both directions, the contract of
 * {@see Mapper} for a service depending on both directions or decorating
 * the mapper.
 */
interface MapperInterface extends
    DenormalizerInterface,
    NormalizerInterface {}
