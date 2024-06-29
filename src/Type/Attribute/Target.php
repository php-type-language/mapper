<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Type\Attribute;

enum Target
{
    case TypeName;
    case TemplateArgument;
    case ShapeFields;
    case SealedShapeFlag;
}
