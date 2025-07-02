<?php
declare(strict_types=1);

namespace App\Entity;

use Illuminate\Database\Eloquent\Model;

class UserEntity extends Model{
    protected $table = 'users';
    protected $primaryKey = 'uuid';

    protected $hidden = [
        'password'
    ];

    protected $fillable = [
        'uuid',
        'name',
        'login',
        'password',
        'email',
        'phone',
        'user_type_uuid',
        'cpf'
    ];

    protected $casts = [
        'uuid' => 'string'
    ];

    public $timestamps = true;

    public $incrementing = false;

}