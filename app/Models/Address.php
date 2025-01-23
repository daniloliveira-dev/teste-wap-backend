<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Address extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    public $table = 'addresses';
    public $timestamps = false;
    protected $fillable = [
        'name',
        'foreign_table',
        'foreign_id',
        'postal_code',
        'state',
        'city',
        'sublocality',
        'street',
        'street_number',
        'complement'
    ];
}
