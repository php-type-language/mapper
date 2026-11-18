<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Bridge\Symfony\Profiler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;
use Symfony\Component\VarDumper\Cloner\Data;
use TypeLang\Mapper\Diagnostics\Exception\ViolationException;
use TypeLang\Mapper\Diagnostics\Printer\Path\JsonPathPrinter;
use TypeLang\Mapper\Diagnostics\Printer\Value\ShortValuePrinter;
use TypeLang\Mapper\Diagnostics\ViolationFormatter;
use TypeLang\Mapper\Diagnostics\ViolationFormatterInterface;
use TypeLang\Printer\PrettyTypePrinter;
use TypeLang\Printer\PrinterOptions;

/**
 * Collects the calls of the mappers, the types they build and the metadata
 * they read during a request for the web profiler.
 *
 * @phpstan-type CallType array{
 *     mapper: non-empty-string,
 *     method: non-empty-string,
 *     type: string|null,
 *     input: mixed,
 *     output: mixed,
 *     error: array{class: class-string, message: string}|null,
 *     violations: list<array{path: non-empty-string, message: string}>,
 *     duration: float,
 *     file: string|null,
 *     line: int|null,
 * }
 * @phpstan-type BuiltTypeType array{
 *     mapper: non-empty-string,
 *     type: string,
 *     class: class-string,
 * }
 * @phpstan-type MetadataReadType array{
 *     mapper: non-empty-string,
 *     class: class-string,
 *     reads: int<1, max>,
 *     duration: float,
 * }
 */
final class MapperDataCollector extends DataCollector implements LateDataCollectorInterface
{
    /**
     * Calls of the current request with the raw values, cloned on the late
     * collection
     *
     * @var list<CallType>
     */
    private array $calls = [];

    /**
     * @var list<BuiltTypeType>
     */
    private array $types = [];

    /**
     * Reads by the mapper and the class
     *
     * @var array<non-empty-string, MetadataReadType>
     */
    private array $metadata = [];

    /**
     * @param non-empty-string $mapper
     * @param non-empty-string $method
     * @param float $duration in milliseconds
     */
    public function collectCall(
        string $mapper,
        string $method,
        ?string $type,
        mixed $input,
        mixed $output,
        ?\Throwable $error,
        float $duration,
        ?string $file = null,
        ?int $line = null,
    ): void {
        $this->calls[] = [
            'mapper' => $mapper,
            'method' => $method,
            'type' => $type,
            'input' => $input,
            'output' => $output,
            'error' => $error === null ? null : ['class' => $error::class, 'message' => $error->getMessage()],
            'violations' => self::readViolations($error),
            'duration' => $duration,
            'file' => $file,
            'line' => $line,
        ];
    }

    /**
     * @param non-empty-string $mapper
     * @param class-string $class
     */
    public function collectType(string $mapper, string $type, string $class): void
    {
        $this->types[] = ['mapper' => $mapper, 'type' => $type, 'class' => $class];
    }

    /**
     * @param non-empty-string $mapper
     * @param class-string $class
     * @param float $duration in milliseconds
     */
    public function collectMetadata(string $mapper, string $class, float $duration): void
    {
        $key = $mapper . ':' . $class;

        $previous = $this->metadata[$key] ?? null;

        $this->metadata[$key] = [
            'mapper' => $mapper,
            'class' => $class,
            'reads' => ($previous['reads'] ?? 0) + 1,
            'duration' => ($previous['duration'] ?? 0.0) + $duration,
        ];
    }

    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void {}

    public function lateCollect(): void
    {
        $calls = [];

        foreach ($this->calls as $call) {
            $call['input'] = $this->cloneVar($call['input']);
            $call['output'] = $this->cloneVar($call['output']);

            $calls[] = $call;
        }

        $this->data = [
            'calls' => $calls,
            'types' => $this->types,
            'metadata' => \array_values($this->metadata),
        ];
    }

    public function getName(): string
    {
        return 'type_lang_mapper';
    }

    public function reset(): void
    {
        parent::reset();

        $this->calls = [];
        $this->types = [];
        $this->metadata = [];
    }

    /**
     * The input and the output values are {@see Data} dumps.
     *
     * @return list<CallType>
     */
    public function getCalls(): array
    {
        /** @var list<CallType> */
        return $this->data['calls'] ?? [];
    }

    /**
     * @return int<0, max>
     */
    public function getErrorCount(): int
    {
        $result = 0;

        foreach ($this->getCalls() as $call) {
            if ($call['error'] !== null) {
                ++$result;
            }
        }

        return $result;
    }

    /**
     * Returns the total time of the calls in milliseconds, the nested calls
     * of a mapper, such as from a hook, are counted twice.
     */
    public function getTotalDuration(): float
    {
        $result = 0.0;

        foreach ($this->getCalls() as $call) {
            $result += $call['duration'];
        }

        return $result;
    }

    /**
     * @return list<BuiltTypeType>
     */
    public function getTypes(): array
    {
        /** @var list<BuiltTypeType> */
        return $this->data['types'] ?? [];
    }

    /**
     * Returns the classes the metadata of which is read, the duration is the
     * total of the reads of the class.
     *
     * @return list<MetadataReadType>
     */
    public function getMetadata(): array
    {
        /** @var list<MetadataReadType> */
        return $this->data['metadata'] ?? [];
    }

    /**
     * Returns the violations for the developer: with the declared types and
     * the messages of their causes, such as of an error of a hook.
     *
     * @return list<array{path: non-empty-string, message: string}>
     */
    private static function readViolations(?\Throwable $error): array
    {
        if (!$error instanceof ViolationException) {
            return [];
        }

        $formatter = self::createFormatter();
        $printer = new JsonPathPrinter();
        $result = [];

        foreach ($error->violations as $violation) {
            $message = $formatter->format($violation);

            if ($violation->cause !== null && $violation->cause->getMessage() !== '') {
                $message .= ': ' . $violation->cause->getMessage();
            }

            $result[] = [
                'path' => $printer->print($violation->path),
                'message' => $message,
            ];
        }

        return $result;
    }

    /**
     * Creates the formatter for the developer: the declared types with the
     * classes of the application in a single line.
     */
    private static function createFormatter(): ViolationFormatterInterface
    {
        return new ViolationFormatter(
            new ShortValuePrinter(),
            new PrettyTypePrinter(new PrinterOptions(maxInlineShapeFields: \PHP_INT_MAX)),
            shouldPrintDeclaredType: true,
        );
    }
}
