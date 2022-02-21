<?php

namespace Attla\Validation;

use Attla\Database\EncodedId;
use Illuminate\Validation\Validator as BaseValidator;

class Validator extends BaseValidator
{
    /**
     * Validate the existence of an attribute value in a database table
     *
     * @param string $attribute
     * @param mixed $value
     * @param array $parameters
     * @return bool
     */
    public function validateExists($attribute, $value, $parameters)
    {
        return parent::validateExists($attribute, EncodedId::resolver($value), $parameters);
    }
}
