<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AssistanceRequest extends Model
{
    use HasFactory;

    protected $table = 'assistance_requests';

    protected $fillable = [
        'status',
        'problem_description',
        'latitude',
        'longitude',
        'voice_note',
        'photo',
        'total_price',
        'customer_id',
        'vehicle_id',
        'technician_id'
    ];

    // RELATIONSHIPS

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function workshops(): BelongsToMany {
        return $this->belongsToMany(Workshop::class, 'workshop_assistance_requests', 'assistance_request_id', 'workshop_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(Technician::class, 'technician_id');
    }
}
