<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Mapping\Reader\ConfigReader;

use JsonSchema\Constraints\Constraint as JsonSchemaConstraint;
use JsonSchema\Validator as JsonSchemaValidator;

/**
 * @phpstan-type JsonSchemaErrorType array<array-key, array{
 *     message: non-empty-string,
 *     property: non-empty-string,
 *     constraint?: array{
 *         name?: string,
 *         ...
 *     },
 *     ...
 * }>
 * @phpstan-type PropertyConfigType array{
 *     name?: non-empty-string,
 *     type?: non-empty-string,
 *     skip?: 'null'|'empty'|non-empty-string|list<'null'|'empty'|non-empty-string>,
 *     type_error_message?: non-empty-string,
 *     undefined_error_message?: non-empty-string,
 *     ...
 * }
 * @phpstan-type ClassDiscriminatorConfigType array{
 *     field: non-empty-string,
 *     map: non-empty-array<non-empty-string, non-empty-string>,
 *     otherwise?: non-empty-string,
 * }
 * @phpstan-type ClassConfigType array{
 *     normalize_as_array?: bool,
 *     discriminator?: ClassDiscriminatorConfigType,
 *     properties?: array<non-empty-string, non-empty-string|PropertyConfigType>
 * }
 */
final class SchemaValidator
{
    /**
     * @var non-empty-string
     */
    private const JSON_SCHEMA_PATHNAME = __DIR__ . '/../../../../resources/config.schema.json';

    public static function isAvailable(): bool
    {
        return \class_exists(JsonSchemaValidator::class);
    }

    /**
     * @phpstan-assert ClassConfigType $config
     *
     * @param non-empty-string $path
     * @param array<array-key, mixed> $config
     *
     * @throws \InvalidArgumentException
     */
    public function validateOrFail(string $path, array $config): void
    {
        $validator = new JsonSchemaValidator();

        $validator->validate($config, (object) [
            '$ref' => 'file://' . \realpath(self::JSON_SCHEMA_PATHNAME),
        ], JsonSchemaConstraint::CHECK_MODE_TYPE_CAST);

        // @phpstan-ignore-next-line
        $message = $this->getFormattedErrorMessage($path, $validator->getErrors());

        if ($message === null) {
            return;
        }

        throw new \InvalidArgumentException(\sprintf(
            "The following configuration errors were found: \n%s",
            $message,
        ));
    }

    /**
     * @param non-empty-string $path
     * @param JsonSchemaErrorType $errors
     *
     * @return non-empty-string|null
     */
    private function getFormattedErrorMessage(string $path, array $errors): ?string
    {
        $result = [];

        foreach ($this->filterErrorMessages($errors) as $localPath => $localMessages) {
            $realPath = \rtrim($path . '.' . $localPath, '.');

            $result[] = \vsprintf("- An error at \"%s\":\n  └ %s", [
                $realPath,
                \implode("\n  └ ", $localMessages),
            ]);
        }

        if ($result === []) {
            return null;
        }

        return \implode("\n", $result);
    }

    /**
     * @param JsonSchemaErrorType $errors
     *
     * @return iterable<non-empty-string, list<non-empty-string>>
     */
    private function filterErrorMessages(array $errors): iterable
    {
        $processedPaths = [];

        foreach ($this->groupErrorMessages($errors) as $path => $messages) {
            if ($this->containsInPath($processedPaths, $path)) {
                continue;
            }

            // @phpstan-ignore-next-line : PHPStan generator false-positive
            yield $path => $messages;

            $processedPaths[] = $path;
        }
    }

    /**
     * @param list<non-empty-string> $processedPaths
     * @param non-empty-string $path
     */
    private function containsInPath(array $processedPaths, string $path): bool
    {
        foreach ($processedPaths as $processedPath) {
            if (\str_contains($processedPath, $path . '.')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param JsonSchemaErrorType $errors
     *
     * @return array<non-empty-string, list<non-empty-string>>
     */
    private function groupErrorMessages(array $errors): array
    {
        $groups = [];

        foreach ($errors as $error) {
            switch ($error['constraint']['name'] ?? null) {
                // Exclude "anyOf" constraints
                case 'anyOf':
                    continue 2;
                default:
                    $groups[$error['property']][] = $error['message'];
            }
        }

        return $groups;
    }
}
