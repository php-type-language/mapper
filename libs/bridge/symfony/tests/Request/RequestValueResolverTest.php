<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Tests\Request;

use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\Validator\Validation;
use TypeLang\Mapper\Bridge\Symfony\Request\Exception\InvalidRequestException;
use TypeLang\Mapper\Bridge\Symfony\Request\MapBody;
use TypeLang\Mapper\Bridge\Symfony\Request\MapQuery;
use TypeLang\Mapper\Bridge\Symfony\Request\MapRequest;
use TypeLang\Mapper\Bridge\Symfony\Request\RequestValueResolver;
use TypeLang\Mapper\Bridge\Symfony\Tests\Stub\UserStub;
use TypeLang\Mapper\Bridge\Symfony\Tests\TestCase;
use TypeLang\Mapper\Context\Configuration;
use TypeLang\Mapper\Mapper;

final class RequestValueResolverTest extends TestCase
{
    public function testArgumentWithoutAttributeIsSkipped(): void
    {
        $argument = new ArgumentMetadata('user', UserStub::class, false, false, null);

        self::assertSame([], self::resolve(self::createJsonRequest('{}'), $argument));
    }

    public function testJsonBodyIsMappedByArgumentType(): void
    {
        $result = self::resolve(
            self::createJsonRequest('{"userName": "admin", "age": "42"}'),
            self::createArgument(new MapBody()),
        );

        self::assertCount(1, $result);
        self::assertInstanceOf(UserStub::class, $result[0]);
        self::assertSame('admin', $result[0]->userName);
        self::assertSame(42, $result[0]->age);
    }

    public function testBodyIsMappedByTypeOfAttribute(): void
    {
        $result = self::resolve(
            self::createJsonRequest('[1, "2"]'),
            new ArgumentMetadata('ids', 'array', false, false, null, false, [new MapBody(type: 'list<int>')]),
        );

        self::assertSame([[1, 2]], $result);
    }

    public function testFormBodyIsMapped(): void
    {
        $request = Request::create('/', 'POST', ['userName' => 'admin', 'age' => '42']);
        $request->headers->set('Content-Type', 'application/x-www-form-urlencoded');

        $result = self::resolve($request, self::createArgument(new MapBody()));

        self::assertInstanceOf(UserStub::class, $result[0]);
        self::assertSame(42, $result[0]->age);
    }

    public function testQueryIsMapped(): void
    {
        $result = self::resolve(
            Request::create('/?userName=admin&age=42'),
            self::createArgument(new MapQuery()),
        );

        self::assertInstanceOf(UserStub::class, $result[0]);
        self::assertSame('admin', $result[0]->userName);
    }

    public function testEmptyBodyGivesNullToNullableArgument(): void
    {
        $result = self::resolve(
            Request::create('/', 'POST'),
            self::createArgument(new MapBody(), isNullable: true),
        );

        self::assertSame([null], $result);
    }

    public function testNamedMapperIsUsed(): void
    {
        $this->expectException(InvalidRequestException::class);

        self::resolve(
            self::createJsonRequest('{"userName": "admin", "age": "42"}'),
            self::createArgument(new MapBody(mapper: 'strict')),
        );
    }

    public function testDataThatCannotBeMappedIsUnprocessable(): void
    {
        try {
            self::resolve(
                self::createJsonRequest('{"userName": "admin", "age": "old"}'),
                self::createArgument(new MapBody()),
            );
        } catch (InvalidRequestException $e) {
            self::assertSame(422, $e->getStatusCode());
            self::assertSame('$.age', $e->violations[0]['path']);
            self::assertStringStartsWith('$.age: ', $e->getMessage());

            return;
        }

        self::fail('The data is mapped');
    }

    public function testInvalidValueIsUnprocessable(): void
    {
        try {
            self::resolve(
                self::createJsonRequest('{"userName": "ad", "age": 42}'),
                self::createArgument(new MapBody()),
            );
        } catch (InvalidRequestException $e) {
            self::assertSame('$.userName', $e->violations[0]['path']);

            return;
        }

        self::fail('The value is validated');
    }

    public function testValidationCanBeDisabled(): void
    {
        $result = self::resolve(
            self::createJsonRequest('{"userName": "ad", "age": 42}'),
            self::createArgument(new MapBody(isValidated: false)),
        );

        self::assertInstanceOf(UserStub::class, $result[0]);
    }

    public function testMalformedJsonIsBadRequest(): void
    {
        $this->expectException(BadRequestHttpException::class);

        self::resolve(self::createJsonRequest('{'), self::createArgument(new MapBody()));
    }

    public function testUnsupportedBodyIsRejected(): void
    {
        $request = Request::create('/', 'POST', content: '<user/>');
        $request->headers->set('Content-Type', 'application/xml');

        $this->expectException(UnsupportedMediaTypeHttpException::class);

        self::resolve($request, self::createArgument(new MapBody()));
    }

    public function testUnknownMapperIsRejected(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Mapper "admin" is not configured');

        self::resolve(self::createJsonRequest('{}'), self::createArgument(new MapBody(mapper: 'admin')));
    }

    /**
     * @return list<mixed>
     */
    private static function resolve(Request $request, ArgumentMetadata $argument): array
    {
        $resolver = new RequestValueResolver(
            new ServiceLocator([
                'default' => static fn(): Mapper => new Mapper(),
                'strict' => static fn(): Mapper => new Mapper(new Configuration(isStrict: true)),
            ]),
            Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator(),
        );

        return \array_values([...$resolver->resolve($request, $argument)]);
    }

    private static function createArgument(MapRequest $attribute, bool $isNullable = false): ArgumentMetadata
    {
        return new ArgumentMetadata('user', UserStub::class, false, false, null, $isNullable, [$attribute]);
    }

    private static function createJsonRequest(string $json): Request
    {
        $request = Request::create('/', 'POST', content: $json);
        $request->headers->set('Content-Type', 'application/json');

        return $request;
    }
}
