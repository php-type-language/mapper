<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Metadata\Access;

/**
 * Place the mapper reads the value of a field from or writes it to, such
 * as the backing store or a hook of a PHP property. The metadata only
 * describe the place, the access strategies of the mapper execute it.
 */
abstract class AccessMetadata {}
