<?php

namespace App\DataTables;

use DataTables;

class Table extends DataTables
{
    protected $attributes = [];

    /**
     * Dynamically retrieve the value of an attribute.
     *
     * @param string $key
     * @return mixed|null
     */
    public function __get($key)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }
    }

    /**
     * Set a custom class attribute.
     *
     * @param mixed $key
     * @param mixed|null $value
     * @return $this
     */
    public function with($key, $value = null)
    {
        if (is_array($key)) {
            $this->attributes = array_merge($this->attributes, $key);
        } else {
            $this->attributes[$key] = $value;
        }

        return $this;
    }
}