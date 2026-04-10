# Phase 2 QA Testing Guide (Streamlined)
**Status**: Ready to test | **Estimated Time**: 1-2 hours

---

## Quick Start: Manual Testing Steps

### 1️⃣ Desktop Testing (1920px) - 15 min
**Goal**: No regression, check color consistency

```
Browser DevTools → F12 → Ctrl+Shift+M → Toggle Device Toolbar OFF
```

**Checklist**:
- [ ] Dashboard page loads normally
- [ ] Settings pages (Agencies, Purposes, Resource Types, Program Names, Form Fields)
  - [ ] Tables display in normal table format (headers visible, proper columns)
  - [ ] Filter cards display correctly
  - [ ] CRUD modals open and form fields visible
- [ ] Beneficiaries list page
  - [ ] Search/filter working
  - [ ] Table shows all columns
  - [ ] Action buttons visible (View, Edit, Delete)
- [ ] Check colors:
  - [ ] Status badges on beneficiaries page (Active = green, Inactive = gray)
  - [ ] Icons on dashboard (Farmers = blue, Fisherfolk = cyan, Both = purple)
  - [ ] All badge colors consistent
- [ ] No console errors (F12 → Console tab)

---

### 2️⃣ Tablet Testing (768px) - 15 min
**Goal**: Verify responsive columns and tables still functional

```
DevTools → F12 → Ctrl+Shift+M → Set width to 768px
```

**Checklist**:
- [ ] Beneficiaries form: 2-column layout
  - [ ] Agency/Classification/Status in 2 columns (not 3)
  - [ ] Name/Date/Civil Status in 2 columns
- [ ] Settings modals: Form fields in 2 columns
- [ ] Settings page tables:
  - [ ] Still display as normal tables (NOT cards yet)
  - [ ] Headers visible
  - [ ] Columns readable (may be slightly compressed)
  - [ ] All action buttons clickable
- [ ] Buttons are large enough to tap (44px+ height)
- [ ] No horizontal scroll

---

### 3️⃣ Mobile Testing (375px) - 30 min
**Goal**: Verify mobile-first grid, table transformation, typography

```
DevTools → F12 → Ctrl+Shift+M → iPhone SE (375px) or Pixel 5 (393px)
```

#### Part A: Form Stacking (10 min)
- [ ] **Beneficiaries form**:
  - All fields stack single column (full width)
  - [ ] Agency select: full width
  - [ ] Classification select: full width
  - [ ] First/Middle/Last Name: each full width
  - [ ] Submit button: full width and tappable (44px+)
- [ ] **Distribution Events form**:
  - [ ] Form fields single column
  - [ ] Tab content responsive
  - [ ] No horizontal scroll
- [ ] **Settings modals**:
  - [ ] Form fields stack single column
  - [ ] Submit/Cancel buttons at bottom, full tappable area

#### Part B: Table Transformation (10 min)
**Navigate to**: Admin > Settings > Agencies
- [ ] **Table TRANSFORMS to card layout**:
  - [ ] Table headers (HIDDEN) - should NOT see column headers
  - [ ] Each row displays as a CARD BLOCK with border
  - [ ] Each row has padding and space between
  - [ ] Data displays as "Label: Value" pairs:
    - [ ] "Name: BFAR"
    - [ ] "Full Name: Bureau of..."
    - [ ] "Description: ..."
    - [ ] "Status: Active/Inactive badge"
    - [ ] "Actions: Edit/Deactivate buttons"
- [ ] All action buttons clickable (Edit, Deactivate/Activate)
- [ ] Card layout looks like this:
  ```
  ┌─────────────────────┐
  │ Name: BFAR          │
  │ Full Name: Bureau...│
  │ Description: Coastal│
  │ Status: Active      │
  │ Actions: [Edit]...  │
  └─────────────────────┘
  ```

**Repeat for**:
- [ ] Purposes page
- [ ] Resource Types page
- [ ] Program Names page
- [ ] Form Fields page (in accordion)

#### Part C: Typography (5 min)
- [ ] Headings readable (not too small)
  - [ ] Page title (h1): Clear and bold
  - [ ] Section headings (h3): Readable
- [ ] Body text legible (14px minimum)
  - [ ] Form labels clear
  - [ ] Table card labels bold
  - [ ] Description text readable
- [ ] No text overlapping or bunching
- [ ] Line-height gives breathing room

#### Part D: Touch Targets (5 min)
- [ ] All buttons minimum 44px height
  - [ ] Edit buttons in tables
  - [ ] Delete buttons in tables
  - [ ] Modal action buttons (Save, Cancel)
  - [ ] Form submit button
  - [ ] Filter buttons
- [ ] Form inputs 44px+ height
  - [ ] Text inputs
  - [ ] Select dropdowns
  - [ ] Checkboxes larger/tappable
- [ ] Can easily tap everything on real phone

---

### 4️⃣ Cross-Browser Testing (15 min)
**Test in each browser**, spot-check key pages

**Chrome Desktop/Mobile**:
- [ ] Desktop main page
- [ ] Mobile Beneficiaries form (col-12 stacking)
- [ ] Mobile Agencies table (card transformation)

**Firefox Desktop**:
- [ ] Dashboard loads
- [ ] Settings pages functional

**Safari Desktop** (if available):
- [ ] Dashboard loads
- [ ] Colors render correctly

**Mobile Safari (iOS - if available)**:
- [ ] Contact form stacks (col-12)
- [ ] Table transforms to cards
- [ ] Touch targets work

**Chrome Mobile (Android - if available)**:
- [ ] Same as iOS - form stacking, table cards

---

### 5️⃣ WCAG AA Quick Compliance Check (10 min)
**Use**: Chrome DevTools Lighthouse

```
DevTools → F12 → Lighthouse tab → Run audit (Accessibility)
```

**Target**: Score ≥ 80

**Manual Checks**:
- [ ] Color contrast readable
  - [ ] Black text on white: ✓
  - [ ] White text on blue badge: ✓
  - [ ] Gray text on white: ✓
- [ ] Can navigate with Tab key
  - [ ] Tab through form fields in order
  - [ ] Focus indicator visible on buttons
  - [ ] Can open/close modals with keyboard
- [ ] Form labels associated with inputs
  - [ ] Click label → input focuses
  - [ ] For attribute present (inspect element)

---

## One-Minute Issue Checklist

**If you find issues**, check:

| Issue | Check |
|-------|-------|
| Mobile form NOT single-column | Verify `class="col-12 col-md-*"` in file |
| Table NOT transforming to cards on mobile | Check `table-responsive-cards` class + data-label attributes |
| Colors inconsistent | Verify `var(--color-name)` used in inline styles |
| Touch targets too small (< 44px) | Check DevTools computed height on buttons |
| Text unreadable on mobile | Check @media (max-width: 575px) typography rules |
| Horizontal scroll on mobile | Check no fixed widths, verify col-12 applied |

---

## Pass/Fail Criteria

### ✅ **Phase 2 PASSES if**:
- [ ] All desktop pages load (1920px) without regression
- [ ] Tablet (768px) shows responsive columns (col-md in effect)
- [ ] Mobile (375px):
  - [ ] All form fields stack single-column (col-12)
  - [ ] All 5 settings page tables transform to card layout
  - [ ] Data-labels appear before values
  - [ ] Headers hidden on tables
  - [ ] Typography readable (14px+ text)
  - [ ] Touch targets 44px+ (buttons clickable)
- [ ] No horizontal scroll at any viewport
- [ ] No console errors (F12 → Console)
- [ ] Cross-browser (Chrome, Firefox, Safari) loads without issues
- [ ] Colors consistent (CSS variables applied)
- [ ] Lighthouse Accessibility score ≥ 80

### ❌ **Phase 2 FAILS if**:
- [ ] Desktop regression (layout breaks, colors wrong)
- [ ] Mobile forms DON'T stack to single column
- [ ] Mobile tables DON'T transform to card layout
- [ ] Horizontal scroll appears on mobile
- [ ] Touch targets < 44px (buttons too small)
- [ ] Console errors/warnings
- [ ] Lighthouse Accessibility < 70

---

## Testing Device Setup

### Local Browser DevTools (Easiest)
- Chrome DevTools: `F12 → Ctrl+Shift+M` (Device Toolbar)
- Firefox: `Ctrl+Shift+M` (Responsive Design Mode)
- Safari: `Develop → Enter Responsive Design Mode`

### Real Devices (Best)
- iPhone: Safari
- Android: Chrome
- Tablet: iPad Safari or Android tablet

### Emulation Tools
- BrowserStack (cross-browser, paid)
- LambdaTest (cross-browser, paid)

---

## Stop & Report

**If issues found**:
1. Screenshot the problem
2. DevTools Inspect (right-click → Inspect)
3. Check:
   - Device width (DevTools)
   - Viewport (in DevTools Console: `window.innerWidth`)
   - Applied media query (DevTools Computed section)
   - CSS class on element (class attribute)
4. Report with:
   - Device/browser/width
   - Expected behavior
   - Actual behavior
   - Screenshot
   - HTML snippet (what's wrong)

**Example**: "iPhone 375px, Settings > Agencies table, table NOT transforming to cards on mobile. Headers still visible. Should be hidden and rows display as cards."

---

## Success Path

**IF ALL TESTS PASS**:
1. ✅ Update memory: "Phase 2 QA: PASSED - Ready for production"
2. ✅ Commit: "Phase 2 QA: PASSED - All tests passed"
3. ✅ Deploy to staging
4. ✅ Schedule production deployment

**IF ISSUES FOUND**:
1. 🔧 Report issue
2. 🔧 Fix in code
3. ✅ Re-test fix
4. Repeat until all pass

---

**Ready to start? Begin with Step 1️⃣ Desktop Testing (15 min)**

Let me know what you find! 🚀

