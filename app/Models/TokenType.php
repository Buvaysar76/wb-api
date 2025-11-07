<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TokenType extends Model
{
    protected $fillable = [
        'code',
        'name',
    ];

    public function apiServices(): BelongsToMany
    {
        return $this->belongsToMany(ApiService::class, 'api_service_token_types');
    }
}
