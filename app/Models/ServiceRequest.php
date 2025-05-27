<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_user_id',
        'service_provider_user_id',
        'service_category_id',
        'subject',
        'description',
        'initial_fee_amount',
        'invoice_id',
        'chat_id',
        'status',
        'admin_notes',
        'rejection_reason',
        'request_type',
        'province_id',
        'city_id',
        'scope_type',
        'accepted_service_provider_user_id',
        'accepted_at',
        'available_until',
        'completed_at'
    ];

    protected $casts = [
        'initial_fee_amount' => 'decimal:2',
        'accepted_at' => 'datetime',
        'available_until' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_user_id');
    }

    public function serviceProvider()
    {
        return $this->belongsTo(User::class, 'service_provider_user_id');
    }

    public function acceptedServiceProvider()
    {
        return $this->belongsTo(User::class, 'accepted_service_provider_user_id');
    }

    public function serviceCategory()
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function files()
    {
        return $this->hasMany(RequestFile::class, 'request_id');
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending_payment');
    }

    public function scopePendingAdminApproval($query)
    {
        return $query->where('status', 'pending_admin_approval');
    }

    public function scopePendingSpAcceptance($query)
    {
        return $query->where('status', 'pending_sp_acceptance');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeReadyForReview($query)
    {
        return $query->where('status', 'ready_for_review');
    }

    // Validation rules
    public static function rules($type = 'create')
    {
        $rules = [
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'request_type' => 'required|in:private,public',
            'service_category_id' => 'required_if:request_type,private|exists:service_categories,id',
            'service_provider_user_id' => 'required_if:request_type,private|exists:users,id',
            'province_id' => 'required_if:request_type,public|exists:provinces,id',
            'city_id' => 'required_if:request_type,public|exists:cities,id',
            'scope_type' => 'required_if:request_type,public|in:city_wide,nation_wide',
        ];

        if ($type === 'update') {
            $rules['status'] = 'sometimes|required|in:pending_payment,pending_admin_approval,approved_by_admin,rejected_by_admin,pending_sp_acceptance,accepted_by_sp,rejected_by_sp,canceled_by_customer,completed,expired,ready_for_review';
            $rules['admin_notes'] = 'nullable|string|max:1000';
            $rules['rejection_reason'] = 'nullable|string|max:1000';
        }

        return $rules;
    }
} 