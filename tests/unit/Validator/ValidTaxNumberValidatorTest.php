<?php

declare(strict_types=1);

namespace App\Tests\unit\Validator;

use App\Entity\Tax;
use App\Repository\TaxRepository;
use App\Validator\ValidTaxNumber;
use App\Validator\ValidTaxNumberValidator;
use Codeception\Test\Unit;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

final class ValidTaxNumberValidatorTest extends Unit
{
    private TaxRepository $taxRepository;
    private ExecutionContextInterface $context;
    private ValidTaxNumberValidator $validator;
    private ValidTaxNumber $constraint;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->taxRepository = $this->createMock(TaxRepository::class);
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->constraint = new ValidTaxNumber();
        
        $this->validator = new ValidTaxNumberValidator($this->taxRepository);
        $this->validator->initialize($this->context);
    }

    public function testValidGermanTaxNumber(): void
    {
        $tax = $this->createTax('DE', '^DE[0-9]{9}$');

        $this->taxRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['countryCode' => 'DE'])
            ->willReturn($tax);

        $this->context
            ->expects($this->never())
            ->method('buildViolation');

        $this->validator->validate('DE123456789', $this->constraint);
    }

    public function testValidItalianTaxNumber(): void
    {
        $tax = $this->createTax('IT', '^IT[0-9]{11}$');

        $this->taxRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['countryCode' => 'IT'])
            ->willReturn($tax);

        $this->context
            ->expects($this->never())
            ->method('buildViolation');

        $this->validator->validate('IT12345678900', $this->constraint);
    }

    public function testValidFrenchTaxNumber(): void
    {
        $tax = $this->createTax('FR', '^FR[A-Z]{2}[0-9]{9}$');

        $this->taxRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['countryCode' => 'FR'])
            ->willReturn($tax);

        $this->context
            ->expects($this->never())
            ->method('buildViolation');

        $this->validator->validate('FRAA123456789', $this->constraint);
    }

    public function testValidGreekTaxNumber(): void
    {
        $tax = $this->createTax('GR', '^GR[0-9]{9}$');

        $this->taxRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['countryCode' => 'GR'])
            ->willReturn($tax);

        $this->context
            ->expects($this->never())
            ->method('buildViolation');

        $this->validator->validate('GR123456789', $this->constraint);
    }

    public function testNullValueSkipsValidation(): void
    {
        $this->taxRepository
            ->expects($this->never())
            ->method('findOneBy');

        $this->context
            ->expects($this->never())
            ->method('buildViolation');

        $this->validator->validate(null, $this->constraint);
    }

    public function testEmptyStringSkipsValidation(): void
    {
        $this->taxRepository
            ->expects($this->never())
            ->method('findOneBy');

        $this->context
            ->expects($this->never())
            ->method('buildViolation');

        $this->validator->validate('', $this->constraint);
    }

    public function testTooShortTaxNumber(): void
    {
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->context
            ->expects($this->once())
            ->method('buildViolation')
            ->with($this->constraint->tooShortMessage)
            ->willReturn($violationBuilder);

        $violationBuilder
            ->expects($this->once())
            ->method('addViolation');

        $this->validator->validate('D', $this->constraint);
    }

    public function testUnknownCountryCode(): void
    {
        $this->taxRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['countryCode' => 'XX'])
            ->willReturn(null);

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->context
            ->expects($this->once())
            ->method('buildViolation')
            ->with($this->constraint->unknownCountryMessage)
            ->willReturn($violationBuilder);

        $violationBuilder
            ->expects($this->once())
            ->method('setParameter')
            ->with('{{ country }}', 'XX')
            ->willReturn($violationBuilder);

        $violationBuilder
            ->expects($this->once())
            ->method('addViolation');

        $this->validator->validate('XX123456789', $this->constraint);
    }

    public function testInvalidTaxNumberFormat(): void
    {
        $tax = $this->createTax('DE', '^DE[0-9]{9}$');

        $this->taxRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['countryCode' => 'DE'])
            ->willReturn($tax);

        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);

        $this->context
            ->expects($this->once())
            ->method('buildViolation')
            ->with($this->constraint->message)
            ->willReturn($violationBuilder);

        $violationBuilder
            ->expects($this->exactly(2))
            ->method('setParameter')
            ->willReturn($violationBuilder);

        $violationBuilder
            ->expects($this->once())
            ->method('addViolation');

        $this->validator->validate('DE12345', $this->constraint); // Слишком короткий
    }

    private function createTax(string $countryCode, string $pattern): Tax
    {
        $tax = $this->createMock(Tax::class);
        $tax->method('getCountryCode')->willReturn($countryCode);
        $tax->method('getTaxNumberPattern')->willReturn($pattern);
        
        return $tax;
    }
}
