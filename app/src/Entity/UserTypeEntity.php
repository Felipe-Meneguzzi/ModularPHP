<?php
declare(strict_types = 1);

namespace App\Entity;

use Illuminate\Database\Eloquent\Model;

class UserTypeEntity extends Model{
    protected $table = 'user_types';
    protected $primaryKey = 'uuid';

    protected $fillable = [
        'uuid',
        'name'
    ];

    protected $casts = [
        'uuid' => 'string'
    ];

    public $timestamps = false;

    public $incrementing = false;

}