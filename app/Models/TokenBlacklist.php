<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenBlacklist extends Model
{
    protected $table = 'tokens_blacklist';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'token',
    ];
}
