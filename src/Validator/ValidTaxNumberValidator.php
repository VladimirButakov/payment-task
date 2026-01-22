<?php

declare(strict_types=1);

namespace App\Validator;

use App\Repository\TaxRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ValidTaxNumberValidator extends ConstraintValidator
{
    private const MIN_TAX_NUMBER_LENGTH = 2;

    public function __construct(
        private readonly TaxRepository $taxRepository
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ValidTaxNumber) {
            throw new UnexpectedTypeException($constraint, ValidTaxNumber::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        if (strlen($value) < self::MIN_TAX_NUMBER_LENGTH) {
            $this->context->buildViolation($constraint->tooShortMessage)
                ->addViolation();
            return;
        }

        $countryCode = strtoupper(substr($value, 0, 2));

        $tax = $this->taxRepository->findOneBy(['countryCode' => $countryCode]);

        if (!$tax) {
            $this->context->buildViolation($constraint->unknownCountryMessage)
                ->setParameter('{{ country }}', $countryCode)
                ->addViolation();
            return;
        }

        $pattern = '/' . $tax->getTaxNumberPattern() . '/';
        
        if (!preg_match($pattern, $value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->setParameter('{{ country }}', $countryCode)
                ->addViolation();
        }
    }
}
