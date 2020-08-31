<?php

namespace App;

use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;
use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Database\Eloquent\Model;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use HasApiTokens, Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'email_verified_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * the allowed attributes
     */
    public $attributes = [];

    // incrementing false
    public $incrementing = false;
    // the primary key type
    protected $keyType = 'string';

    // model boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    /**
     * return the type of a model
     * 
     * @return string
     */
    public function type()
    {
        return 'users';
    }

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
            // 'token' => $this->createToken('Unnuio')->accessToken
        ]);
    }
}
