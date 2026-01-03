# 📦 Sprint 24: Prompt Library Core (FG-05.1)

**Version:** 1.0.0  
**Date:** 2026-01-03  
**Duration:** 2 Sprints  
**Status:** ✅ Completed

## 📋 Sprint Goals

Mengimplementasikan sistem Prompt Library yang memungkinkan user untuk membuat, mengelola, dan mengorganisir AI prompts untuk berbagai writing tasks, yaitu: mempercepat workflow penulisan, meningkatkan konsistensi output AI, dan memberikan fleksibilitas kustomisasi prompt sesuai kebutuhan.

**Reference:**  
- [Novelcrafter Prompts Documentation](https://www.novelcrafter.com/help/docs/prompts/prompt-library)
- EPIC: [05-EPIC-prompts-system.md](../../scrum/epic-planning/05-EPIC-prompts-system.md)

---

## ✨ Features Implemented

### 1. Prompt Library Management
- Browse prompts dalam dedicated page (`/prompts`)
- Filter prompts berdasarkan type (Chat, Prose, Replacement, Summary)
- Search prompts by name atau description
- View statistics (total prompts, usage count)
- Group prompts by type dengan separator antara system dan user prompts

### 2. CRUD Operations for Prompts
- **Create**: User dapat membuat prompt baru dengan kustomisasi lengkap
- **Read**: View prompt details dengan format yang jelas
- **Update**: Edit prompt yang sudah dibuat (hanya untuk user-owned prompts)
- **Delete**: Hapus prompt (system prompts tidak bisa dihapus)
- **Clone**: Duplicate prompt (termasuk system prompts) untuk customization

### 3. Prompt Types
Mendukung 4 tipe prompts sesuai use case:
- **Workshop Chat**: Untuk interactive chat dengan AI
- **Scene Beat Completion**: Untuk prose writing assistance
- **Text Replacement**: Untuk text improvement dan rewriting
- **Scene Summarization**: Untuk summarizing content

### 4. System & User Prompts
- **System Prompts**: Built-in prompts yang read-only, dapat di-clone
- **User Prompts**: Custom prompts yang dapat di-edit dan dihapus
- Access control: User hanya bisa lihat system prompts + own prompts

### 5. Folder Organization (Prepared)
- Database support untuk folder hierarchy
- Model relationships sudah ready untuk future implementation
- Saat ini prompts masih flat (tanpa folder UI)

### 6. Workspace Integration
- Prompts section di workspace sidebar
- Quick access untuk prompts yang sering digunakan
- Navigation ke Prompt Library dari workspace

### 7. Usage Tracking
- Record usage count setiap kali prompt digunakan
- Statistics untuk monitoring prompt popularity

---

## 📁 File Structure

### Backend Files

```
app/
├── Http/
│   ├── Controllers/
│   │   └── PromptController.php                    ✨ NEW - 11 endpoints
│   └── Requests/
│       ├── StorePromptRequest.php                  ✨ NEW - Validation untuk create
│       └── UpdatePromptRequest.php                 ✨ NEW - Validation untuk update
├── Models/
│   ├── Prompt.php                                  ✨ NEW - Main model dengan scopes
│   └── PromptFolder.php                            ✨ NEW - Folder organization
└── Services/
    └── Prompts/
        └── PromptService.php                       ✨ NEW - Business logic untuk prompts

database/
├── factories/
│   └── PromptFactory.php                           ✨ NEW - Factory dengan states
├── migrations/
│   ├── 2026_01_03_100000_create_prompt_folders_table.php  ✨ NEW
│   └── 2026_01_03_100001_create_prompts_table.php         ✨ NEW
└── seeders/
    └── PromptSeeder.php                            ✨ NEW - System prompts seeder
```

### Frontend Files

```
resources/js/
├── Pages/
│   └── Prompts/
│       └── Index.vue                               ✨ NEW - Main prompt library page
├── components/
│   ├── prompts/
│   │   ├── PromptCard.vue                          ✨ NEW - Prompt card display
│   │   └── PromptEditor.vue                        ✨ NEW - Form untuk create/edit
│   └── workspace/
│       └── SidebarToolSection.vue                  ✏️ UPDATED - Added prompts icon
├── composables/
│   ├── usePrompts.ts                               ✨ NEW - Prompt API composable
│   └── useWorkspaceState.ts                        ✏️ UPDATED - Added prompts tool
└── types/
    └── prompts.ts                                  ✨ NEW - TypeScript interfaces
```

### Test Files

```
tests/Feature/
└── PromptTest.php                                  ✨ NEW - 18 test cases (All passing ✅)
```

### Routes

```
routes/
├── web.php                                         ✏️ UPDATED - Added prompts index route
└── api.php                                         ✏️ UPDATED - Added 10 API routes
```

---

## 🔌 API Endpoints Summary

### Web Routes
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/prompts` | Prompt library page (Inertia) |

### API Routes (Prefix: `/api/prompts`)
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/prompts` | List all accessible prompts | ✅ |
| POST | `/api/prompts` | Create new prompt | ✅ |
| GET | `/api/prompts/types` | Get available prompt types | ✅ |
| GET | `/api/prompts/type/{type}` | Get prompts by type | ✅ |
| GET | `/api/prompts/{prompt}` | Get prompt detail | ✅ |
| PATCH | `/api/prompts/{prompt}` | Update prompt | ✅ |
| DELETE | `/api/prompts/{prompt}` | Delete prompt | ✅ |
| POST | `/api/prompts/{prompt}/clone` | Clone prompt | ✅ |
| POST | `/api/prompts/{prompt}/usage` | Record usage | ✅ |
| POST | `/api/prompts/reorder` | Reorder prompts | ✅ |

> 📡 Full API documentation: [Prompts API](../04-api-reference/prompts.md)

---

## 🗄️ Database Schema

### `prompts` Table

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | BIGINT | NO | Primary key |
| user_id | BIGINT | YES | NULL untuk system prompts |
| folder_id | BIGINT | YES | Foreign key ke prompt_folders |
| name | VARCHAR(255) | NO | Nama prompt |
| description | TEXT | YES | Deskripsi prompt |
| type | ENUM | NO | chat/prose/replacement/summary |
| system_message | LONGTEXT | YES | System message untuk AI |
| user_message | LONGTEXT | YES | User message template |
| model_settings | JSON | YES | Model parameters (temp, max_tokens, dll) |
| is_system | BOOLEAN | NO | True jika built-in prompt |
| is_active | BOOLEAN | NO | Status aktif |
| sort_order | INT | NO | Order dalam list |
| usage_count | INT | NO | Tracking usage |
| created_at | TIMESTAMP | NO | - |
| updated_at | TIMESTAMP | NO | - |

**Indexes:**
- `idx_prompts_user_type` on (user_id, type)
- `idx_prompts_user_system` on (user_id, is_system)
- `idx_prompts_system` on (is_system)

### `prompt_folders` Table

| Column | Type | Nullable | Description |
|--------|------|----------|-------------|
| id | BIGINT | NO | Primary key |
| user_id | BIGINT | NO | Foreign key ke users |
| parent_id | BIGINT | YES | For nested folders |
| name | VARCHAR(255) | NO | Folder name |
| color | VARCHAR(7) | YES | Hex color code |
| sort_order | INT | NO | Order dalam list |
| created_at | TIMESTAMP | NO | - |
| updated_at | TIMESTAMP | NO | - |

**Indexes:**
- `idx_folders_user_parent` on (user_id, parent_id)

---

## 📊 Component Architecture

### Data Flow

```
┌─────────────────────────────────────────────────────────────┐
│ Prompts/Index.vue (Page)                                    │
│  • Displays prompts in grid                                 │
│  • Handles filter & search                                  │
│  • Manages selected prompt state                            │
└────────┬──────────────────────────────┬────────────────────┘
         │                              │
         │ uses                         │ uses
         ▼                              ▼
┌──────────────────────┐      ┌─────────────────────┐
│ PromptCard.vue       │      │ PromptEditor.vue    │
│  • Display prompt    │      │  • Form untuk CRUD  │
│  • Actions (edit,    │      │  • Validation       │
│    delete, clone)    │      │  • Submit handler   │
└──────────┬───────────┘      └─────────┬───────────┘
           │                            │
           │ triggers                   │ calls
           ▼                            ▼
      ┌─────────────────────────────────────────┐
      │ usePrompts.ts (Composable)              │
      │  • createPrompt()                       │
      │  • updatePrompt()                       │
      │  • deletePrompt()                       │
      │  • clonePrompt()                        │
      └────────────────┬────────────────────────┘
                       │
                       │ HTTP requests
                       ▼
      ┌──────────────────────────────────────────┐
      │ API: /api/prompts/*                      │
      │  • PromptController                      │
      └────────────────┬─────────────────────────┘
                       │
                       │ delegates to
                       ▼
      ┌──────────────────────────────────────────┐
      │ PromptService                            │
      │  • Business logic                        │
      │  • Access control                        │
      │  • Data transformation                   │
      └────────────────┬─────────────────────────┘
                       │
                       │ queries
                       ▼
      ┌──────────────────────────────────────────┐
      │ Prompt Model + PromptFolder Model        │
      │  • Eloquent relationships                │
      │  • Query scopes                          │
      │  • Business methods                      │
      └──────────────────────────────────────────┘
```

---

## 🎨 UI/UX Features

### Prompt Library Page (`/prompts`)

**Layout:**
- Header dengan title, search bar, dan "Create Prompt" button
- Statistics cards (Total, User, System, Usage count)
- Filter by type (chips/tabs)
- Grid layout untuk prompt cards
- Empty states untuk no results

**Prompt Card:**
- Name (bold)
- Description (truncated)
- Type badge dengan icon
- Usage count indicator
- System badge (jika system prompt)
- Actions: Edit, Clone, Delete (hover untuk show actions)
- iOS-style animations (scale on press, spring transitions)

**Prompt Editor:**
- Modal/Slide-over untuk create/edit
- Form fields:
  - Name (required)
  - Description
  - Type selector
  - System Message (textarea dengan syntax highlighting)
  - User Message (textarea dengan variable support)
  - Model Settings (advanced section)
- Save/Cancel buttons
- Loading states
- Validation errors inline
- Success toast setelah save

### Workspace Integration

**Sidebar Prompts Section:**
- Collapsible section dengan icon
- Quick list untuk frequently used prompts
- "Manage Prompts" link → navigate to `/prompts`
- Empty state jika belum ada prompts

---

## 🧪 Testing Coverage

### Test Summary
- **Total Test Cases:** 18
- **Status:** ✅ All Passing
- **Coverage:** CRUD operations, filtering, authorization, cloning

### Test Categories
1. **Access Control** (5 tests)
   - List accessible prompts (system + own only)
   - Filter by type
   - Search functionality
   - Get by type endpoint
   - Cannot access other user's prompts

2. **CRUD Operations** (6 tests)
   - Create prompt
   - View prompt details
   - Update prompt
   - Delete prompt
   - Cannot edit system prompts
   - Cannot delete system prompts

3. **Clone Functionality** (3 tests)
   - Clone system prompt
   - Clone user prompt
   - Custom name untuk cloned prompt

4. **Usage Tracking** (2 tests)
   - Record usage increments count
   - Usage accessible by authorized user only

5. **Reordering** (1 test)
   - Reorder user's own prompts

6. **Get Types** (1 test)
   - Get available prompt types with labels

> 📋 Full test plan: [Prompts Testing Guide](../06-testing/prompts-testing.md)

---

## 🔒 Security Considerations

| Concern | Mitigation | Implementation |
|---------|-----------|----------------|
| **Unauthorized Access** | Policy-based authorization | `canBeEditedBy()`, `canBeDeletedBy()` methods |
| **System Prompt Protection** | Immutable system prompts | `is_system` check di service layer |
| **Data Isolation** | User-scoped queries | `accessibleBy()` scope di model |
| **XSS Prevention** | Input sanitization | Laravel validation + Vue escaping |
| **SQL Injection** | Eloquent ORM | Prepared statements via Eloquent |
| **Mass Assignment** | Fillable guard | `$fillable` property di model |

---

## ⚡ Performance Considerations

| Aspect | Optimization | Details |
|--------|-------------|---------|
| **Database Queries** | Proper indexing | Indexes pada user_id, type, is_system |
| **N+1 Prevention** | Eager loading | Future: with('folder', 'user') |
| **Pagination** | Lazy loading | Client-side filtering saat ini |
| **Caching** | Future enhancement | Cache system prompts |
| **Asset Size** | Code splitting | Prompts page lazy loaded |

---

## 🔄 Business Rules Implemented

| Rule ID | Rule Description | Implementation |
|---------|------------------|----------------|
| BR-P01 | System prompts are read-only | `is_system` check prevents editing |
| BR-P02 | System prompts can be cloned | Clone creates user-owned copy |
| BR-P03 | User can only access own prompts + system prompts | `accessibleBy()` scope |
| BR-P04 | Each prompt has unique sort_order per user | Auto-increment di service |
| BR-P05 | Deleting folder sets prompt.folder_id to NULL | `nullOnDelete()` constraint |
| BR-P06 | Inactive prompts excluded from listings | `active()` scope |
| BR-P07 | Usage count tracked automatically | `incrementUsage()` method |

---

## 📚 Related Documentation

- **API Reference:** [Prompts API](../04-api-reference/prompts.md)
- **Testing Guide:** [Prompts Testing](../06-testing/prompts-testing.md)
- **User Journeys:** [Prompts User Journeys](../07-user-journeys/prompts/)
- **EPIC:** [EPIC-05: Prompts System](../../scrum/epic-planning/05-EPIC-prompts-system.md)

---

## 🚀 Future Enhancements (Not in This Sprint)

### FG-05.2: Prompt Editor Advanced Features
- [ ] Prompt variables support (`{variable_name}`)
- [ ] Prompt preview functionality
- [ ] Syntax highlighting untuk messages
- [ ] Template snippets

### FG-05.3: Personas & Presets
- [ ] Prompt personas untuk AI personality
- [ ] Prompt presets untuk quick setup
- [ ] Preset templates

### FG-05.4: Advanced Features
- [ ] Prompt components (reusable blocks)
- [ ] Prompt inputs (dynamic form fields)
- [ ] Conditional logic dalam prompts

### FG-05.5: Sharing & Organization
- [ ] Folder UI implementation
- [ ] Drag-and-drop organization
- [ ] Share prompts with team
- [ ] Import/export prompts
- [ ] Prompt marketplace

---

## 📝 Changelog

| Version | Date | Changes |
|---------|------|---------|
| 1.0.0 | 2026-01-03 | Initial implementation: CRUD, types, workspace integration |

---

## ✅ Verification Checklist (Completed)

### Pre-Documentation Verification

- [x] Routes verified: `php artisan route:list --path=prompts`
- [x] Service methods match Controller calls
- [x] Tested with `php artisan test --filter=PromptTest`
- [x] Vue pages exist for Inertia renders
- [x] Migrations applied: `php artisan migrate:status`
- [x] Following DOCUMENTATION_GUIDE.md template

### Feature Completion

- [x] All CRUD endpoints working
- [x] Authorization rules enforced
- [x] Frontend UI implemented
- [x] Tests passing (18/18)
- [x] Workspace integration complete
- [x] Seeder dengan sample system prompts
- [x] Factory dengan useful states

---

*Last Updated: 2026-01-03*
