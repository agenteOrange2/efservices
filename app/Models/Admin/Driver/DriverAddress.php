<?php

namespace App\Models\Admin\Driver;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DriverAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_application_id',
        'primary',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'zip_code',
        'lived_three_years',
        'from_date',
        'to_date'
    ];

    protected $casts = [
        'lived_three_years' => 'boolean',
        'from_date' => 'date',
        'to_date' => 'date'        
    ];

    public function application()
    {
        return $this->belongsTo(DriverApplication::class, 'driver_application_id');
    }

    public function isPrimary(): bool
    {
        return $this->primary;
    }

    public function getPeriodInYears(): float
    {
        return Carbon::parse($this->from_date)
            ->diffInYears(Carbon::parse($this->to_date ?? now()));
    }
}
