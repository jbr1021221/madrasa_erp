<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentMonth extends Model
{
    protected $fillable = [
        'student_id',
        'month_key',
        'status', // 'paid' or null (unpaid)
        'payment_id'
    ];

    protected $casts = [
        'status' => 'string' // Allow null for unpaid
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function isPaid()
    {
        return $this->status === 'paid';
    }
}
