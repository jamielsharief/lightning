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
        $this->errors->reset();

        # Prepare Data
        if ($data instanceof ServerRequestInterface) {
            $data = $data->getParsedBody();
        } elseif (is_object($data)) {
            $data = $this->getProperties($data);
        }

        foreach ($this->validate as $field => $validationSet) {
            $value = $data[$field] ?? null;

            foreach ($validationSet->toArray() as $validationRule) {
                if ($validationRule['rule'] === 'optional') {
                    if ($this->validation->empty($value)) {
                        break;
                    }

                    continue;
                }

                $object = $this->validation;
                if (method_exists($this, $validationRule['rule'])) {
                    $object = $this;
                    array_push($validationRule['args'], $data); // add data to method
                }

                if (! call_user_func_array([$object,$validationRule['rule']], [$value,  ...$validationRule['args']])) {
                    $this->errors->setError($field, $validationRule['message']);
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
            $data[$property->getName()] = $property->getValue($object);
        }

        return $data;
    }

    /**
     * Adds a validation rule
     */
    public function createRuleFor(string $name): ValidationSet
    {
        return $this->validate[$name] = $this->createValdiationSet();
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
}
