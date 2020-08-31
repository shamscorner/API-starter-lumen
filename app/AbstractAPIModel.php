<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

abstract class AbstractAPIModel extends Model
{
    /**
     * return the type of a model
     * 
     * @return string
     */
    abstract public function type();

    /**
     * return the allowed attributes of a model
     * 
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function allowedAttributes()
    {
        return collect($this->attributes)->filter(function ($item, $key) {
            return !collect($this->hidden)->contains($key) && $key !== 'id';
        })->merge([
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);
    }
}
