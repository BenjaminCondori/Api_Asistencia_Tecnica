<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workshop extends Model
{
    use HasFactory;

    protected $table = 'workshops';

    protected $fillable = [
        'name',
        'description',
        'address',
        'phone',
        'photo',
    ];

    // RELATIONSHIPS

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'workshop_id');
    }

    public function technicians()
    {
      return $this->hasMany(Technician::class, 'workshop_id');
    }

    public function assistanceRequests(): BelongsToMany
    {
      return $this->belongsToMany(AssistanceRequest::class, 'workshop_assistance_requests', 'workshop_id', 'assistance_request_id');
    }
}
