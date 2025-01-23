<?php

namespace App\Models\Admin\Driver;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DriverApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'social_security_number',        
        'email',
        'phone',
        'status'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'status' => 'string'
    ];

    // Constantes para status
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function addresses()
    {
        return $this->hasMany(DriverAddress::class);
    }

    public function details()
    {
        return $this->hasOne(DriverApplicationDetail::class);
    }

    public function getCurrentStep()
    {
        if (!$this->details) return 1;
        if ($this->addresses->isEmpty()) return 2;
        if (!$this->details->applying_position) return 3;
        return 4;
    }
}