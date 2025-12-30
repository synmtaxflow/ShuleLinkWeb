# Fix: Duplicate Names in Attendance Records

## Issue
You're seeing the same user name appearing multiple times in attendance records.

## Why This Happens
This is **NORMAL** - it's not a bug! Each time a user punches in/out, a **new attendance record** is created. So if User #1 punches in 5 times, you'll see 5 records with User #1's name.

## Understanding the Data

Looking at your records:
- **Ally** (Enroll ID: 2) - Multiple records (different punch times)
- **Oen** (Enroll ID: 1) - Multiple records (different punch times)  
- **Hasani** (Enroll ID: 4) - Multiple records (different punch times)
- **ISSA** (Enroll ID: 5) - One record

This is **correct** - each record represents a separate punch in/out event.

## Status Field Issue

Some records show status as "15" instead of "Check In" or "Check Out". This is being fixed:
- `state: 1` = Check In ✓
- `state: 15` = Check Out (will now show as "Check Out")

## What Each Record Represents

Each row = One punch event:
- User punches in at 09:46:58 → Record #1
- User punches in at 09:46:48 → Record #2
- User punches in at 09:42:44 → Record #3
- etc.

## If You Want to See Only Latest Record Per User

This would require grouping/filtering, which is a different feature. Currently, the system shows all records chronologically (most recent first).

## Summary

✅ **Multiple records with same name = Normal** (each is a separate punch event)
✅ **Records are sorted by most recent first**
✅ **Status mapping is being fixed** (15 will show as Check Out)

The system is working correctly - you're just seeing all the punch records for each user!






