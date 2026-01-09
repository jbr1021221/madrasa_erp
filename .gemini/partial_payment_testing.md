# Partial Payment Fix - Testing Guide

## Issue Fixed
The `partial_payments` field was not being saved to the database because:
1. ❌ Not in `$fillable` array in Student model
2. ❌ Not in `$casts` array in Student model  
3. ❌ Not in validation rules

## Changes Made

### 1. Student Model (`app/Models/Student.php`)
Added to `$fillable`:
```php
'partial_payments', // Stores partial payment tracking for admission fees
```

Added to `$casts`:
```php
'partial_payments' => 'array',
```

### 2. Validation Rules (`app/Http/Requests/StoreStudentRequest.php`)
Added validation for partial payment fields:
```php
'is_partial_payment' => 'nullable|boolean',
'partial_amount' => 'nullable|numeric|min:0',
```

### 3. Fixed Existing Student (26015054)
Manually updated the student record with partial payment data from logs:
```json
{
    "Admission Fee": {
        "total": 50000,
        "paid": 25639,
        "remaining": 24361,
        "payment_ids": []
    }
}
```

## How to Test

### Test 1: Verify Existing Student Shows Remaining Fee
1. Go to Students list page
2. Find student ID: **26015054**
3. Click the **"Fees"** button
4. **Expected Result**: In the "Other Fees" section, you should see:
   - "Admission Fee (Remaining)" with **DUE** badge
   - Amount: ৳24,361.00
   - Yellow/amber background and border

### Test 2: Create New Student with Partial Payment
1. Click "Add Student"
2. Fill in required fields
3. Select a class (admission fee will be added automatically)
4. Check the **"Pay Partial Admission Fee"** checkbox
5. Enter a partial amount (e.g., ৳20,000 out of ৳50,000)
6. Verify the display shows:
   - Paying Now: ৳20,000 (green)
   - Remaining Due: ৳30,000 (red)
7. Submit the form
8. Go back to Students list
9. Click "Fees" button for the new student
10. **Expected Result**: Should see "Admission Fee (Remaining)" with ৳30,000

### Test 3: Pay Remaining Fee
1. In the payment modal, click on "Admission Fee (Remaining)"
2. It should be added to the payment table
3. Complete the payment
4. **Expected Result**: 
   - Payment should be recorded
   - Remaining fee should disappear from "Other Fees"
   - Student's `partial_payments` should be updated

## Debugging

If the remaining fee doesn't show:

1. **Check Database**:
```bash
php artisan tinker
$student = App\Models\Student::where('student_id', '26015054')->first();
dd($student->partial_payments);
```

2. **Check Browser Console**:
   - Open browser DevTools (F12)
   - Go to Console tab
   - Click "Fees" button
   - Look for: `Added partial fee: Admission Fee - Remaining: 24361`

3. **Check Logs**:
```bash
tail -f storage/logs/laravel.log
```

## Expected Console Output

When opening payment modal for student 26015054:
```
=== Payment Modal Debug ===
Subscribed Fees: [...]
Initial Active Fees: [...]
Partial Payments: {Admission Fee: {total: 50000, paid: 25639, remaining: 24361, payment_ids: []}}
Added partial fee: Admission Fee - Remaining: 24361
```

## Visual Indicators

The partial fee should have:
- ✅ **"DUE" badge** in amber color
- ✅ Yellow/amber background (`rgba(255,193,7,0.08)`)
- ✅ Yellow border (`1px solid rgba(255,193,7,0.3)`)
- ✅ Amount in amber color (`#ffc107`)
- ✅ Special hover effect (brighter yellow on hover)

## Notes

- Future students created with partial payments will automatically work
- The fix is backward compatible
- Existing students without partial payments are unaffected
- Payment completion will update the `partial_payments` record
