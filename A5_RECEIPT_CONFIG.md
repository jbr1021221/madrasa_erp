# A5 Receipt Configuration Summary

## Changes Made

The student admission receipt has been optimized for **A5 paper size** (148mm × 210mm).

### PDF Configuration
- **Paper Size**: A5
- **Orientation**: Portrait
- **Location**: `StudentController.php` methods `downloadReceipt()` and `viewReceipt()`

### CSS Optimizations for A5

All styling has been scaled down to fit the smaller A5 format:

#### Layout & Spacing
- Body padding: 20px → **10px**
- Container padding: 30px → **15px**
- Base font size: default → **10px**
- Line height: 1.6 → **1.4**

#### Header
- H1 font size: 28px → **18px**
- H2 font size: 20px → **14px**
- Subtitle: 12px → **9px**
- Bottom border: 3px → **2px**
- Padding bottom: 20px → **10px**
- Margin bottom: 30px → **15px**

#### Section Titles
- Font size: 16px → **11px**
- Padding: 10px 15px → **6px 10px**
- Margin: 25px 0 15px 0 → **12px 0 8px 0**

#### Info Grid
- Label width: 35% → **40%**
- Cell padding: 6px 10px → **4px 6px**
- Font size: default → **9px**
- Margin bottom: 20px → **10px**

#### Payment Summary
- Padding: 20px → **12px**
- Margin: 25px 0 → **12px 0**
- Label font size: default → **10px**
- Amount font size: 18px → **12px**
- Total amount: 24px → **16px**
- Total label: 18px → **12px**
- Row margin: 10px → **6px**

#### Signature Section
- Margin top: 50px → **20px**
- Signature line margin: 60px → **30px**
- Font size: default → **9px**

#### Footer
- Margin top: 40px → **15px**
- Padding top: 20px → **10px**
- Main text: 11px → **8px**
- Copyright text: 10px → **7px**

#### Watermark
- Font size: 100px → **60px**
- Opacity: 0.1 → **0.08**

#### Badge
- Padding: 5px 12px → **3px 8px**
- Font size: 11px → **8px**

#### Date/Time
- Font size: 11px → **8px**
- Margin bottom: 20px → **10px**

## Benefits of A5 Size

1. **Cost Effective**: Uses half the paper of A4
2. **Compact**: Easier to store and file
3. **Professional**: Standard receipt size
4. **Portable**: Convenient for parents/guardians to carry
5. **Eco-Friendly**: Reduces paper waste

## Printing Tips

1. Set printer to **A5** paper size
2. Use **Portrait** orientation
3. Ensure margins are set to minimum
4. For best results, use quality paper (80gsm or higher)
5. Print in color for professional appearance

## Testing

To test the receipt:
1. Go to any student in the students list
2. Click the green "Receipt" button
3. The PDF will download in A5 format
4. Open and verify the layout fits properly

## File Modified
- `app/Http/Controllers/StudentController.php` - Added `->setPaper('a5', 'portrait')`
- `resources/views/students/receipt.blade.php` - Optimized all CSS for A5 dimensions
