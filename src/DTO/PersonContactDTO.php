<?php
declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO d'une personne physique.
 *
 * Contient toutes les informations d'une personne
 * avec validation intégrée via les contraintes Symfony.
 */
readonly class PersonContactDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Le genre est obligatoire')]
        #[Assert\Choice(choices: ['male', 'female'], message: 'Genre invalide')]
        private string $gender,

        #[Assert\NotBlank(message: 'Le nom est obligatoire')]
        #[Assert\Length(max: 50, maxMessage: 'Le nom ne peut pas dépasser 50 caractères')]
        private string $name,

        #[Assert\NotBlank(message: 'Le prénom est obligatoire')]
        #[Assert\Length(max: 50, maxMessage: 'Le prénom ne peut pas dépasser 50 caractères')]
        private string $lastname,

        #[Assert\NotBlank(message: 'L\'email est obligatoire')]
        #[Assert\Email(message: 'Format email invalide')]
        private string $email,

        #[Assert\NotBlank(message: 'Le téléphone est obligatoire')]
        #[Assert\Regex('/^(?:(?:\+|00)33|0)[1-9](?:[\s.-]*\d{2}){4}$/', message: 'Format téléphone français invalide')]
        private string $phone,

        #[Assert\NotBlank(message: 'L\'adresse est obligatoire')]
        #[Assert\Length(max: 255, maxMessage: 'L\'adresse ne peut pas dépasser 255 caractères')]
        private string $address,

        #[Assert\NotBlank(message: 'Le code postal est obligatoire')]
        #[Assert\Regex('/^\d{5}$/', message: 'Le code postal doit contenir 5 chiffres')]
        private string $postalCode,

        #[Assert\NotBlank(message: 'La ville est obligatoire')]
        #[Assert\Length(max: 100, maxMessage: 'La ville ne peut pas dépasser 100 caractères')]
        private string $city,
    )
    {
    }

    /**
     * @param array $data Données du formulaire
     * @return self Instance du DTO
     */
    public static function fromArray(array $data): self
    {
        return new self(
            $data['gender'] ?? '',
            $data['name'] ?? '',
            $data['lastname'] ?? '',
            $data['email'] ?? '',
            $data['phone'] ?? '',
            $data['address'] ?? '',
            $data['postalCode'] ?? '',
            $data['city'] ?? ''
        );
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function toArray(): array
    {
        return [
            'gender' => $this->gender,
            'name' => $this->name,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'postalCode' => $this->postalCode,
            'city' => $this->city,
        ];
    }
}
