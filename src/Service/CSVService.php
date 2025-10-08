<?php
declare(strict_types=1);

namespace App\Service;

use App\DTO\PersonContactDTO;
use App\DTO\CompanyContactDTO;

/**
 * Gère la création et l'écriture de fichiers CSV
 * pour les personnes physiques et les sociétés.
 */
class CSVService
{
    private string $contactsDirectory;

    /**
     * @param string $projectDir racine du projet
     */
    public function __construct(string $projectDir)
    {
        $this->contactsDirectory = $projectDir . '/var/contacts';
    }

    /**
     * Sauvegarde un contact en CSV.
     *
     * @param PersonContactDTO|CompanyContactDTO $dto DTO du contact à sauvegarder
     * @param bool $isCompany True si c'est une société, false si c'est une personne
     */
    public function save(PersonContactDTO|CompanyContactDTO $dto, bool $isCompany): void
    {
        $this->ensureDirectoryExists();

        $filename = $this->getFilename($isCompany);
        $isNewFile = !file_exists($filename);

        $file = fopen($filename, 'a');
        if (!$file) {
            throw new \RuntimeException('Unable to open CSV file for writing');
        }

        try {
            if ($isNewFile) {
                $this->writeHeaders($file, $isCompany);
            }

            $this->writeContactData($file, $dto, $isCompany);

        } finally {
            fclose($file);
        }
    }

    /**
     * S'assure que le répertoire de stockage existe.
     */
    private function ensureDirectoryExists(): void
    {
        if (!is_dir($this->contactsDirectory)) {
            if (!mkdir($this->contactsDirectory, 0755, true)) {
                throw new \RuntimeException('Unable to create contacts directory');
            }
        }
    }

    /**
     * Retourne le nom du fichier CSV selon le type de contact.
     *
     * @param bool $isCompany True pour companies.csv, false pour persons.csv
     * @return string Chemin complet du fichier CSV
     */
    private function getFilename(bool $isCompany): string
    {
        $type = $isCompany ? 'companies' : 'persons';
        return $this->contactsDirectory . "/{$type}.csv";
    }

    /**
     * @param resource $file Handle du fichier CSV ouvert
     * @param bool $isCompany True pour les en-têtes société, false pour personne
     */
    private function writeHeaders($file, bool $isCompany): void
    {
        if ($isCompany) {
            $headers = [
                'timestamp',
                'siret',
                'companyName',
                'email',
                'phone',
                'address',
                'postalCode',
                'city'
            ];
        } else {
            $headers = [
                'timestamp',
                'gender',
                'name',
                'lastname',
                'email',
                'phone',
                'address',
                'postalCode',
                'city'
            ];
        }

        fputcsv($file, $headers);
    }

    /**
     * Écrit les données du contact dans le CSV.
     *
     * @param resource $file Handle du fichier CSV ouvert
     * @param PersonContactDTO|CompanyContactDTO $dto DTO contenant les données
     * @param bool $isCompany True pour société, false pour personne
     */
    private function writeContactData($file, PersonContactDTO|CompanyContactDTO $dto, bool $isCompany): void
    {
        $timestamp = date('Y-m-d H:i:s');

        if ($isCompany) {
            $data = [
                $timestamp,
                $dto->getSiret(),
                $dto->getCompanyName(),
                $dto->getCompanyEmail() ?? '',
                $dto->getCompanyPhone(),
                $dto->getCompanyAddress(),
                $dto->getCompanyPostalCode(),
                $dto->getCompanyCity()
            ];
        } else {
            $data = [
                $timestamp,
                $dto->getGender(),
                $dto->getName(),
                $dto->getLastname(),
                $dto->getEmail(),
                $dto->getPhone(),
                $dto->getAddress(),
                $dto->getPostalCode(),
                $dto->getCity()
            ];
        }

        fputcsv($file, $data);
    }
}
