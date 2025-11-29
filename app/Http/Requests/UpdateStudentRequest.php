<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'mother_name' => 'required|string|max:255',
            'address' => 'required|string',
            'mobile' => ['required', 'string', 'regex:/^(?:\+88|88)?(01[3-9]\d{8})$/'],
            'alt_mobile' => ['nullable', 'string', 'regex:/^(?:\+88|88)?(01[3-9]\d{8})$/'],
            'class_id' => 'required|exists:classrooms,id',
            'section' => 'required|string|max:10',
            'nid_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            // New student details
            'dob' => 'required|date|before:today',
            'gender' => 'required|string|in:Male,Female,Other',
            'blood_group' => 'nullable|string|max:5',
            'last_school' => 'nullable|string|max:255',
            'siblings_count' => 'nullable|integer|min:0',
            'birth_order' => 'nullable|integer|min:1',
            // Address details
            'present_district' => 'required|string|max:255',
            'permanent_address' => 'nullable|string',
            'permanent_district' => 'nullable|string|max:255',
            // Guardian details
            'guardian_occupation' => 'required|string|max:255',
            'guardian_nationality' => 'required|string|max:255',
            'guardian_phone' => ['required', 'string', 'regex:/^(?:\+88|88)?(01[3-9]\d{8})$/'],
            'guardian_email' => 'nullable|email|max:255',
            'guardian_nid' => 'required|string|max:30',
        ];
    }
}
