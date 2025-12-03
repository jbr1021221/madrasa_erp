<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

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
    ];

    protected $casts = [
        'dob' => 'date',
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