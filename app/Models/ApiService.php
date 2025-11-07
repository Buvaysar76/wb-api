<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ApiService extends Model
{
    protected $fillable = [
        'code',
        'name',
    ];

    public function tokenTypes(): BelongsToMany
    {
        return $this->belongsToMany(TokenType::class, 'api_service_token_types');
    }
}
