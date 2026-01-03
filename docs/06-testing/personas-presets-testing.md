# Testing Guide: Personas & Presets

**Feature:** Prompt Personas & Presets  
**Sprint:** 26  
**Test Date:** 2026-01-03  
**Status:** ✅ Implementation Complete

---

## Overview

Test plan untuk fitur Personas dan Presets yang memungkinkan users untuk:
1. Membuat dan manage reusable AI personas
2. Menyimpan dan apply prompt configurations (presets)
3. Sharing instructions across prompts dan projects

---

## Test Environment Setup

### Prerequisites

- [ ] Database migrations untuk `prompt_personas` dan `prompt_presets` sudah dijalankan
- [ ] User sudah login ke aplikasi
- [ ] Ada minimal 1 prompt yang sudah dibuat untuk testing presets
- [ ] Browser: Chrome/Edge/Firefox (latest)

### Test Data

```sql
-- Verify tables exist
SELECT * FROM prompt_personas LIMIT 1;
SELECT * FROM prompt_presets LIMIT 1;

-- Check indexes
SHOW INDEXES FROM prompt_personas;
SHOW INDEXES FROM prompt_presets;
```

---

## Manual Testing Checklist

### 1. Personas - Create & List

| Test ID | Scenario | Steps | Expected Result | Status |
|---------|----------|-------|-----------------|--------|
| **PC-001** | Navigate to Personas tab | 1. Go to workspace sidebar<br>2. Click "Personas" tab | Tab switches, shows personas list or empty state | ☐ |
| **PC-002** | Create persona button visible | 1. Be on Personas tab | "New Persona" button visible at bottom | ☐ |
| **PC-003** | Open persona editor | 1. Click "+ New Persona" | PersonaEditor modal opens with empty form | ☐ |
| **PC-004** | Create valid persona | 1. Fill name: "Test Style"<br>2. Fill system message<br>3. Click "Create Persona" | Persona created, appears in list, modal closes | ☐ |
| **PC-005** | Name required validation | 1. Open editor<br>2. Leave name empty<br>3. Click create | Error shown, creation blocked | ☐ |
| **PC-006** | System message required | 1. Fill name only<br>2. Leave system message empty<br>3. Click create | Error shown, creation blocked | ☐ |

### 2. Personas - Interaction Types

| Test ID | Scenario | Steps | Expected Result | Status |
|---------|----------|-------|-----------------|--------|
| **PI-001** | Select single interaction type | 1. Open editor<br>2. Click "Chat" checkbox | Checkbox selected, highlighted | ☐ |
| **PI-002** | Select multiple types | 1. Click "Chat" and "Prose" | Both selected, both highlighted | ☐ |
| **PI-003** | Deselect interaction type | 1. Select "Chat"<br>2. Click "Chat" again | Deselected, back to normal state | ☐ |
| **PI-004** | Save with no types selected | 1. Create persona without selecting types<br>2. Save | Saved as "All types" (null in DB) | ☐ |

### 3. Personas - Edit & Delete

| Test ID | Scenario | Steps | Expected Result | Status |
|---------|----------|-------|-----------------|--------|
| **PE-001** | Edit button on hover | 1. Hover over persona card | Edit button appears | ☐ |
| **PE-002** | Open edit modal | 1. Click edit button | Modal opens with persona data pre-filled | ☐ |
| **PE-003** | Update persona name | 1. Change name to "Updated"<br>2. Save | Name updated in list | ☐ |
| **PE-004** | Archive persona | 1. Open edit modal<br>2. Click "Archive"<br>3. Confirm | Persona removed from list | ☐ |
| **PE-005** | Delete persona | 1. Open edit modal<br>2. Click "Delete"<br>3. Confirm | Persona permanently deleted | ☐ |
| **PE-006** | Unsaved changes warning | 1. Edit persona<br>2. Change name<br>3. Click close (X) | Warning modal appears | ☐ |

### 4. Presets - Create & Apply

| Test ID | Scenario | Steps | Expected Result | Status |
|---------|----------|-------|-----------------|--------|
| **PR-001** | Preset section visible | 1. Open prompt editor<br>2. Go to "General" tab | "Presets" section visible | ☐ |
| **PR-002** | New preset button | 1. Be on General tab | "+ New Preset" button visible | ☐ |
| **PR-003** | Open preset editor | 1. Click "+ New Preset" | PresetEditor modal opens | ☐ |
| **PR-004** | Create valid preset | 1. Fill name: "High Creativity"<br>2. Set temperature: 0.9<br>3. Save | Preset created, appears in list | ☐ |
| **PR-005** | Preset name required | 1. Open editor<br>2. Leave name empty<br>3. Save | Error shown, blocked | ☐ |
| **PR-006** | Temperature validation | 1. Enter temperature: 3.0<br>2. Save | Validation error (max 2.0) | ☐ |

### 5. Presets - Model Settings

| Test ID | Scenario | Steps | Expected Result | Status |
|---------|----------|-------|-----------------|--------|
| **PM-001** | Set all model settings | 1. Fill all fields (temp, tokens, top_p, etc)<br>2. Save | All settings saved correctly | ☐ |
| **PM-002** | Add stop sequences | 1. Type "END" in input<br>2. Click "Add" | "END" appears as chip | ☐ |
| **PM-003** | Remove stop sequence | 1. Add "END"<br>2. Click X on chip | Chip removed | ☐ |
| **PM-004** | Set as default toggle | 1. Toggle "Set as default"<br>2. Save | Preset marked as default | ☐ |

### 6. Presets - Input Values

| Test ID | Scenario | Steps | Expected Result | Status |
|---------|----------|-------|-----------------|--------|
| **PV-001** | Pre-fill text input | 1. If prompt has text inputs<br>2. Fill value in preset | Value saved in preset | ☐ |
| **PV-002** | Pre-fill textarea | 1. Fill textarea input<br>2. Save preset | Textarea value saved | ☐ |
| **PV-003** | Pre-fill select | 1. Select option<br>2. Save preset | Selection saved | ☐ |
| **PV-004** | Pre-fill checkbox | 1. Check checkbox<br>2. Save preset | Checkbox state saved | ☐ |

### 7. Presets - Apply & Edit

| Test ID | Scenario | Steps | Expected Result | Status |
|---------|----------|-------|-----------------|--------|
| **PA-001** | Apply preset | 1. Click preset button | Model settings updated, "Using preset" indicator shown | ☐ |
| **PA-002** | Clear preset | 1. Apply preset<br>2. Click X on indicator | Settings remain, indicator removed | ☐ |
| **PA-003** | Manual change clears | 1. Apply preset<br>2. Manually change temperature | Preset indicator removed | ☐ |
| **PA-004** | Edit existing preset | 1. Hover preset<br>2. Click edit<br>3. Modify<br>4. Save | Preset updated | ☐ |
| **PA-005** | Delete preset | 1. Open preset editor<br>2. Click "Delete"<br>3. Confirm | Preset removed from list | ☐ |
| **PA-006** | Set different as default | 1. Click "Set as Default" on another preset | Other presets lose default, this one becomes default | ☐ |

### 8. API Integration

| Test ID | Scenario | Steps | Expected Result | Status |
|---------|----------|-------|-----------------|--------|
| **API-001** | GET /prompt-personas | Call API | Returns user's personas | ☐ |
| **API-002** | POST /prompt-personas | Create via API | Persona created in DB | ☐ |
| **API-003** | PATCH /prompt-personas/:id | Update via API | Persona updated | ☐ |
| **API-004** | DELETE /prompt-personas/:id | Delete via API | Persona deleted | ☐ |
| **API-005** | GET /prompts/:id/presets | Get presets for prompt | Returns presets | ☐ |
| **API-006** | POST /prompts/:id/presets | Create preset via API | Preset created | ☐ |
| **API-007** | POST /preset/:id/set-default | Set default via API | Other defaults unset | ☐ |

### 9. Mobile Responsiveness

| Test ID | Scenario | Device | Expected Result | Status |
|---------|----------|--------|-----------------|--------|
| **MR-001** | Personas tab mobile | iPhone/Android | Tab switches properly | ☐ |
| **MR-002** | Persona editor mobile | Mobile | Full-screen modal, all fields accessible | ☐ |
| **MR-003** | Interaction types mobile | Mobile | Checkboxes stack vertically | ☐ |
| **MR-004** | Preset editor mobile | Mobile | Bottom sheet, thumb-accessible buttons | ☐ |
| **MR-005** | Preset list mobile | Mobile | Cards stack properly | ☐ |

### 10. Edge Cases

| Test ID | Scenario | Expected Result | Status |
|---------|----------|-----------------|--------|
| **EC-001** | Create persona with very long name (300 chars) | Validation error at 255 | ☐ |
| **EC-002** | Create 50+ personas | All load properly, scroll works | ☐ |
| **EC-003** | Delete persona while viewing | Graceful handling, no errors | ☐ |
| **EC-004** | Create preset with negative temperature | Validation error | ☐ |
| **EC-005** | Preset with empty input_values | Saves as null, no errors | ☐ |
| **EC-006** | Apply deleted preset | Graceful handling | ☐ |
| **EC-007** | Two users creating personas simultaneously | Both save correctly | ☐ |
| **EC-008** | Persona with special characters in name | Saves and displays correctly | ☐ |
| **EC-009** | Preset with max_tokens: 999999 | Accepts or validates | ☐ |
| **EC-010** | Archive all personas | Empty state shown properly | ☐ |

---

## Automated Testing

### Backend Tests

```bash
# Run PHPUnit tests
php artisan test --filter=PersonaTest
php artisan test --filter=PresetTest
php artisan test --filter=PromptPersonaControllerTest
php artisan test --filter=PromptPresetControllerTest
```

### Frontend Tests (Optional)

```bash
# If Vitest/Jest setup exists
yarn test personas
yarn test presets
```

---

## Performance Testing

| Test | Metric | Target | Status |
|------|--------|--------|--------|
| Load 100 personas | < 500ms | - | ☐ |
| Create persona | < 200ms | - | ☐ |
| Load 50 presets for prompt | < 300ms | - | ☐ |
| Apply preset | < 100ms | - | ☐ |

---

## Security Testing

| Test ID | Scenario | Expected Result | Status |
|---------|----------|-----------------|--------|
| **SEC-001** | Access other user's persona | 403 Forbidden | ☐ |
| **SEC-002** | Delete other user's persona | 403 Forbidden | ☐ |
| **SEC-003** | SQL injection in persona name | Escaped, no injection | ☐ |
| **SEC-004** | XSS in system message | Sanitized on output | ☐ |
| **SEC-005** | CSRF token validation | Required for all mutations | ☐ |

---

## Regression Testing

Test existing features masih berfungsi setelah personas/presets added:

- [ ] Prompt editor masih bisa save prompt
- [ ] Prompt library masih load correctly
- [ ] Prompt execution tidak terpengaruh
- [ ] Variable resolution masih bekerja
- [ ] Prompt components tidak broken

---

## Known Issues & Limitations

| Issue | Severity | Status | Workaround |
|-------|----------|--------|------------|
| - | - | - | - |

---

## Test Summary Template

```
Date: YYYY-MM-DD
Tester: [Name]
Environment: [Dev/Staging/Prod]

Total Tests: XX
Passed: XX
Failed: XX
Blocked: XX

Critical Issues: [List if any]
Notes: [Additional observations]
```

---

## Related Documentation

- **API Reference:** [Personas & Presets API](../04-api-reference/personas-presets.md)
- **User Journeys:** [Personas & Presets User Journeys](../07-user-journeys/personas-presets/)
- **Sprint Documentation:** [Sprint 26: Personas & Presets](../10-sprints/sprint-26-personas-presets.md)

---

*Last Updated: 2026-01-03*
