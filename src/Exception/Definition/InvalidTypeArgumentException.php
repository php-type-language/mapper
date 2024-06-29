<?php

declare(strict_types=1);

namespace Serafim\Mapper\Exception\Definition;

class InvalidTypeArgumentException extends TypeArgumentException
{
    public static function fromParamReflection(
        \ReflectionParameter $param,
        string $expected,
        int $code = 0,
        \Throwable $prev = null,
    ): self {
        $message = \vsprintf('Parameter $%s (%d) of %s must be of type %s', [
            $param->getName(),
            $param->getPosition(),
            self::getParamContext($param),
            $expected,
        ]);

        return new static($message, $code, $prev);
    }

    private static function getParamContext(\ReflectionParameter $param): string
    {
        $function = $param->getDeclaringFunction();
        $class = $param->getDeclaringClass();

        if ($class === null) {
            return \vsprintf('%s()', [
                $function->getName(),
            ]);
        }

        if ($class->isAnonymous()) {
            return \vsprintf('class@anonymous::%s()', [
                $function->getName(),
            ]);
        }

        return \vsprintf('%s::%s()', [
            $class->getName(),
            $function->getName(),
        ]);
    }
}
