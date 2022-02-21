<?php

namespace Attla\Database;

use Attla\Encrypter;
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
        return !empty($model->{$key}) ? jwt()->id($model->{$key}) : null;
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

        if (is_string($value) and $encodedId = Encrypter::jwtDecode($value)) {
            return $encodedId;
        }

        return $value;
    }
}
