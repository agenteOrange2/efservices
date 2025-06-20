<?php

namespace App\Models\Admin\Driver;

use Illuminate\Support\Str;
use App\Models\UserDriverDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmploymentVerificationToken extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array
     */
    protected $fillable = [
        'token',
        'driver_id',
        'employment_company_id',
        'email',
        'expires_at',
        'verified_at',
        'signature_path',
        'document_path',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Relación con el modelo DriverEmploymentCompany.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function employmentCompany()
    {
        return $this->belongsTo(DriverEmploymentCompany::class, 'employment_company_id');
    }

    /**
     * Relación con el modelo UserDriverDetail.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function driver()
    {
        return $this->belongsTo(UserDriverDetail::class, 'driver_id');
    }

    /**
     * Genera un token único para la verificación.
     *
     * @return string
     */
    public static function generateToken()
    {
        return Str::random(64);
    }

    /**
     * Verifica si el token ha expirado.
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    /**
     * Verifica si el token ya ha sido verificado.
     *
     * @return bool
     */
    public function isVerified()
    {
        return $this->verified_at !== null;
    }

    /**
     * Marca el token como verificado.
     *
     * @return $this
     */
    public function markAsVerified()
    {
        $this->verified_at = now();
        $this->save();

        return $this;
    }
}
