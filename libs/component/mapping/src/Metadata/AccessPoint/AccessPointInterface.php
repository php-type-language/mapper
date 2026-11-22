<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata\AccessPoint;

/**
 * Place the mapper reads a value of a property from or writes it to, such
 * as the backing store or a hook of a PHP property. The metadata only
 * describe the place, the access strategies of the mapper execute it.
 */
interface AccessPointInterface {}
