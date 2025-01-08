<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activities extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'activities';

    protected $fillable = [
        'description',
        'hour_amount',
        'users_id',
        'projects_id',
    ];

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function projects(): BelongsTo
    {
        return $this->belongsTo(Projects::class);
    }
}
