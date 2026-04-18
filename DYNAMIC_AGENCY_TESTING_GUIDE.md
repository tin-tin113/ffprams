# Dynamic Agency System - Testing & Debugging Guide

## Overview
This guide helps you test and debug the dynamic agency form system implemented in Phase 4.2.

---

## Quick Start Testing

### 1. Test the API Endpoints

#### Option A: Using curl/PowerShell
```bash
# Test agencies by classification
curl "http://localhost/ffprams/api/agencies/by-classification?classification=Farmer"

# Test form fields for specific agencies
curl "http://localhost/ffprams/api/agencies/form-fields?agencies=1,2,3"
```

#### Option B: Using browser
Navigate to these URLs:
- `http://localhost/ffprams/api/agencies/by-classification?classification=Farmer`
- `http://localhost/ffprams/api/agencies/form-fields?agencies=1,2,3`

You should see JSON responses. If not, check the browser "Network" tab for status codes.

### 2. Test the Beneficiary Form

1. Navigate to: `http://localhost/ffprams/beneficiaries/create`
2. Open Browser Developer Tools (F12)
3. Go to "Console" tab
4. Follow these steps:

**Step 1: Select a Classification**
- Click dropdown for "Classification"
- Select "Farmer" or "Fisherfolk"
- Check console for errors
- Expected: Agency checkboxes should appear below

**Step 2: Select an Agency**
- Check one or more agency checkboxes
- Check console for errors
- Expected: Form fields should appear in the "Dynamic Agency" section

**Step 3: Test Required Field Toggle**
- Find a required field (has "I have it / I don't have it" toggle)
- Click "I have it" → input field should appear
- Click "I don't have it" → reason textarea should appear
- Toggle back and forth → sections should show/hide

**Step 4: Fill Out Form**
- Fill in the agency field values
- For required fields: either enter value OR explain why you don't have it
- Click "Register Beneficiary" button

**Step 5: Check Submission**
- Open "Network" tab in browser
- Submit the form
- Click the POST request to `/beneficiaries`
- Go to "Payload" or "Request" tab
- Verify the nested structure shows: `agencies[agencyId][fieldName]=value`

---

## Debugging Steps

### Issue 1: "Unable to load agency form fields"

#### Step 1a: Check API Response
Open your browser and go to:
```
http://localhost/ffprams/api/agencies/by-classification?classification=Farmer
```

**Expected response:**
```json
[
    {"id": 1, "name": "DA", "full_name": "Department of Agriculture"},
    {"id": 2, "name": "BFAR", "full_name": "Bureau of Fisheries and Aquatic Resources"}
]
```

**If you get an error (404, 500, etc.):**
- Check `routes/web.php` lines 157-160
- Verify the route is registered under a middleware group
- Check if the API routes need to include `'api'` in URL prefix

#### Step 1b: Check Browser Console
1. F12 → Console tab
2. Look for red error messages
3. Common errors:
   - "Cannot read property 'getElementById'" → form elements missing
   - "fetch is not defined" → browser compatibility issue
   - Network errors → API endpoint not working

#### Step 1c: Check Network Tab
1. F12 → Network tab
2. Select "Fetch/XHR" filter
3. Do the action that fails
4. Look for failed requests (red background)
5. Click the request and check:
   - **Status**: Should be 200
   - **Response**: Should be valid JSON
   - **Response Headers**: Check if CORS headers present (if cross-origin)

#### Step 1d: Check Database
Verify that data exists in the database:

**Via Tinker (Laravel shell):**
```bash
php artisan tinker
Agency::with('classifications', 'formFields')->get()
```

**What to verify:**
- Classifications table has "Farmer" and "Fisherfolk"
- agency_classifications table has pivot records
- agency_form_fields table has fields for each agency

### Issue 2: Agencies Load but Form Fields Don't Appear

#### Step 2a: Check JavaScript Events
1. F12 → Console
2. Type: `document.getElementById('agency-checkboxes')`
3. Should return the checkboxes container element (not null)
4. Verify the checkboxes have the class `agency-checkbox`

#### Step 2b: Check Event Listener
1. In Console, check if DynamicAgencyForm is initialized:
```javascript
// This should show the class definition
window.DynamicAgencyForm
```

2. Check if form data loaded correctly:
```javascript
// In Console type this:
document.querySelectorAll('.agency-checkbox').forEach(c => console.log(c.value))
```

#### Step 2c: Check API Response for Form Fields
When you check an agency, the form fields API should be called:
1. F12 → Network → XHR
2. Click an agency checkbox
3. Look for a request to `/api/agencies/form-fields?agencies=...`
4. Check response is valid JSON with form field definitions

### Issue 3: Toggle Not Showing/Hiding Sections

#### Step 3a: Verify HTML Structure
1. F12 → Elements tab
2. Find a required field
3. Verify it has these elements:
   - `<input type="radio" class="btn-check has-value-toggle">`
   - `<div class="has-value-section" data-toggle-id="toggle_...">`
   - `<div class="no-value-section" data-toggle-id="toggle_...">`
4. Verify `data-toggle-id` values MATCH between radio and divs

#### Step 3b: Check Toggle Functionality
1. Inspect the radio buttons
2. Click the "I have it" radio
3. Check that the `has-value-section` div gets `style="display: block"`
4. Check that the `no-value-section` div gets `style="display: none"`

#### Step 3c: Debug Event Listener
1. F12 → Console
2. Type this to manually trigger an event:
```javascript
const radio = document.querySelector('.has-value-toggle');
if (radio) {
    radio.click();
    console.log('Clicked radio, toggle id:', radio.dataset.toggleId);
}
```

---

## Form Data Structure

When submitting, the form sends this structure:

```
agencies[1][rsbsa_number] = "RS-12345"
agencies[1][rsbsa_number_has_value] = "1"
agencies[1][rsbsa_number_unavailability_reason] = ""

agencies[2][fishr_certificate] = ""
agencies[2][fishr_certificate_has_value] = "0"
agencies[2][fishr_certificate_unavailability_reason] = "Don't have certificate yet"
```

**Key Points:**
- `agencies[agencyId][fieldName]` = the actual value
- `agencies[agencyId][fieldName_has_value]` = 1 (has value) or 0 (doesn't have)
- `agencies[agencyId][fieldName_unavailability_reason]` = explanation if no value

---

## Testing Checklist

### Backend
- [ ] Agencies exist in database (DA, BFAR, DAR)
- [ ] Classifications exist (Farmer, Fisherfolk)
- [ ] agency_classifications pivot has mappings
- [ ] agency_form_fields has fields for each agency
- [ ] Form fields are marked as active (is_active = 1)

### Frontend
- [ ] Classification dropdown exists and has options
- [ ] Agency checkboxes container is empty initially
- [ ] Form fields container for dynamic content exists
- [ ] Script tag includes: `public/js/beneficiary-dynamic-agencies.js`
- [ ] No JavaScript errors in console

### Interactions
- [ ] Changing classification triggers API call
- [ ] Agencies appear as checkboxes
- [ ] Checking agencies triggers form fields API call
- [ ] Form fields appear in correct container
- [ ] Form field types render correctly (text, number, date, etc.)
- [ ] Required field toggle shows/hides sections
- [ ] Form submits without errors
- [ ] Nested data structure is correct in request

### Data Persistence (Edit Page)
- [ ] Go to `/beneficiaries/{id}/edit`
- [ ] Previous selections are checked
- [ ] Previous field values are populated
- [ ] Toggle state matches saved data
- [ ] Editing and submitting works

---

## Common Error Messages

### "TypeError: Cannot read property 'getElementById' of null"
**Cause**: DOM elements don't exist
**Fix**: Verify form has correct container IDs:
- `<div id="agency-checkboxes">`
- `<div id="dynamic-agencies-container">`
- `<select id="classification">`

### "Fetch is not defined"
**Cause**: Old browser that doesn't support fetch
**Fix**: Add polyfill or upgrade browser

### "422 Unprocessable Entity"
**Cause**: Form validation failed
**Fix**: Check browser console → Network tab → POST request → Response
Should show which fields failed validation

### "CSRF token mismatch"
**Cause**: Missing or invalid CSRF token
**Fix**: Verify form includes: `@csrf`

---

## Performance Notes

- **First Load**: Will fetch 2 API calls
  1. When page loads and classification is already selected
  2. When agencies are checked
- **Subsequent Changes**: Only fetches when classification/agency selection changes
- **String Operations**: Using data attributes instead of querying
- **Memory**: Stores form field data in JavaScript object for reuse

---

## Next Steps After Validation

Once testing confirms everything works:

1. **Direct Allocation Module**: Update to use dynamic agencies
2. **Events Management Module**: Update to use dynamic agencies
3. **Agency Filtering**: Replace hardcoded agency lists with dynamic system
4. **Admin Interface**: Create UI for managing agencies and form fields
5. **End-to-End Testing**: Test across all modules

---

Generated: 2026-04-18
Status: Ready for Browser Testing
