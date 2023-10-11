<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TraccarUser extends Model
{
    use HasFactory;

    protected $table = 'traccar_users';
    protected $fillable = [
        'email',
        'password',
        'hash',
    ];
}
