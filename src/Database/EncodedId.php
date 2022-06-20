<?php

namespace Attla\Database;

use Attla\Jwt;
use Illuminate\Database\Eloquent\Model;

class EncodedId
{
    /**
     * Get a encoded id
     *
     * @return string
     */
    public static function generate(Model $model)
    {
        $key = $model->getKeyName();
        return !empty($model->{$key}) ? Jwt::id($model->{$key}) : null;
    }

    /**
     * Check if value is a encodedId and decode it
     *
     * @param mixed $value
     * @return mixed
     */
    public static function resolver($value)
    {
        if (is_array($value)) {
            return array_map([get_called_class(), 'resolver'], $value);
        }

        if (is_string($value) and $encodedId = Jwt::decode($value)) {
            return $encodedId;
        }

        return $value;
    }
}
