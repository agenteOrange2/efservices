<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Carrier extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'address',
        'state',
        'zipcode',
        'ein_number',
        'dot_number',
        'mc_number',
        'state_dot',
        'ifta_account',
        'logo_img',
        'id_plan',
        'status',
    ];


    // Relación con usuarios (manager)
    public function managers()
    {
        return $this->belongsToMany(User::class, 'user_carrier')
            ->withPivot('status')
            ->withTimestamps();
    }

    // Relación con documentos
    public function documents()
    {
        return $this->hasMany(CarrierDocument::class);
    }
}
