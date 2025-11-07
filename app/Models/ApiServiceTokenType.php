<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiServiceTokenType extends Model
{
    public $table = 'api_service_token_types';

    protected $fillable = [
        'api_service_id',
        'token_type_id',
    ];

    public $timestamps = false;
}
