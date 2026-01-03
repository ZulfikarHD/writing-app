# 🧪 Testing Guide: Prompt Advanced Features (FG-05.4)

**Feature:** Prompt Components & Inputs  
**Sprint:** 27  
**Last Updated:** 2026-01-04

---

## Overview

Dokumen ini berisi panduan testing untuk fitur Prompt Components dan Prompt Inputs, yaitu: memastikan semua fungsionalitas CRUD bekerja dengan benar, UI responsif di semua ukuran layar, dan integrasi antara frontend dan backend berjalan lancar.

---

## Pre-Testing Checklist

- [ ] Backend server running (`php artisan serve`)
- [ ] Frontend dev server running (`yarn dev`)
- [ ] Database migrated (`php artisan migrate`)
- [ ] User logged in

---

## Test Cases

### TC-01: Component CRUD Operations

| ID | Scenario | Steps | Expected Result |
|----|----------|-------|-----------------|
| TC-01.1 | Create component | 1. Go to Workspace sidebar<br>2. Click "Blocks" tab<br>3. Click "+ New Component"<br>4. Fill name, label, content<br>5. Click "Create Component" | Component created, appears in list |
| TC-01.2 | Create with invalid name | Same as above but use spaces in name | Validation error shown |
| TC-01.3 | Edit component | 1. Click component in list<br>2. Modify content<br>3. Save | Component updated |
| TC-01.4 | Clone component | 1. Click component dropdown<br>2. Select "Clone"<br>| New component with "_copy" suffix |
| TC-01.5 | Delete component | 1. Click component dropdown<br>2. Select "Delete"<br>3. Confirm | Component removed from list |
| TC-01.6 | Cannot edit system component | Open a system component | Edit fields disabled, notice shown |

### TC-02: Component Usage in Prompts

| ID | Scenario | Steps | Expected Result |
|----|----------|-------|-----------------|
| TC-02.1 | Insert via autocomplete | 1. Edit prompt<br>2. In instructions, type `{include(`<br>3. Select component | `{include("name")}` inserted |
| TC-02.2 | Copy include syntax | 1. Open component<br>2. Click "Copy include() call" | Syntax copied to clipboard |
| TC-02.3 | Preview resolution | 1. Add `{include("x")}` to prompt<br>2. Enable "Show with sample data" | Component content shown inline |
| TC-02.4 | Track usages | 1. Create prompt using component<br>2. Call GET `/api/prompt-components/{id}/usages` | Prompt appears in usages list |

### TC-03: Input Form

| ID | Scenario | Steps | Expected Result |
|----|----------|-------|-----------------|
| TC-03.1 | Show input form | 1. Create prompt with inputs defined<br>2. Execute prompt | InputForm modal appears |
| TC-03.2 | Required validation | Leave required field empty, submit | Validation error shown |
| TC-03.3 | Default values | Create input with default | Form pre-filled with default |
| TC-03.4 | Select dropdown | Create select input with options | Dropdown shows all options |
| TC-03.5 | Submit and execute | Fill all inputs, submit | Values passed to prompt |

### TC-04: TabAdvanced UX

| ID | Scenario | Steps | Expected Result |
|----|----------|-------|-----------------|
| TC-04.1 | Add input | Click "+ Add Input" | New input form expanded |
| TC-04.2 | Copy input syntax | Click on `{input("name")}` badge | Copied, checkmark shown |
| TC-04.3 | Invalid input name | Enter name with spaces | Red border, error message |
| TC-04.4 | View components | Scroll to Components section | All available components listed |
| TC-04.5 | Insert component | Click "+" on component row | Component inserted |

### TC-05: Preview Panel

| ID | Scenario | Steps | Expected Result |
|----|----------|-------|-----------------|
| TC-05.1 | Toggle resolution | Check "Show with sample data" | Variables resolved with samples |
| TC-05.2 | Test inputs panel | 1. Add inputs to prompt<br>2. Click "Test Inputs" | Panel opens with input fields |
| TC-05.3 | Live input preview | Change value in test input | Preview updates immediately |
| TC-05.4 | Legend display | Enable resolution | Legend shows all marker types |

---

## API Testing

### Component Endpoints

```bash
# List components
curl -X GET http://localhost:8000/api/prompt-components \
  -H "Authorization: Bearer <token>"

# Create component  
curl -X POST http://localhost:8000/api/prompt-components \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"name":"test_comp","label":"Test","content":"Test content"}'

# Get usages
curl -X GET http://localhost:8000/api/prompt-components/1/usages \
  -H "Authorization: Bearer <token>"

# Clone
curl -X POST http://localhost:8000/api/prompt-components/1/clone \
  -H "Authorization: Bearer <token>"

# Delete
curl -X DELETE http://localhost:8000/api/prompt-components/1 \
  -H "Authorization: Bearer <token>"
```

### Expected Responses

| Endpoint | Success | Error |
|----------|---------|-------|
| POST create | 201 with component data | 422 validation error |
| PATCH update | 200 with updated data | 403 for system components |
| DELETE | 200 success | 403 for system components |
| GET usages | 200 with usages array | 403 if not accessible |

---

## Mobile Testing

| ID | Test Case | Expected |
|----|-----------|----------|
| M-01 | Components list on mobile | Full width, proper spacing |
| M-02 | ComponentEditor on mobile | Full screen modal |
| M-03 | InputForm on mobile | Touch-friendly inputs |
| M-04 | TabAdvanced on mobile | Single column layout |

---

## Edge Cases

| ID | Scenario | Expected Behavior |
|----|----------|-------------------|
| E-01 | Circular component reference | Should not infinite loop |
| E-02 | Delete component used by prompts | Warning shown, prompts updated |
| E-03 | Duplicate component name | 422 error with message |
| E-04 | Very long component content | Truncated in preview |
| E-05 | Empty inputs array | "No inputs" message shown |

---

## Related Documentation

- **API Reference:** [Prompts API](../04-api-reference/prompts.md)
- **Sprint Documentation:** [Sprint 27](../10-sprints/sprint-27-prompt-advanced-features.md)
- **User Journeys:** [Prompt Editor Flow](../07-user-journeys/prompts/prompt-editor-flow.md)

---

*Last Updated: 2026-01-04*
