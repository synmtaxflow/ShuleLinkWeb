# Postman Setup - Fix 405 Error

## Problem: 405 Method Not Allowed

If you're getting a **405 Method Not Allowed** error, you're likely using **query parameters** instead of **JSON body**.

---

## ❌ Wrong Way (Query Parameters)

**This will NOT work:**

```
POST http://192.168.100.100:8000/api/v1/users/register?id=87&name=MWAKABANJE
```

**Error:** `405 Method Not Allowed`

---

## ✅ Correct Way (JSON Body)

### Step-by-Step in Postman:

1. **Set Method to POST**
   - Select `POST` from the dropdown

2. **Enter URL (NO query parameters)**
   ```
   http://192.168.100.100:8000/api/v1/users/register
   ```
   - ❌ **DON'T** add `?id=87&name=MWAKABANJE`

3. **Go to Headers Tab**
   - Add header: `Content-Type` = `application/json`
   - Add header: `Accept` = `application/json`

4. **Go to Body Tab**
   - Select `raw` radio button
   - Select `JSON` from the dropdown (on the right)
   - Enter JSON:
   ```json
   {
       "id": "87",
       "name": "MWAKABANJE"
   }
   ```

5. **Click Send**

---

## Visual Guide

### ✅ Correct Postman Setup:

```
┌─────────────────────────────────────────────────┐
│ POST │ http://192.168.100.100:8000/api/v1/... │ Send │
├─────────────────────────────────────────────────┤
│ Params │ Authorization │ Headers │ Body │ ... │
├─────────────────────────────────────────────────┤
│ Headers Tab:                                     │
│ Content-Type: application/json                   │
│ Accept: application/json                         │
├─────────────────────────────────────────────────┤
│ Body Tab:                                        │
│ ○ none  ○ form-data  ○ x-www-form-urlencoded    │
│ ● raw   ○ binary     ○ GraphQL                  │
│                                                  │
│ [JSON ▼]                                         │
│                                                  │
│ {                                                │
│     "id": "87",                                  │
│     "name": "MWAKABANJE"                         │
│ }                                                │
└─────────────────────────────────────────────────┘
```

---

## Quick Test

Use this exact setup:

**URL:**
```
http://192.168.100.100:8000/api/v1/users/register
```

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Body (raw JSON):**
```json
{
    "id": "87",
    "name": "MWAKABANJE"
}
```

**Expected Response (201 Created):**
```json
{
    "success": true,
    "message": "User created successfully",
    "data": {
        "id": 24,
        "name": "MWAKABANJE",
        "enroll_id": "87",
        "registered_on_device": false
    }
}
```

---

## Common Mistakes

1. ❌ Adding query parameters to URL
   - `?id=87&name=MWAKABANJE`

2. ❌ Using GET instead of POST
   - Must use POST method

3. ❌ Not setting Content-Type header
   - Must be `application/json`

4. ❌ Using form-data instead of raw JSON
   - Must use raw JSON body

---

## Test with cURL

If you want to test with command line:

```bash
curl -X POST http://192.168.100.100:8000/api/v1/users/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"id":"87","name":"MWAKABANJE"}'
```

---

**Remember:** Always send `id` and `name` as JSON in the request body, NOT as query parameters!


