<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyDriverDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'driver_application_id',
        'employee_id',
        'department',
        'supervisor_name',
        'supervisor_phone',
        'salary_type',
        'base_rate',
        'overtime_rate',
        'benefits_eligible',
        'notes'
    ];

    protected $casts = [
        'base_rate' => 'float',
        'overtime_rate' => 'float',
        'benefits_eligible' => 'boolean'
    ];

    /**
     * Get the vehicle driver assignment that owns this company driver detail
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(VehicleDriverAssignment::class, 'assignment_id');
    }

    /**
     * Get the driver application associated with this company driver detail
     */
    public function driverApplication(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Admin\Driver\DriverApplication::class, 'driver_application_id');
    }



    /**
     * Get the vehicle through the assignment
     */
    public function vehicle()
    {
        return $this->assignment->vehicle();
    }

    /**
     * Get the user (driver) through the assignment
     */
    public function user()
    {
        return $this->assignment->user();
    }

    /**
     * Scope to filter by salary type
     */
    public function scopeBySalaryType($query, $salaryType)
    {
        return $query->where('salary_type', $salaryType);
    }

    /**
     * Scope to filter by department
     */
    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    /**
     * Scope to filter benefits eligible drivers
     */
    public function scopeBenefitsEligible($query)
    {
        return $query->where('benefits_eligible', true);
    }

    /**
     * Get formatted base rate with currency
     */
    public function getFormattedBaseRateAttribute()
    {
        return $this->base_rate ? '$' . number_format($this->base_rate, 2) : null;
    }

    /**
     * Get formatted overtime rate with currency
     */
    public function getFormattedOvertimeRateAttribute()
    {
        return $this->overtime_rate ? '$' . number_format($this->overtime_rate, 2) : null;
    }

    /**
     * Get salary type display name
     */
    public function getSalaryTypeDisplayAttribute()
    {
        $types = [
            'hourly' => 'Por Hora',
            'salary' => 'Salario Fijo',
            'commission' => 'Comisión',
            'per_mile' => 'Por Milla'
        ];

        return $types[$this->salary_type] ?? $this->salary_type;
    }

    /**
     * Get benefits status display
     */
    public function getBenefitsStatusAttribute()
    {
        return $this->benefits_eligible ? 'Elegible' : 'No Elegible';
    }
}