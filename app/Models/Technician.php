<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Technician extends Model
{
    use HasFactory;

    protected $table = 'technicians';

    protected $fillable = [
        'name',
        'surname',
        'phone',
        'specialty',
        'status',
        'photo',
        'workshop_id',
    ];

    // RELATIONSHIPS

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'technician_id');
    }

    public function workshop(): BelongsTo
    {
      return $this->belongsTo(Workshop::class, 'workshop_id');
    }

    public function assistanceRequests(): HasMany
    {
      return $this->hasMany(AssistanceRequest::class, 'technician_id');
    }
}
