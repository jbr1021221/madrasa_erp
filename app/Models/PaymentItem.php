<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'student_id',
        'fee_name',
        'base_fee_name',
        'fee_type',
        'month',
        'year',
        'amount',
        'original_amount',
        'discount',
        'due_date'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'original_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'due_date' => 'date'
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
