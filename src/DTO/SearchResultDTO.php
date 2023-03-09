<?php

namespace Alifuz\AlifSearch\DTO;

use Alifuz\Utils\Entities\AbstractEntity;
use Alifuz\Utils\Exceptions\ParseException;

final class SearchResultDTO extends AbstractEntity
{
    public function __construct(
        private ?string $index,
        private ?string $type,
        private ?string $id,
        private ?float $score,
        private ?array $source,
    ) {
    }

    /**
     * @return string|null
     */
    public function getIndex(): ?string
    {
        return $this->index;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return float|null
     */
    public function getScore(): ?float
    {
        return $this->score;
    }

    /**
     * @return SearchResultSourceDTO[]|null
     */
    public function getSource(): ?array
    {
        return $this->source;
    }

    /**
     * @inheritDoc
     * @throws ParseException
     */
    public static function fromArray(array $data): static
    {
        return new static(
            index: self::parseNullableString($data['_index']),
            type: self::parseNullableString($data['_type']),
            id: self::parseNullableString($data['_id']),
            score: self::parseNullableFloat($data['_score']),
            source: self::parseNullableEntity(SearchResultSourceDTO::class, $data['_source']),
        );
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            'index' => $this->index,
            'type' => $this->type,
            'id' => $this->id,
            'score' => $this->score,
            'source' => $this->source,
        ];
    }
}
