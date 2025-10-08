<?php
declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CompanyContactDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Le SIRET est obligatoire')]
        #[Assert\Regex('/^\d{14}$/', message: 'SIRET invalide (14 chiffres)')]
        private string $siret,

        #[Assert\NotBlank(message: 'La raison sociale est obligatoire')]
        #[Assert\Length(max: 100, maxMessage: 'La raison sociale ne peut pas dépasser 100 caractères')]
        private string $companyName,

        #[Assert\Email(message: 'Format email invalide')]
        private ?string $companyEmail,

        #[Assert\NotBlank(message: 'Le téléphone est obligatoire')]
        #[Assert\Regex('/^(?:(?:\+|00)33|0)[1-9](?:[\s.-]*\d{2}){4}$/', message: 'Format téléphone français invalide')]
        private string $companyPhone,

        #[Assert\NotBlank(message: 'L\'adresse est obligatoire')]
        #[Assert\Length(max: 255, maxMessage: 'L\'adresse ne peut pas dépasser 255 caractères')]
        private string $companyAddress,

        #[Assert\NotBlank(message: 'Le code postal est obligatoire')]
        #[Assert\Regex('/^\d{5}$/', message: 'Le code postal doit contenir 5 chiffres')]
        private string $companyPostalCode,

        #[Assert\NotBlank(message: 'La ville est obligatoire')]
        #[Assert\Length(max: 100, maxMessage: 'La ville ne peut pas dépasser 100 caractères')]
        private string $companyCity,
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['siret'] ?? '',
            $data['companyName'] ?? '',
            $data['companyEmail'] ?? null,
            $data['companyPhone'] ?? '',
            $data['companyAddress'] ?? '',
            $data['companyPostalCode'] ?? '',
            $data['companyCity'] ?? ''
        );
    }

    public function getSiret(): string
    {
        return $this->siret;
    }

    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    public function getCompanyEmail(): ?string
    {
        return $this->companyEmail;
    }

    public function getCompanyPhone(): string
    {
        return $this->companyPhone;
    }

    public function getCompanyAddress(): string
    {
        return $this->companyAddress;
    }

    public function getCompanyPostalCode(): string
    {
        return $this->companyPostalCode;
    }

    public function getCompanyCity(): string
    {
        return $this->companyCity;
    }

    public function toArray(): array
    {
        return [
            'siret' => $this->siret,
            'companyName' => $this->companyName,
            'companyEmail' => $this->companyEmail,
            'companyPhone' => $this->companyPhone,
            'companyAddress' => $this->companyAddress,
            'companyPostalCode' => $this->companyPostalCode,
            'companyCity' => $this->companyCity,
        ];
    }
}
