<?php
declare(strict_types=1);

namespace App\Service;

use App\DTO\PersonContactDTO;
use App\DTO\CompanyContactDTO;
class CSVService
{
    private string $contactsDirectory;

    public function __construct(string $projectDir)
    {
        $this->contactsDirectory = $projectDir . '/var/contacts';
    }
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

    private function ensureDirectoryExists(): void
    {
        if (!is_dir($this->contactsDirectory)) {
            if (!mkdir($this->contactsDirectory, 0755, true)) {
                throw new \RuntimeException('Unable to create contacts directory');
            }
        }
    }

    private function getFilename(bool $isCompany): string
    {
        $type = $isCompany ? 'companies' : 'persons';
        return $this->contactsDirectory . "/{$type}.csv";
    }

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
