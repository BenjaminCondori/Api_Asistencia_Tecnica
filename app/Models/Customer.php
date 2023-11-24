<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customers';

    protected $fillable = [
        'name',
        'surname',
        'phone',
        'photo',
    ];

    // RELATIONSHIPS

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'customer_id');
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class, 'customer_id');
    }

    public function assistanceRequests(): HasMany
    {
        return $this->hasMany(AssistanceRequest::class, 'customer_id');
    }
}
