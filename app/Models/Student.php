<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'name',
        'father_name',
        'mother_name',
        'address',
        'mobile',
        'alt_mobile',
        'nid_file_path',
        'class_id',
        'section',
        'discounts', // Permanent discounts
        'program_type', // Hifz or Schooling
        'shift', // Morning or Evening
        'photo', // Student photo
        // New student details  
        'dob',
        'gender',
        'siblings_count',
        'birth_order',
        'last_school',
        'present_district',
        'permanent_address',
        'permanent_district',
        'blood_group',
        // Guardian details
        'guardian_occupation',
        'guardian_nationality',
        'guardian_phone',
        'guardian_email',
        'guardian_nid',
        'selected_fees', // Stores the list of fees the student is subscribed to
        'partial_payments', // Stores partial payment tracking for admission fees
    ];

    protected $casts = [
        'dob' => 'date',
        'discounts' => 'array',
        'selected_fees' => 'array',
        'partial_payments' => 'array',
    ];
    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'class_id');
    }

    public function fees()
    {
        return $this->hasMany(Fee::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}