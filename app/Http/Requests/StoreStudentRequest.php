<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_id' => 'nullable|string',
            'name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'mobile' => ['required', 'string', 'regex:/^(?:01|\+8801)\d{9}$/'],
            'alt_mobile' => ['nullable', 'string', 'regex:/^(?:01|\+8801)\d{9}$/'],
            'class_id' => 'required|exists:classrooms,id',
            'section' => 'required|string|max:100',
            'program_type' => 'required|array|min:1',
            'program_type.*' => 'in:Hifz,Schooling',
            'shift' => 'required|string|in:Morning,Evening',
            'discounts' => 'nullable|array',
            'nid_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'payment_mode' => 'required|string',
            'payment_note' => 'nullable|string',
            'total_admission_fee' => 'required|numeric|min:0',
            'pay_first_month' => 'nullable|string|in:yes,no',
            'first_month' => 'nullable|string',
            'first_month_fee' => 'nullable|numeric|min:0',
            // New student details
            'dob' => 'nullable|date|before:today',
            'gender' => 'nullable|string|in:Male,Female,Other',
            'blood_group' => 'nullable|string|max:5',
            'last_school' => 'nullable|string|max:255',
            'siblings_count' => 'nullable|integer|min:0',
            'birth_order' => 'nullable|integer|min:1',
            // Address details
            'present_district' => 'nullable|string|max:255',
            'permanent_address' => 'nullable|string',
            'permanent_district' => 'nullable|string|max:255',
            // Guardian details
            'guardian_occupation' => 'nullable|string|max:255',
            'guardian_nationality' => 'nullable|string|max:255',
            'guardian_phone' => ['nullable', 'string', 'regex:/^(?:01|\+8801)\d{9}$/'],
            'guardian_email' => 'nullable|email|max:255',
            'guardian_nid' => 'nullable|string|max:30',
            // Fee months for monthly fees
            'fee_months' => 'nullable|array',
            'fee_months.*' => 'nullable|string|max:20',
            // Partial payment fields
            'is_partial_payment' => 'nullable|boolean',
            'partial_amount' => 'nullable|numeric|min:0',
        ];
    }
}
