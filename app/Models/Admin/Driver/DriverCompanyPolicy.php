<?php

namespace App\Models\Admin\Driver;

use App\Models\UserDriverDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DriverCompanyPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_driver_detail_id',
        'consent_all_policies_attached',
        'substance_testing_consent',
        'authorization_consent',
        'fmcsa_clearinghouse_consent',
        'company_name'
    ];

    protected $casts = [
        'consent_all_policies_attached' => 'boolean',
        'substance_testing_consent' => 'boolean',
        'authorization_consent' => 'boolean',
        'fmcsa_clearinghouse_consent' => 'boolean'
    ];

    public function userDriverDetail()
    {
        return $this->belongsTo(UserDriverDetail::class);
    }
}