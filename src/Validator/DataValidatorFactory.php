<?php
declare(strict_types=1);

namespace DigitalRevolution\SymfonyRequestValidation\Validator;

use DigitalRevolution\SymfonyRequestValidation\Builder\MapBuilderFactory;
use DigitalRevolution\SymfonyRequestValidation\Builder\MapBuilderFactoryInterface;
use DigitalRevolution\SymfonyRequestValidation\Constraint\Type\RequestConstraint;
use DigitalRevolution\SymfonyRequestValidation\Constraint\Type\TraversableConstraint;
use DigitalRevolution\SymfonyRequestValidation\RequestValidationException;
use DigitalRevolution\SymfonyRequestValidation\Utility\InvalidArrayPathException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DataValidatorFactory
{
    /** @var MapBuilderFactoryInterface */
    private $factory;

    /** @var ValidatorInterface */
    private $validator;

    public function __construct(ValidatorInterface $validator = null, MapBuilderFactoryInterface $factory = null)
    {
        $this->validator = $validator ?? Validation::createValidator();
        $this->factory   = $factory ?? new MapBuilderFactory();
    }

    /**
     * @param Collection|array<string, string|Constraint|array<string|Constraint>>|null $queryDefinitions
     * @param Collection|array<string, string|Constraint|array<string|Constraint>>|null $requestDefinitions
     * @throws RequestValidationException
     * @throws InvalidArrayPathException
     */
    public function createRequestValidator($queryDefinitions, $requestDefinitions): RequestValidator
    {
        $options = [
            'queryConstraint'   => null,
            'requestConstraint' => null
        ];

        if ($queryDefinitions !== null) {
            $options['queryConstraint'] = $this->getConstraint($queryDefinitions);
        }
        if ($requestDefinitions !== null) {
            $options['requestConstraint'] = $this->getConstraint($requestDefinitions);
        }

        return new RequestValidator(new RequestConstraint($options), $this->validator);
    }

    /**
     * @param Collection|array<string, string|Constraint|array<string|Constraint>> $ruleDefinitions
     * @throws RequestValidationException
     * @throws InvalidArrayPathException
     */
    public function createArrayValidator($ruleDefinitions): ArrayValidator
    {
        return new ArrayValidator($this->getConstraint($ruleDefinitions), $this->validator);
    }

    /**
     * @param Collection|array<string, string|Constraint|array<string|Constraint>> $ruleDefinitions
     * @throws RequestValidationException
     * @throws InvalidArrayPathException
     */
    public function createTraversableDataValidator($ruleDefinitions): TraversableDataValidator
    {
        $constraint = new TraversableConstraint($this->getConstraint($ruleDefinitions));

        return new TraversableDataValidator($constraint, $this->validator);
    }

    /**
     * @param Collection|array<string, string|Constraint|array<string|Constraint>> $ruleDefinitions
     * @throws RequestValidationException
     * @throws InvalidArrayPathException
     */
    private function getConstraint($ruleDefinitions): Constraint
    {
        if ($ruleDefinitions instanceof Constraint) {
            return $ruleDefinitions;
        }

        $ruleList      = $this->factory->createRuleListMapBuilder()->build($ruleDefinitions);
        $constraintMap = $this->factory->createConstraintMapBuilder()->build($ruleList);
        return $this->factory->createConstraintCollectionBuilder()->build($constraintMap);
    }
}
