<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TaxRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaxRepository::class)]
#[ORM\Table(name: 'tax')]
class Tax
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 2, unique: true)]
    private string $countryCode;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    private string $rate;

    /**
     * Regex pattern for validating tax numbers of this country
     * Example: DE - "^DE[0-9]{9}$", IT - "^IT[0-9]{11}$"
     */
    #[ORM\Column(type: Types::STRING, length: 100)]
    private string $taxNumberPattern;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function setCountryCode(string $countryCode): self
    {
        $this->countryCode = $countryCode;
        return $this;
    }

    public function getRate(): string
    {
        return $this->rate;
    }

    public function setRate(string $rate): self
    {
        $this->rate = $rate;
        return $this;
    }

    public function getTaxNumberPattern(): string
    {
        return $this->taxNumberPattern;
    }

    public function setTaxNumberPattern(string $taxNumberPattern): self
    {
        $this->taxNumberPattern = $taxNumberPattern;
        return $this;
    }
}

