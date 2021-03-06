<?php declare(strict_types=1);
/**
 * LightningPHP
 * Copyright 2021 - 2022 Jamiel Sharief.
 *
 * Licensed under The MIT License
 * The above copyright notice and this permission notice shall be included in all copies or substantial
 * portions of the Software.
 *
 * @copyright   Copyright (c) Jamiel Sharief
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */

namespace Lightning\Validator;

use ReflectionClass;
use ReflectionProperty;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Validator Service
 */
class Validator
{
    private ValidationRules $validation;
    private Errors $errors ;
    private array $validate = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->validation = $this->createValidationRules();
        $this->errors = $this->createErrors();
        $this->initialize();
    }

    /**
     * A hook that is called when the object is created
     */
    protected function initialize(): void
    {
    }

    /**
     * Get the value of rules
     */
    public function getRules(): array
    {
        return $this->validate;
    }

    /**
     * Get the Errors object
     */
    public function getErrors(): Errors
    {
        return $this->errors;
    }

    /**
     * Set the value of errors
     */
    public function setErrors(Errors $errors): static
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * Validates the a server request, value object or an array of data
     */
    public function validate(object|array $data): bool
    {
        $this->errors = $this->createErrors(); // create or reset?

        # Prepare Data
        if ($data instanceof ServerRequestInterface) {
            $data = $data->getParsedBody();
        } elseif (is_object($data)) {
            $data = $this->getProperties($data);
        }

        foreach ($this->validate as $field => $validationSet) {
            $value = $data[$field] ?? null;

            if ($validationSet->isOptional() && $this->validation->empty($value)) {
                continue;
            }

            foreach ($validationSet->toArray() as $validation) {
                $object = $this->validation;

                if ($validation['rule'] === 'stopIfFailure') {
                    if ($this->errors->hasErrors()) {
                        break;
                    }

                    continue;
                }

                if (method_exists($this, $validation['rule'])) {
                    $object = $this;
                    array_push($validation['args'], $data); // add data to method
                }

                if (! call_user_func_array([$object,$validation['rule']], [$value,  ...$validation['args']])) {
                    $this->errors->setError($field, $validation['message']);
                    if ($validationSet->isStoppable()) {
                        break;
                    }
                }
            }
        }

        return $this->errors->hasErrors() === false;
    }

    /**
     * Using reflection will get properties of an object
     */
    private function getProperties(object $object): array
    {
        $reflection = new ReflectionClass($object);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE);

        $data = [];
        foreach ($properties as $property) {
            $property->setAccessible(true); // From 8.1 this has not effect and is not required
            if ($property->isInitialized($object)) {
                $data[$property->getName()] = $property->getValue($object);
            }
        }

        return $data;
    }

    /**
     * Creates a rule for property field
     */
    public function createRuleFor(string $property): ValidationSet
    {
        return $this->validate[$property] = $this->createValdiationSet();
    }

    /**
     * Removes a validators for a property
     */
    public function removeRuleFor(string $property): static
    {
        unset($this->validate[$property]);

        return $this;
    }

    /**
     * Gets a rule for a property field
     */
    public function getRuleFor(string $property): ?ValidationSet
    {
        return $this->validate[$property] ?? null;
    }

    /**
     * Checks if a rule exists for
     */
    public function hasRuleFor(string $property): bool
    {
        return key_exists($property, $this->validate);
    }

    /**
     * Returns a new validator without validators for a property
     */
    public function withoutRuleFor(string $property): static
    {
        return (clone $this)->removeRuleFor($property);
    }

    /**
     * Factory method
     */
    protected function createValidationRules(): ValidationRules
    {
        return new ValidationRules();
    }

    /**
     * Factory method
     */
    protected function createValdiationSet(): ValidationSet
    {
        return new ValidationSet();
    }

    /**
     * Factory method
     */
    protected function createErrors(): Errors
    {
        return new Errors();
    }

    /**
     * Deep copy
     */
    public function __clone()
    {
        foreach ($this->validate as $key => $value) {
            $this->validate[$key] = clone $value;
        }
    }
}
