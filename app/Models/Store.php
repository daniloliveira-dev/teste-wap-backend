<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Store extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    public $table = 'stores';
    public $timestamps = false;
    protected $fillable = [
        'name'
    ];
}