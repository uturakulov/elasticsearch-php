<?php

namespace Alifuz\AlifSearch\DTO;

use Alifuz\Utils\Entities\AbstractEntity;
use Alifuz\Utils\Exceptions\ParseException;

final class SearchResultSourceCreditsDTO extends AbstractEntity
{
    public function __construct(
        private ?int $id,
        private ?string $contract_number,
    ) {
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getContractNumber(): ?string
    {
        return $this->contract_number;
    }

    /**
     * @inheritDoc
     * @throws ParseException
     */
    public static function fromArray(array $data): static
    {
        return new static(
            id: self::parseNullableInt($data['id']),
            contract_number: self::parseNullableString($data['contract_number']),
        );
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'contract_number' => $this->contract_number,
        ];
    }
}
