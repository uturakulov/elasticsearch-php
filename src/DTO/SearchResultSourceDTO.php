<?php

namespace Alifuz\AlifSearch\DTO;

use Alifuz\Utils\Entities\AbstractEntity;
use Alifuz\Utils\Exceptions\ParseException;

final class SearchResultSourceDTO extends AbstractEntity
{
    public function __construct(
        private ?int $id,
        private ?string $phone,
        private ?string $name,
        private ?string $surname,
        private ?string $patronymic,
        private ?string $passport_id,
        private ?int $pinfl,
        private ?string $full_name,
//        private ?string $contract_number,
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
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getSurname(): ?string
    {
        return $this->surname;
    }

    /**
     * @return string|null
     */
    public function getPatronymic(): ?string
    {
        return $this->patronymic;
    }

    /**
     * @return string|null
     */
    public function getPassportId(): ?string
    {
        return $this->passport_id;
    }

    /**
     * @return int|null
     */
    public function getPinfl(): ?int
    {
        return $this->pinfl;
    }

    /**
     * @return string|null
     */
    public function getFullName(): ?string
    {
        return $this->full_name;
    }

//    /**
//     * @return string|null
//     */
//    public function getContractNumber(): ?string
//    {
//        return $this->contract_number;
//    }

    /**
     * @inheritDoc
     * @throws ParseException
     */
    public static function fromArray(array $data): static
    {
        return new static(
            id: self::parseNullableInt($data['id']),
            phone: self::parseNullableString($data['phone']),
            name: self::parseNullableString($data['name']),
            surname: self::parseNullableString($data['surname']),
            patronymic: self::parseNullableString($data['patronymic']),
            passport_id: self::parseNullableString($data['passport_id']),
            pinfl: self::parseNullableInt($data['pinfl']),
            full_name: self::parseNullableString($data['full_name']),
//            contract_number: self::parseNullableString($data['contract_number']),
        );
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'phone' => $this->phone,
            'name' => $this->name,
            'surname' => $this->surname,
            'patronymic' => $this->patronymic,
            'passport_id' => $this->passport_id,
            'pinfl' => $this->pinfl,
            'full_name' => $this->full_name,
//            'contract_number' => $this->contract_number,
        ];
    }
}
