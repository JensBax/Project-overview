<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Projects extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'projects';

    protected $fillable = [
        'description',
        'address',
        'city',
        'client',
        'price',
        'users_id',
        'duration_in_days',
    ];

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expenses::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activities::class);
    }
}
