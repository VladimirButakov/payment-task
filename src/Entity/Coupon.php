<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\CouponType;
use App\Repository\CouponRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CouponRepository::class)]
#[ORM\Table(name: 'coupon')]
class Coupon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 50, unique: true)]
    private string $code;

    #[ORM\Column(type: Types::STRING, length: 20, enumType: CouponType::class)]
    private CouponType $type;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $value;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function getType(): CouponType
    {
        return $this->type;
    }

    public function setType(CouponType $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;
        return $this;
    }
}

