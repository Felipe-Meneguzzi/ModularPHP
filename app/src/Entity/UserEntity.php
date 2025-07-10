<?php
declare(strict_types = 1);

namespace App\Entity;

use Illuminate\Database\Eloquent\Model;

class UserEntity extends Model{
    protected $table = 'users';
    protected $primaryKey = 'uuid';

    protected $hidden = [
        'password',
        'created_at',
        'updated_at'
    ];

    protected $fillable = [
        'uuid',
        'name',
        'login',
        'password',
        'email',
        'phone',
        'user_type_uuid',
        'cpf',
        'building_uuid',
        'company_uuid'
    ];

    protected $casts = [
        'uuid' => 'string'
    ];

    public array $searchIgnore = [
        'uuid',
        'user_type_uuid',
        'building_uuid',
        'company_uuid'
    ];

    public $timestamps = false;

    public $incrementing = false;

}