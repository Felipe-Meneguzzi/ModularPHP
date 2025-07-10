<?php
declare(strict_types = 1);

namespace App\Entity;

use Illuminate\Database\Eloquent\Model;

class RequestLogEntity extends Model{
    protected $table = 'request_logs';
    protected $fillable = [
        'user_id',
        'uri',
        'method',
        'headers',
        'body',
        'cookies',
        'agent',
        'time',
        'ip'
    ];
    public $timestamps = false;
}