<?php

declare(strict_types=1);

namespace TypeLang\Mapper\Meta;

abstract class Metadata
{
    /**
     * @var int<0, max>
     */
    public const METADATA_VERSION = 1;

    private readonly int $createdAt;

    /**
     * @var list<non-empty-string>
     */
    protected const SERIALIZABLE_PROPERTIES = [
        'name',
        'createdAt',
    ];

    /**
     * @param non-empty-string $name
     * @throws \Exception
     */
    public function __construct(
        private readonly string $name,
        ?int $createdAt = null,
    ) {
        $this->createdAt = $createdAt ?? $this->getCurrentTimestamp();
    }

    /**
     * @return int<0, max>
     */
    public function getVersion(): int
    {
        return self::METADATA_VERSION;
    }

    private function getCurrentTimestamp(): int
    {
        $date = new \DateTimeImmutable();

        return $date->getTimestamp();
    }

    public function __serialize(): array
    {
        return [
            'version' => $this->getVersion(),
            'name' => $this->getName(),
            'createdAt' => $this->getCreatedAtTimestamp(),
        ];
    }

    /**
     * @param array<array-key, mixed> $data
     * @throws \InvalidArgumentException
     */
    public function __unserialize(array $data): void
    {
        if (!isset($data['version']) || $data['version'] !== $this->getVersion()) {
            throw new \InvalidArgumentException(\sprintf(
                'Metadata of version "%s" is not compatible with expected "%s", please clear the cache',
                \var_export($data['version'] ?? 'unknown', true),
                $this->getVersion(),
            ));
        }

        $this->name = $data['name'];
        $this->createdAt = $data['createdAt'];
    }

    /**
     * @api
     *
     * @return non-empty-string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @api
     */
    public function getCreatedAtTimestamp(): int
    {
        return $this->createdAt;
    }

    /**
     * @api
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return (new \DateTimeImmutable())
            ->setTimestamp($this->getCreatedAtTimestamp());
    }
}
