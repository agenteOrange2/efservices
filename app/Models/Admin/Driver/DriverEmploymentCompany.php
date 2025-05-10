<?php

namespace App\Models\Admin\Driver;

use App\Models\UserDriverDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DriverEmploymentCompany extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_driver_detail_id',
        'master_company_id',
        'employed_from',
        'employed_to',
        'positions_held',
        'subject_to_fmcsr',
        'safety_sensitive_function',
        'reason_for_leaving',
        'other_reason_description',
        'email',
        'explanation'
    ];

    protected $casts = [
        'employed_from' => 'date',
        'employed_to' => 'date',
        'subject_to_fmcsr' => 'boolean',
        'safety_sensitive_function' => 'boolean',
    ];


    public function userDriverDetail()
    {
        return $this->belongsTo(UserDriverDetail::class);
    }

    public function company()
    {
        return $this->belongsTo(MasterCompany::class, 'master_company_id');
    }

    // Add the missing relationship
    public function masterCompany()
    {
        return $this->belongsTo(MasterCompany::class);
    }
}
