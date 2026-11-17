<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Request;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use TypeLang\Mapper\Bridge\Symfony\DependencyInjection\MapperDefinitions;
use TypeLang\Mapper\Bridge\Symfony\Request\Exception\InvalidRequestException;
use TypeLang\Mapper\DenormalizerInterface;
use TypeLang\Mapper\Diagnostics\Exception\ViolationException;

/**
 * Resolves the controller arguments marked with {@see MapBody} and
 * {@see MapQuery}.
 */
final class RequestValueResolver implements ValueResolverInterface
{
    public function __construct(
        /**
         * Mappers by the name
         */
        private readonly ContainerInterface $mappers,
        private readonly ?ValidatorInterface $validator = null,
    ) {}

    /**
     * @return list<mixed>
     * @throws InvalidRequestException in case of the data cannot be mapped
     *         or the mapped value is invalid
     * @throws BadRequestHttpException in case of the JSON body is malformed
     * @throws UnsupportedMediaTypeHttpException in case of the body is
     *         neither JSON nor a form
     * @throws \LogicException in case of the type of the argument is
     *         unknown or the mapper is not configured
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $attribute = $argument->getAttributesOfType(MapRequest::class, ArgumentMetadata::IS_INSTANCEOF)[0] ?? null;

        if (!$attribute instanceof MapRequest) {
            return [];
        }

        $data = self::readData($request, $attribute);

        if ($data === null && $argument->hasDefaultValue()) {
            return [$argument->getDefaultValue()];
        }

        if ($data === null && $argument->isNullable()) {
            return [null];
        }

        try {
            $value = $this->getMapper($attribute)->denormalize($data, self::getType($argument, $attribute));
        } catch (ViolationException $e) {
            throw InvalidRequestException::becauseDataIsNotMapped($e);
        }

        if ($attribute->isValidated && $this->validator !== null) {
            $violations = $this->validator->validate($value, null, $attribute->validationGroups);

            if (\count($violations) > 0) {
                throw InvalidRequestException::becauseValueIsInvalid($violations);
            }
        }

        return [$value];
    }

    /**
     * @throws BadRequestHttpException in case of the JSON body is malformed
     * @throws UnsupportedMediaTypeHttpException in case of the body is
     *         neither JSON nor a form
     */
    private static function readData(Request $request, MapRequest $attribute): mixed
    {
        if ($attribute instanceof MapQuery) {
            return $request->query->all();
        }

        $format = $request->getContentTypeFormat();

        if ($format === 'form') {
            $fields = $request->request->all();

            return $fields === [] ? null : $fields;
        }

        if ($request->getContent() === '') {
            return null;
        }

        return match ($format) {
            'json' => self::decodeJson($request->getContent()),
            default => throw new UnsupportedMediaTypeHttpException(\sprintf(
                'Request body of type "%s" is not supported, JSON or a form is expected',
                \addcslashes((string) $request->headers->get('Content-Type'), '"'),
            )),
        };
    }

    /**
     * @throws BadRequestHttpException in case of the JSON is malformed
     */
    private static function decodeJson(string $json): mixed
    {
        try {
            return \json_decode($json, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new BadRequestHttpException(
                \sprintf('Request body is not a valid JSON: %s', $e->getMessage()),
                $e,
            );
        }
    }

    /**
     * @return non-empty-string
     * @throws \LogicException in case of the type is unknown
     */
    private static function getType(ArgumentMetadata $argument, MapRequest $attribute): string
    {
        $type = $attribute->type ?? $argument->getType();

        if ($type === null || $type === '') {
            throw new \LogicException(\sprintf(
                'Type of the argument "$%s" cannot be read, write it in #[%s(type: ...)]',
                $argument->getName(),
                $attribute::class,
            ));
        }

        if ($argument->isNullable() && $attribute->type === null) {
            return '?' . $type;
        }

        return $type;
    }

    /**
     * @throws \LogicException in case of the mapper is not configured
     */
    private function getMapper(MapRequest $attribute): DenormalizerInterface
    {
        $name = $attribute->mapper ?? MapperDefinitions::DEFAULT_NAME;

        if (!$this->mappers->has($name)) {
            throw new \LogicException(\sprintf('Mapper "%s" is not configured', \addcslashes($name, '"')));
        }

        $mapper = $this->mappers->get($name);

        \assert($mapper instanceof DenormalizerInterface);

        return $mapper;
    }
}
