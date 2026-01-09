# Partial Admission Fee Payment Implementation

## Overview
Implemented the ability to make partial admission fee payments during student creation, with the remaining balance automatically appearing in the payment modal for future payment.

## Changes Made

### 1. Student Creation Form (`resources/views/students/create.blade.php`)

#### UI Changes:
- Updated the partial payment section label from "Pay Partial Amount" to **"Pay Partial Admission Fee"** to clarify it applies only to admission fees
- The partial payment checkbox and input remain in the same location below the Student Fees table

#### JavaScript Changes:
- **Modified `updateTotal()` function** (lines 1083-1113):
  - Now separately tracks admission fees (One Time fees) vs monthly/recurring fees
  - Only admission fees are stored in `total_admission_fee` hidden field for partial payment calculation
  - Total display still shows all checked fees, but partial payment only applies to admission fees

### 2. Backend Controller (`app/Http/Controllers/StudentController.php`)

#### Enhanced Partial Payment Logic (lines 242-332):
- **Separates fees by type**: Admission fees (One Time) vs Monthly/Recurring fees
- **Applies partial payment only to admission fees**:
  - Calculates proportional amounts for each admission fee
  - Monthly fees are added to the payment as-is (not affected by partial payment)
  
- **Improved tracking structure**:
  ```php
  'partial_payments' => [
      'Admission Fee' => [
          'total' => 5000,
          'paid' => 2000,
          'remaining' => 3000,
          'payment_ids' => [123]
      ],
      'Registration Fee' => [
          'total' => 1000,
          'paid' => 500,
          'remaining' => 500,
          'payment_ids' => [123]
      ]
  ]
  ```
  - Each admission fee is tracked individually with its own total, paid, and remaining amounts
  - Payment IDs are stored for audit trail

### 3. Payment Modal (`resources/views/students/index.blade.php`)

#### Visual Enhancements (lines 491-509, 667-700):
- **Automatic detection**: Remaining admission fees are automatically added to `availableClassFees`
- **Visual highlighting**:
  - Partial payment fees have a yellow/amber background (`rgba(255,193,7,0.08)`)
  - Yellow border to make them stand out
  - **"DUE" badge** in amber color next to the fee name
  - Amount is displayed in amber color instead of the default accent color
  
- **Enhanced hover effects**: Maintains special styling for partial fees while providing visual feedback

## User Flow

### During Student Creation:
1. User fills in student details and selects class
2. Admission fees and other fees are automatically added to the Student Fees table
3. User can check the **"Pay Partial Admission Fee"** checkbox
4. User enters the amount they want to pay now (e.g., ৳2000 out of ৳5000)
5. System shows:
   - **Paying Now**: ৳2000 (in green)
   - **Remaining Due**: ৳3000 (in red)
6. User submits the form
7. Receipt shows the partial payment with "(Partial)" suffix

### During Future Payments:
1. User clicks "Fees" button for the student in the student list
2. Payment modal opens
3. **Remaining admission fees appear in "Other Fees" section** with:
   - Fee name + " (Remaining)"
   - **"DUE" badge** in amber
   - Yellow/amber background and border
   - Remaining amount displayed
4. User can click on the partial fee to add it to the payment
5. User completes the payment
6. System updates the `partial_payments` record with the new payment

## Technical Details

### Database Structure:
- `students.partial_payments` (JSON column):
  - Stores individual fee tracking
  - Each fee has: total, paid, remaining, payment_ids
  - Automatically updated when payments are made

### Fee Calculation:
- Admission fees are distributed proportionally when partial payment is made
- Monthly fees are never affected by partial payment logic
- Rounding errors are corrected on the last fee item

### Receipt Display:
- Partial payments show with "(Partial)" suffix
- Original amount and discount information are removed to avoid confusion
- Full payment history is maintained in payment_ids array

## Benefits

1. **Flexibility**: Families can pay admission fees in installments
2. **Transparency**: Clear tracking of what's paid and what's remaining
3. **Convenience**: Remaining fees automatically appear in payment modal
4. **Audit Trail**: All payment IDs are tracked for each fee
5. **Visual Clarity**: Special styling makes it obvious which fees have remaining balances

## Testing Recommendations

1. Create a new student with multiple admission fees
2. Select partial payment and pay 50% of total
3. Verify receipt shows partial amounts correctly
4. Go to student list and click "Fees" button
5. Verify remaining fees appear with "DUE" badge in Other Fees section
6. Complete the remaining payment
7. Verify student record shows all fees as fully paid
