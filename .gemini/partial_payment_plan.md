# Partial Payment Implementation Plan

## Overview
Implement partial payment functionality for admission and other one-time fees.

## Database Changes Needed
We'll use the existing `fee_details` in Payment model to track:
- `original_amount`: Total fee amount
- `paid_amount`: Amount paid in this payment
- `remaining_amount`: Amount still owed
- `is_partial`: Boolean flag

## Implementation Steps

### 1. Student Model
Add field to track partial payments:
- `partial_payments` (JSON): Track which fees are partially paid and remaining amounts

### 2. Student Creation (create.blade.php)
- Add "Partial Payment" checkbox for each fee
- Add input field for partial amount
- Validate that partial amount <= total amount
- Update total calculation to use partial amounts

### 3. StudentController
- Store partial payment info in fee_details
- Create payment record with partial flag
- Store remaining balance in student's partial_payments field

### 4. Payment Modal (index.blade.php)
- Check student's partial_payments field
- Show unpaid/partially paid admission fees in "Other Fees"
- Display remaining amount
- Remove from list once fully paid

### 5. PaymentController
- Handle partial payment completion
- Update student's partial_payments field
- Mark fee as fully paid when complete

## Data Structure

### Payment.fee_details (for partial payment):
```json
{
  "name": "Admission Fee",
  "type": "One Time",
  "amount": 10000,  // Amount paid in THIS payment
  "original_amount": 50000,  // Total fee amount
  "paid_amount": 10000,  // Cumulative paid
  "remaining_amount": 40000,  // Still owed
  "is_partial": true,
  "discount": 0
}
```

### Student.partial_payments:
```json
{
  "Admission Fee": {
    "total": 50000,
    "paid": 10000,
    "remaining": 40000,
    "payments": [155, 160]  // Payment IDs
  }
}
```
