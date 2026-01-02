<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'class_id',
        'sections',
        'max_students_per_section',
        'admission_fee',
        'fees',
        'total_fee'
    ];

    protected $casts = [
        'sections' => 'array',
        'fees' => 'array',
        'admission_fee' => 'decimal:2',
        'total_fee' => 'decimal:2'
    ];

    /**
     * Calculate total fee from admission fee + additional fees
     */
    public function calculateTotalFee()
    {
        $additionalFees = 0;
        if (!empty($this->fees)) {
            $additionalFees = collect($this->fees)->sum('amount');
        }

        return $this->admission_fee + $additionalFees;
    }

    /**
     * Get formatted sections string
     */
    public function getSectionsStringAttribute()
    {
        return implode(', ', $this->sections ?? []);
    }

    /**
     * Get formatted fees string
     */
    public function getFeesStringAttribute()
    {
        if (empty($this->fees)) {
            return 'No fees';
        }

        return collect($this->fees)
            ->map(fn($fee) => "{$fee['name']}: ৳{$fee['amount']}")
            ->implode(' | ');
    }
}