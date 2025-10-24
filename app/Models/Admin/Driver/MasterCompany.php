<?php

namespace App\Models\Admin\Driver;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MasterCompany extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'address',
        'city',
        'state',
        'zip',
        'contact',
        'phone',
        'email',
        'fax',
    ];


    public function employmentHistories()
    {
        return $this->hasMany(DriverEmploymentCompany::class);
    }

    public function driverEmploymentCompanies()
    {
        return $this->hasMany(DriverEmploymentCompany::class);
    }
}