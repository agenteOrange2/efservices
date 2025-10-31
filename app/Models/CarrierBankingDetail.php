<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class CarrierBankingDetail extends Model
{
    protected $fillable = [
        'carrier_id',
        'account_number',
        'account_holder_name',
        'banking_routing_number',
        'zip_code',
        'security_code',
        'country_code',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'account_number' => 'encrypted',
        'account_holder_name' => 'encrypted',
        'banking_routing_number' => 'encrypted',
        'zip_code' => 'encrypted',
        'security_code' => 'encrypted',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * Get the carrier that owns the banking details.
     */
    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    /**
     * Check if the banking details are approved.
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if the banking details are pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if the banking details are rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Override getAttribute to log decryption operations
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);
        
        // Log decryption for encrypted fields
        if (in_array($key, ['account_number', 'account_holder_name', 'banking_routing_number', 'zip_code', 'security_code'])) {
            $rawValue = $this->getRawOriginal($key);
            
            Log::info('CarrierBankingDetail - Field decryption', [
                'model_id' => $this->id,
                'carrier_id' => $this->carrier_id,
                'field' => $key,
                'has_raw_value' => !is_null($rawValue),
                'raw_value_length' => $rawValue ? strlen($rawValue) : 0,
                'decrypted_value_length' => $value ? strlen($value) : 0,
                'is_encrypted' => $rawValue !== $value,
                'decryption_successful' => !is_null($value),
                'timestamp' => now()->toDateTimeString()
            ]);
        }
        
        return $value;
    }

    /**
     * Override setAttribute to log encryption operations
     */
    public function setAttribute($key, $value)
    {
        // Log encryption for encrypted fields before setting
        if (in_array($key, ['account_number', 'account_holder_name', 'banking_routing_number', 'zip_code', 'security_code'])) {
            Log::info('CarrierBankingDetail - Field encryption', [
                'model_id' => $this->id,
                'carrier_id' => $this->carrier_id,
                'field' => $key,
                'original_value_length' => $value ? strlen($value) : 0,
                'has_value' => !is_null($value),
                'timestamp' => now()->toDateTimeString()
            ]);
        }
        
        return parent::setAttribute($key, $value);
    }

    /**
     * Override save to log the complete save operation
     */
    public function save(array $options = [])
    {
        $isNewRecord = !$this->exists;
        
        Log::info('CarrierBankingDetail - Save operation start', [
            'model_id' => $this->id,
            'carrier_id' => $this->carrier_id,
            'is_new_record' => $isNewRecord,
            'dirty_fields' => $this->getDirty(),
            'timestamp' => now()->toDateTimeString()
        ]);
        
        $result = parent::save($options);
        
        Log::info('CarrierBankingDetail - Save operation complete', [
            'model_id' => $this->id,
            'carrier_id' => $this->carrier_id,
            'was_new_record' => $isNewRecord,
            'save_successful' => $result,
            'final_status' => $this->status,
            'final_account_holder' => $this->account_holder_name ? substr($this->account_holder_name, 0, 10) . '***' : null,
            'timestamp' => now()->toDateTimeString()
        ]);
        
        return $result;
    }
}
