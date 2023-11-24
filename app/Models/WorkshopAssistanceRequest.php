<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkshopAssistanceRequest extends Model
{
    use HasFactory;

    protected $table = 'workshop_assistance_requests';

    protected $fillable = [
        'workshop_id',
        'assistance_request_id',
    ];
}
