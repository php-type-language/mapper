<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Tests\Type;

use TypeLang\Mapper\Configuration;
use TypeLang\Mapper\Exception\InvalidValueException;
use TypeLang\Mapper\Exception\TypeDefinitionException;
use TypeLang\Mapper\Tests\Stub\AddressStub;
use TypeLang\Mapper\Tests\Stub\Animal\AnimalStub;
use TypeLang\Mapper\Tests\Stub\Animal\PuppyStub;
use TypeLang\Mapper\Tests\Stub\Shape\CircleStub;
use TypeLang\Mapper\Tests\Stub\Shape\FallbackShapeStub;
use TypeLang\Mapper\Tests\Stub\Shape\ForeignMapStub;
use TypeLang\Mapper\Tests\Stub\Shape\GroupStub;
use TypeLang\Mapper\Tests\Stub\Shape\LabeledStub;
use TypeLang\Mapper\Tests\Stub\Shape\MessageShapeStub;
use TypeLang\Mapper\Tests\Stub\Shape\ShapeStub;
use TypeLang\Mapper\Tests\Stub\Shape\SquareStub;
use TypeLang\Mapper\Tests\TestCase;

final class DiscriminatorTypeTest extends TestCase
{
    public function testAbstractBaseIsBuiltAsDiscriminatorType(): void
    {
        self::assertInstanceOf(
            CircleStub::class,
            self::createMapper()->denormalize(['type' => 'circle', 'radius' => 1], ShapeStub::class),
        );
    }

    public function testTagSelectsClassOfData(): void
    {
        self::assertDenormalizes(ShapeStub::class, ['type' => 'circle', 'radius' => 1], self::createCircle(1));
    }

    public function testClassOfMapIsResolvedRelativeToNamespace(): void
    {
        self::assertDenormalizes(ShapeStub::class, (object) ['type' => 'square', 'side' => 2], self::createSquare(2));
    }

    public function testNestedPolymorphicDataIsDenormalized(): void
    {
        $group = new GroupStub();
        $group->children = [self::createCircle(1), self::createSquare(2)];

        self::assertDenormalizes(
            ShapeStub::class,
            [
                'type' => 'group',
                'children' => [['type' => 'circle', 'radius' => 1], ['type' => 'square', 'side' => 2]],
            ],
            $group,
        );
    }

    public function testMissingTagIsRejectedWithPathOfField(): void
    {
        self::assertDenormalizationFails(
            ShapeStub::class,
            ['radius' => 1],
            '$.type: Required field "type" is missing',
        );
    }

    public function testUnknownTagIsRejectedWithKnownTags(): void
    {
        self::assertDenormalizationFails(
            ShapeStub::class,
            ['type' => 'triangle'],
            '$.type: Value must be of type \'circle\'|\'square\'|\'group\', but "triangle" given',
        );
    }

    public function testErrorOfSelectedClassHasPathOfData(): void
    {
        self::assertDenormalizationFails(
            ShapeStub::class,
            ['type' => 'group', 'children' => [['type' => 'circle', 'radius' => 'x']]],
            '$.children[0].radius: Value must be of type int, but "x" given',
        );
    }

    public function testMessageOfBaseClassReplacesMessageOfMapper(): void
    {
        self::assertDenormalizationFails(MessageShapeStub::class, 'x', '$: Shape is expected, "x" given');
        self::assertNormalizationFails(MessageShapeStub::class, 42, '$: Shape is expected, 42 given');
    }

    public function testDataWithUnknownOrMissingTagIsMappedToDefault(): void
    {
        $fallback = new FallbackShapeStub();
        $fallback->label = 'plain';

        self::assertDenormalizes(FallbackShapeStub::class, ['kind' => 'unknown', 'label' => 'plain'], $fallback);
        self::assertDenormalizes(FallbackShapeStub::class, ['label' => 'plain'], $fallback);

        $labeled = new LabeledStub();
        $labeled->size = 3;

        self::assertDenormalizes(FallbackShapeStub::class, ['kind' => 'labeled', 'size' => 3], $labeled);
    }

    public function testTagIsAddedFirstOnNormalization(): void
    {
        self::assertNormalizes(ShapeStub::class, self::createCircle(1), ['type' => 'circle', 'radius' => 1]);
    }

    public function testTagMappedByClassItselfIsKept(): void
    {
        self::assertNormalizes(ShapeStub::class, self::createSquare(2), ['type' => 'square', 'side' => 2]);
    }

    public function testTagIsAddedToObjectNormalizedAsStdClass(): void
    {
        $mapper = self::createMapper(config: new Configuration(normalizeAsArray: false));

        self::assertEquals(
            (object) ['type' => 'circle', 'radius' => 1],
            $mapper->normalize(self::createCircle(1), ShapeStub::class),
        );
    }

    public function testNestedPolymorphicObjectsAreNormalizedWithTags(): void
    {
        $group = new GroupStub();
        $group->children = [self::createCircle(1)];

        self::assertNormalizes(
            ShapeStub::class,
            $group,
            ['type' => 'group', 'children' => [['type' => 'circle', 'radius' => 1]]],
        );
    }

    public function testObjectOfClassOutsideMapIsRejected(): void
    {
        $mapper = self::createMapper();
        $object = new class extends ShapeStub {};

        self::assertFalse($mapper->isNormalizable($object, ShapeStub::class));
        self::assertInstanceOf(
            InvalidValueException::class,
            self::captureThrowable(static fn(): mixed => $mapper->normalize($object, ShapeStub::class)),
        );
    }

    public function testChildOfClassOfMapIsNormalizedWithTagOfParent(): void
    {
        $proxy = new class extends CircleStub {};
        $proxy->radius = 1;

        self::assertNormalizes(ShapeStub::class, $proxy, ['type' => 'circle', 'radius' => 1]);
    }

    public function testValueThatIsNotArrayOrObjectIsRejected(): void
    {
        self::assertDenormalizationFails(
            ShapeStub::class,
            'x',
            '$: Value must be of type object, but "x" given',
        );
    }

    public function testNormalizationRejectsObjectOfOtherHierarchy(): void
    {
        self::assertNormalizationFails(
            ShapeStub::class,
            new AddressStub(),
            '$: Value must be of type TypeLang\Mapper\Tests\Stub\Shape\ShapeStub, but object(TypeLang\Mapper\Tests\Stub\AddressStub) given',
        );
    }

    public function testObjectOfClassOutsideMapIsNormalizedByDefault(): void
    {
        $fallback = new FallbackShapeStub();

        self::assertNormalizes(FallbackShapeStub::class, $fallback, ['label' => 'none']);
    }

    public function testNormalizationWithoutBaseTypeHasNoTag(): void
    {
        self::assertNormalizes('mixed', self::createCircle(1), ['radius' => 1]);
    }

    public function testClassOfMapOutsideHierarchyIsErrorOfType(): void
    {
        $error = self::captureBuildError(ForeignMapStub::class);

        self::assertInstanceOf(TypeDefinitionException::class, $error);
        self::assertSame(
            \sprintf(
                'Type of tag "address" of class "%s" is invalid: "%s" does not extend it',
                ForeignMapStub::class,
                AddressStub::class,
            ),
            $error->getMessage(),
        );
    }

    private static function createCircle(int $radius): CircleStub
    {
        $circle = new CircleStub();
        $circle->radius = $radius;

        return $circle;
    }

    private static function createSquare(int $side): SquareStub
    {
        $square = new SquareStub();
        $square->side = $side;

        return $square;
    }


    public function testObjectOfUnmappedChildTakesTagOfNearestMappedParent(): void
    {
        $puppy = new PuppyStub();
        $puppy->name = 'Rex';

        self::assertNormalizes(
            AnimalStub::class,
            $puppy,
            ['kind' => 'dog', 'barks' => true, 'name' => 'Rex'],
        );
    }
}
