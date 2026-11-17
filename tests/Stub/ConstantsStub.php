<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Stub;

final class ConstantsStub
{
    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';
    public const CODE_OK = 200;
    public const CODE_CREATED = 201;
    public const NOTHING = null;
    public const LIST = [1];
    public const STATUS = StatusStub::Active;
}
