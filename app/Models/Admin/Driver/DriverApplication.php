<?php
namespace App\Models\Admin\Driver;

use App\Models\User;
use App\Models\UserDriverDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class DriverApplication extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;
    
    protected $fillable = [
        'user_id',
        'status',        
        'pdf_path',
        'completed_at'
    ];
    
    protected $casts = [
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
    
    public function userDriverDetail()
    {
        return $this->belongsTo(UserDriverDetail::class, 'user_id', 'user_id');
    }
    
    public function addresses()
    {
        return $this->hasMany(DriverAddress::class);
    }
    
    public function details()
    {
        return $this->hasOne(DriverApplicationDetail::class);
    }
    
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('application_pdf')
            ->singleFile();
    }
}