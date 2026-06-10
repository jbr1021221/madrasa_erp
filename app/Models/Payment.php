<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'fee_id',
        'amount',
        'final_amount',
        'sub_total',
        'total_discount',
        'payment_type',
        'payment_mode',
        'month',
        'note',
        'payment_date',
        'transaction_id',
        'fee_details',
        'discount_breakdown',
        'share_token'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'final_amount' => 'decimal:2',
        'sub_total' => 'decimal:2',
        'total_discount' => 'decimal:2',
        'fee_details' => 'array',
        'discount_breakdown' => 'array',
    ];

    /**
     * Get the discount percentage for this payment
     */
    public function getDiscountPercentageAttribute()
    {
        if ($this->sub_total > 0) {
            return round(($this->total_discount / $this->sub_total) * 100, 2);
        }
        return 0;
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function fee()
    {
        return $this->belongsTo(Fee::class);
    }

    public function payment_items()
    {
        return $this->hasMany(PaymentItem::class);
    }

    /**
     * Generate a secure share token for this payment
     */
    public function generateShareToken(): string
    {
        $token = bin2hex(random_bytes(32)); // 64 character hex string
        $this->update(['share_token' => $token]);
        return $token;
    }

    /**
     * Find payment by share token
     */
    public static function findByShareToken(string $token): ?self
    {
        return static::where('share_token', $token)->first();
    }

    /**
     * Get or create share token for this payment
     */
    public function getOrCreateShareToken(): string
    {
        if (empty($this->share_token)) {
            return $this->generateShareToken();
        }
        return $this->share_token;
    }

    /**
     * Get public URL for sharing this payment receipt
     */
    public function getPublicShareUrl(): string
    {
        $token = $this->getOrCreateShareToken();
        return route('payments.public.receipt', $token);
    }
}