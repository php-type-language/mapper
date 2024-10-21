<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Runtime\Repository\Reference;

final class NativeReferencesReader implements ReferencesReaderInterface
{
    /**
     * Return array of use statements from class.
     *
     * @param \ReflectionClass<object> $class
     *
     * @return array<int|non-empty-string, non-empty-string>
     */
    public function getUseStatements(\ReflectionClass $class): array
    {
        try {
            $header = $this->getCodeHeader($class);
        } catch (\Throwable) {
            $header = '';
        }

        return [...$this->parse($class, $header)];
    }

    /**
     * Read file source up to the line where our class is defined.
     *
     * @param \ReflectionClass<object> $class
     *
     * @throws \LogicException
     * @throws \RuntimeException
     */
    private function getCodeHeader(\ReflectionClass $class): string
    {
        $pathname = $class->getFileName();

        if ($pathname === false || !$class->isUserDefined()) {
            return '';
        }

        $source = new \SplFileObject($pathname);
        $source->flock(\LOCK_SH);

        $line = 0;
        $result = '';

        while (!$source->eof()) {
            if (++$line >= $class->getStartLine()) {
                break;
            }

            $result .= $source->fgets();
        }

        $source->flock(\LOCK_UN);
        unset($source);

        return $result;
    }

    /**
     * @return \Iterator<array-key, \PhpToken>
     */
    private function lex(string $source): \Iterator
    {
        yield from \PhpToken::tokenize($source);
    }

    /**
     * @param \Iterator<array-key, \PhpToken> $tokens
     */
    private function readNamespace(\Iterator $tokens): string
    {
        // Skip "namespace" token.
        $tokens->next();

        $result = null;

        while ($tokens->valid()) {
            $current = $tokens->current();

            if ($current->id === \T_NAME_QUALIFIED) {
                $result = $current->text;
            } elseif ($current->text === ';') {
                $tokens->next();

                return $result ?? '';
            }

            $tokens->next();
        }

        return $result ?? '';
    }

    /**
     * @param \ReflectionClass<object> $class
     * @param \Iterator<array-key, \PhpToken> $tokens
     *
     * @return \Iterator<array-key, \PhpToken>
     */
    private function skipUnimportantNamespaces(\ReflectionClass $class, \Iterator $tokens): \Iterator
    {
        $expected = $class->getNamespaceName();

        $atLeastOneNamespace = false;

        while ($tokens->valid()) {
            $current = $tokens->current();

            switch ($current->id) {
                case \T_NAMESPACE:
                    $atLeastOneNamespace = true;
                    if ($this->readNamespace($tokens) === $expected) {
                        return $tokens;
                    }
                    break;

                case \T_USE:
                    if ($atLeastOneNamespace === false) {
                        return $tokens;
                    }
                    break;
            }

            $tokens->next();
        }

        return $tokens;
    }

    /**
     * @param \Iterator<array-key, \PhpToken> $tokens
     *
     * @return \Iterator<int|non-empty-string, non-empty-string>
     */
    private function lookupUseStatements(\Iterator $tokens): \Iterator
    {
        while ($tokens->valid()) {
            $current = $tokens->current();

            if ($current->id === \T_USE) {
                $tokens->next();

                $statements = $this->fetchUseStatement($tokens);

                if ($statements === null) {
                    continue;
                }

                [$namespace, $alias] = $statements;

                if ($alias === null) {
                    yield $namespace;
                } else {
                    yield $alias => $namespace;
                }
            }

            $tokens->next();
        }

        return $tokens;
    }

    /**
     * @param \Iterator<array-key, \PhpToken> $tokens
     *
     * @return array{non-empty-string, non-empty-string|null}|null
     */
    private function fetchUseStatement(\Iterator $tokens): ?array
    {
        $alias = $namespace = null;

        while ($tokens->valid()) {
            $current = $tokens->current();

            switch ($current->id) {
                case \T_NAME_QUALIFIED:
                    /** @var non-empty-string $namespace */
                    $namespace = $current->text;
                    break;
                case \T_STRING:
                    /** @var non-empty-string $alias */
                    $alias = $current->text;
                    break;
                default:
                    // TODO Group "use" statements not supported yet
                    if ($current->text === '{') {
                        return null;
                    }

                    if ($current->text === ';') {
                        $tokens->next();

                        if ($namespace === null) {
                            return null;
                        }

                        return [$namespace, $alias];
                    }
            }

            $tokens->next();
        }

        if ($namespace === null) {
            return null;
        }

        return [$namespace, $alias];
    }

    /**
     * Parse the use statements from read source by
     * tokenizing and reading the tokens. Returns
     * an array of use statements and aliases.
     *
     * @param \ReflectionClass<object> $class
     *
     * @return \Iterator<int|non-empty-string, non-empty-string>
     */
    private function parse(\ReflectionClass $class, string $source): \Iterator
    {
        $tokens = $this->lex($source);

        $tokens = $this->skipUnimportantNamespaces($class, $tokens);

        return $this->lookupUseStatements($tokens);
    }
}
