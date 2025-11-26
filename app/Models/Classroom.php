<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sections',
        'max_students_per_section',
        'fees',
        'total_fee'
    ];

    protected $casts = [
        'sections' => 'array',
        'fees' => 'array',
        'total_fee' => 'decimal:2'
    ];

    /**
     * Calculate total fee from fees array
     */
    public function calculateTotalFee()
    {
        if (empty($this->fees)) {
            return 0;
        }

        return collect($this->fees)->sum('amount');
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