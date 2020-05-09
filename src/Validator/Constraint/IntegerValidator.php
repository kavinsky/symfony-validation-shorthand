<?php
declare(strict_types=1);

namespace DigitalRevolution\SymfonyRequestValidation\Validator\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class IntegerValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint): void
    {
        if ($constraint instanceof Integer === false) {
            throw new UnexpectedTypeException($constraint, Integer::class);
        }

        if ($value === null && $constraint->allowNull) {
            return;
        }

        if (is_int($value)) {
            return;
        }

        // value should be either int or string
        if (is_string($value) === false) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setCode($constraint::INVALID_VALUE_TYPE)
                ->addViolation();
        }

        // value can't be cast to int
        if (((string)(int)$value) !== $value) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->setCode($constraint::INVALID_NUMBER_ERROR)
                ->addViolation();
        }
    }
}
