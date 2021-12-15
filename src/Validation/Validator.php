<?php

namespace Attla\Validation;

use Attla\Encrypter;
use Illuminate\Validation\Validator as BaseValidator;

class Validator extends BaseValidator
{
    /**
     * Check if value is a endoded id and decode it
     *
     * @param array $value
     * @return mixed
     */
    public function resolveEncodedId($value)
    {
        if ($encodedId = Encrypter::jwtDecode($value)) {
            $value = $encodedId;
        }

        return $value;
    }

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
        $this->requireParameterCount(1, $parameters, 'exists');

        [$connection, $table] = $this->parseTable($parameters[0]);

        // The second parameter position holds the name of the column that should be
        // verified as existing. If this parameter is not specified we will guess
        // that the columns being "verified" shares the given attribute's name.
        $column = $this->getQueryColumn($parameters, $attribute);

        $expected = is_array($value) ? count(array_unique($value)) : 1;

        return $this->getExistCount(
            $connection,
            $table,
            $column,
            $this->resolveEncodedId($value),
            $parameters
        ) >= $expected;
    }
}
