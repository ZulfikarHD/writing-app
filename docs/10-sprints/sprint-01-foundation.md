# 🏗️ Sprint 01 - Foundation & Core Editor

## Overview

Sprint Foundation untuk NovelWrite, yaitu: implementasi core features yang menjadi dasar aplikasi termasuk authentication, dashboard, novel management, rich text editor dengan formatting lengkap, scene/chapter structure dengan drag-and-drop reordering, editor settings panel untuk kustomisasi tampilan, dan theme/display options untuk pengalaman menulis yang optimal dengan mobile-responsive design.

---

## Sprint Info

| Property | Value |
|----------|-------|
| **Sprint Name** | Foundation & Core Editor |
| **Status** | ✅ Complete |
| **Start Date** | 2025-12-31 |
| **End Date** | 2026-01-01 |
| **Total Story Points** | 36 |

---

## User Stories Completed

### Foundation Features (21 points)

| ID | Story | Points | Status |
|----|-------|--------|--------|
| F-001 | User dapat register account baru | 3 | ✅ Done |
| F-002 | User dapat login ke account | 2 | ✅ Done |
| F-003 | User dapat logout dari account | 1 | ✅ Done |
| F-004 | User dapat melihat dashboard dengan stats | 3 | ✅ Done |
| F-005 | User dapat membuat novel baru | 3 | ✅ Done |
| F-006 | User dapat melihat daftar novel di dashboard | 2 | ✅ Done |
| F-007 | User dapat menghapus novel | 2 | ✅ Done |
| F-008 | User dapat update profile information | 2 | ✅ Done |
| F-009 | User dapat update password | 2 | ✅ Done |
| F-010 | User dapat delete account | 1 | ✅ Done |

### Editor Features (15 points)

| ID | Story | Points | Status |
|----|-------|--------|--------|
| US-001 | Rich Text Editor dengan formatting lengkap | 8 | ✅ Done |
| US-002 | Scene & Chapter Structure dengan drag-drop | 8 | ✅ Done |
| US-009 | Theme & Display Options | 5 | ✅ Done |

---

## ✨ Features Implemented

### 1. Rich Text Editor
- **TipTap editor** dengan formatting dasar: bold, italic, underline, strikethrough
- **Heading levels**: H1, H2, H3 via dropdown
- **Lists**: Bullet list dan numbered list
- **Text alignment**: Left, center, right, justify
- **Auto-save**: Debounced 500ms untuk efisiensi
- **Word count**: Real-time counting dan display
- **Undo/Redo**: Command pattern dengan history depth 100

### 2. Scene & Chapter Management
- **Hierarchical structure**: Novel → Chapter → Scene
- **Drag & drop reordering**: Chapter dan scene dengan VueDraggable
- **Scene creation**: Inline creation dengan enter/escape handling
- **Chapter creation**: Modal dengan validation
- **Expandable sidebar**: Collapse/expand chapters
- **Status indicators**: Visual status untuk scenes (draft, in_progress, completed, needs_revision)
- **Active highlight**: Scene yang sedang active ter-highlight di sidebar

### 3. Editor Settings Panel
- **Slide-over panel** dengan spring animations (motion-v)
- **Font Family**: Serif, Sans-serif, Monospace, Dyslexia-friendly (OpenDyslexic)
- **Font Size**: 14-24px dengan 6 pilihan
- **Line Height**: 1.5-2.0 dengan 4 pilihan
- **Editor Width**: Narrow (640px), Medium (768px), Wide (896px)
- **Theme Toggle**: Light, Dark, System preference
- **Reset to Defaults**: One-click reset semua settings
- **LocalStorage persistence**: Settings tersimpan otomatis

### 4. Mobile Responsiveness
- **iOS-like animations**: Spring physics untuk natural feel
- **Press feedback**: Scale-down effect (0.95 dan 0.98) pada buttons
- **Responsive toolbar**: Wrap pada layar kecil, full layout di desktop
- **Touch-friendly**: Minimum tap target size untuk mobile
- **Full-width settings**: Panel full screen pada mobile, slide-over di desktop

---

## 📁 File Structure

### Backend Files

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   │   ├── AuthenticatedSessionController.php     ✅ Complete
│   │   │   └── RegisteredUserController.php           ✅ Complete
│   │   ├── ChapterController.php                      ✨ NEW
│   │   ├── Controller.php                             ✅ Complete
│   │   ├── DashboardController.php                    ✅ Complete
│   │   ├── EditorController.php                       ✨ NEW
│   │   ├── NovelController.php                        ✅ Complete
│   │   ├── OnboardingController.php                   ✅ Complete
│   │   ├── ProfileController.php                      ✅ Complete
│   │   └── SceneController.php                        ✨ NEW
│   ├── Middleware/
│   │   └── HandleInertiaRequests.php                  ✅ Complete
│   └── Requests/
│       ├── Auth/
│       │   ├── LoginRequest.php                       ✅ Complete
│       │   └── RegisterRequest.php                    ✅ Complete
│       ├── StoreChapterRequest.php                    ✨ NEW
│       ├── StoreNovelRequest.php                      ✅ Complete
│       ├── UpdateProfileRequest.php                   ✅ Complete
│       └── UpdateSceneContentRequest.php              ✨ NEW
├── Models/
│   ├── Chapter.php                                    ✨ NEW
│   ├── Novel.php                                      ✅ Complete
│   ├── PenName.php                                    ✅ Complete
│   ├── Scene.php                                      ✨ NEW
│   ├── SceneRevision.php                              ✨ NEW
│   ├── User.php                                       ✅ Complete
│   └── UserOnboardingState.php                        ✅ Complete
```

### Frontend Files

```
resources/js/
├── components/
│   ├── dashboard/
│   │   ├── EmptyState.vue                            ✅ Complete
│   │   ├── NovelCard.vue                             ✅ Complete
│   │   ├── OnboardingChecklist.vue                   ✅ Complete
│   │   └── StatsCard.vue                             ✅ Complete
│   ├── editor/
│   │   ├── EditorSidebar.vue                         ✏️ ENHANCED (drag-drop)
│   │   ├── EditorToolbar.vue                         ✏️ ENHANCED (formatting tools)
│   │   ├── EditorSettingsPanel.vue                   ✨ NEW
│   │   └── TipTapEditor.vue                          ✏️ ENHANCED (extensions)
│   └── ui/
│       ├── Button.vue                                 ✅ Complete
│       ├── Card.vue                                   ✅ Complete
│       └── Input.vue                                  ✅ Complete
├── composables/
│   ├── useAutoSave.ts                                 ✨ NEW
│   ├── useEditorSettings.ts                          ✨ NEW
│   └── useTheme.ts                                    ✅ Complete
├── layouts/
│   ├── AuthenticatedLayout.vue                        ✅ Complete
│   └── GuestLayout.vue                                ✅ Complete
└── pages/
    ├── Auth/
    │   ├── Login.vue                                  ✅ Complete
    │   └── Register.vue                               ✅ Complete
    ├── Dashboard/
    │   └── Index.vue                                  ✅ Complete
    ├── Editor/
    │   └── Index.vue                                  ✏️ ENHANCED (settings integration)
    ├── Novels/
    │   └── Create.vue                                 ✅ Complete
    ├── Profile/
    │   └── Edit.vue                                   ✅ Complete
    └── Welcome.vue                                    ✅ Complete
```

### Database Migrations

```
database/migrations/
├── 0001_01_01_000000_create_users_table.php          ✅ Complete
├── 0001_01_01_000001_create_cache_table.php          ✅ Complete
├── 0001_01_01_000002_create_jobs_table.php           ✅ Complete
├── 2025_12_31_071018_create_pen_names_table.php      ✅ Complete
├── 2025_12_31_071019_create_novels_table.php         ✅ Complete
├── 2025_12_31_071021_create_user_onboarding_states_table.php  ✅ Complete
├── 2025_12_31_081814_create_chapters_table.php       ✨ NEW
├── 2025_12_31_081815_create_scene_revisions_table.php  ✨ NEW
└── 2025_12_31_081815_create_scenes_table.php         ✨ NEW
```

---

## 🔌 API Endpoints Summary

### Foundation Routes

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/` | home | Welcome page |
| GET | `/register` | register | Registration form |
| POST | `/register` | - | Create user |
| GET | `/login` | login | Login form |
| POST | `/login` | - | Authenticate |
| POST | `/logout` | logout | Logout user |
| GET | `/dashboard` | dashboard | User dashboard |
| GET | `/profile` | profile.edit | Profile settings |
| PATCH | `/profile` | profile.update | Update profile |
| PUT | `/profile/password` | profile.password | Change password |
| DELETE | `/profile` | profile.destroy | Delete account |

### Novel Management

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/novels/create` | novels.create | Novel creation form |
| POST | `/novels` | novels.store | Create novel |
| DELETE | `/novels/{novel}` | novels.destroy | Delete novel |

### Editor Routes

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/novels/{novel}/write` | editor.show | Open editor |
| GET | `/novels/{novel}/write/{scene}` | editor.scene | Open specific scene |

### Chapter API

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `api/novels/{novel}/chapters` | chapters.index | List chapters |
| POST | `api/novels/{novel}/chapters` | chapters.store | Create chapter |
| PATCH | `api/chapters/{chapter}` | chapters.update | Update chapter |
| DELETE | `api/chapters/{chapter}` | chapters.destroy | Delete chapter |
| POST | `api/novels/{novel}/chapters/reorder` | chapters.reorder | Reorder chapters |

### Scene API

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| POST | `api/chapters/{chapter}/scenes` | scenes.store | Create scene |
| GET | `api/scenes/{scene}` | scenes.show | Get scene |
| PATCH | `api/scenes/{scene}` | scenes.update | Update metadata |
| PATCH | `api/scenes/{scene}/content` | scenes.content | Auto-save content |
| DELETE | `api/scenes/{scene}` | scenes.destroy | Delete scene |
| POST | `api/scenes/{scene}/archive` | scenes.archive | Archive scene |
| POST | `api/scenes/{scene}/restore` | scenes.restore | Restore archived |
| POST | `api/chapters/{chapter}/scenes/reorder` | scenes.reorder | Reorder scenes |

### Scene Revisions

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `api/scenes/{scene}/revisions` | scenes.revisions | List revisions |
| POST | `api/scenes/{scene}/revisions` | scenes.revisions.create | Create revision |
| POST | `api/scenes/{scene}/revisions/{id}/restore` | scenes.revisions.restore | Restore revision |

> 📡 **Full API Documentation:** [Manuscript Editor API](../04-api-reference/manuscript-editor.md)

---

## 🗄️ Database Schema

### users

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint | PK, AI |
| name | varchar(255) | NOT NULL |
| email | varchar(255) | UNIQUE, NOT NULL |
| password | varchar(255) | NOT NULL |
| remember_token | varchar(100) | NULLABLE |
| timestamps | - | created_at, updated_at |

### novels

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint | PK, AI |
| user_id | bigint | FK → users.id |
| pen_name_id | bigint | FK → pen_names.id, NULLABLE |
| title | varchar(255) | NOT NULL |
| description | text | NULLABLE |
| genre | varchar(100) | NULLABLE |
| word_count | integer | DEFAULT 0 |
| chapter_count | integer | DEFAULT 0 |
| target_word_count | integer | NULLABLE |
| status | enum | draft, in_progress, completed, archived |
| last_edited_at | timestamp | NULLABLE |
| timestamps | - | created_at, updated_at |

### pen_names

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint | PK, AI |
| user_id | bigint | FK → users.id |
| name | varchar(255) | NOT NULL |
| bio | text | NULLABLE |
| timestamps | - | created_at, updated_at |

### user_onboarding_states

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint | PK, AI |
| user_id | bigint | FK → users.id, UNIQUE |
| welcome_completed | boolean | DEFAULT false |
| first_novel_created | boolean | DEFAULT false |
| editor_toured | boolean | DEFAULT false |
| onboarding_skipped | boolean | DEFAULT false |
| timestamps | - | created_at, updated_at |

### chapters ✨ NEW

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint | PK, AI |
| novel_id | bigint | FK → novels.id |
| title | varchar(255) | NOT NULL |
| position | integer | NOT NULL, DEFAULT 0 |
| settings | json | NULLABLE |
| timestamps | - | created_at, updated_at |

### scenes ✨ NEW

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint | PK, AI |
| chapter_id | bigint | FK → chapters.id |
| title | varchar(255) | NULLABLE |
| content | json | NULLABLE (TipTap JSON) |
| summary | text | NULLABLE |
| position | integer | NOT NULL, DEFAULT 0 |
| pov_character_id | bigint | NULLABLE |
| status | varchar(50) | DEFAULT 'draft' |
| word_count | integer | DEFAULT 0 |
| subtitle | varchar(255) | NULLABLE |
| notes | text | NULLABLE |
| exclude_from_ai | boolean | DEFAULT false |
| metadata | json | NULLABLE |
| archived_at | timestamp | NULLABLE (soft delete) |
| timestamps | - | created_at, updated_at |

### scene_revisions ✨ NEW

| Column | Type | Constraints |
|--------|------|-------------|
| id | bigint | PK, AI |
| scene_id | bigint | FK → scenes.id |
| content | json | NOT NULL |
| word_count | integer | DEFAULT 0 |
| created_at | timestamp | NOT NULL |

---

## 🎨 Key Design Decisions

### 1. Inertia.js untuk SPA-like Experience
- Single Page Application feel tanpa kompleksitas full SPA
- Server-side routing dengan client-side rendering
- Seamless page transitions dengan motion-v
- Auto-reload on navigation errors

### 2. Tailwind CSS v4 untuk Styling
- Utility-first CSS framework
- Dark mode support dengan `dark:` prefix
- Konsisten design system
- CSS variables untuk editor customization

### 3. Motion-V untuk Animations
- Vue 3 compatible animation library
- Spring-based animations untuk feel natural (iOS-like)
- Staggered animations untuk list items
- Press feedback dengan active:scale-* classes

### 4. TipTap (ProseMirror) untuk Rich Text
- Extensible editor framework
- JSON content format untuk flexibility
- Custom extensions support
- Headless UI untuk full control

### 5. VueDraggable untuk Reordering
- Sortable.js wrapper untuk Vue 3
- Touch support untuk mobile
- Smooth animations
- Ghost class untuk visual feedback

### 6. Session-based Authentication
- Laravel built-in session auth
- Remember me functionality
- Secure password hashing dengan bcrypt
- CSRF protection

### 7. Auto-save Strategy
- Debounced 500ms untuk mengurangi API calls
- Force save dengan Ctrl+S / Cmd+S
- Save status indicator (saved/saving/unsaved/error)
- Word count update on save

### 8. LocalStorage untuk Editor Preferences
- Instant settings apply
- No server roundtrip untuk better UX
- Optional sync ke server (future enhancement)
- Default values fallback

---

## 🧪 Testing

### Test Coverage

| Category | Tests | Assertions |
|----------|-------|------------|
| Editor Tests | 19 | 137 |
| Chapter Tests | 11 | - |
| Scene Tests | 16 | - |
| Scene Revision Tests | 12 | - |
| **Total** | **58** | **282+** |

### Key Test Cases

**Editor Feature Tests:**
- ✅ User can access editor
- ✅ Editor loads novel data
- ✅ Editor creates default chapter/scene if none exist
- ✅ User can reorder chapters
- ✅ User can reorder scenes within chapter
- ✅ User can create chapter
- ✅ User can create scene
- ✅ User can update scene content
- ✅ Authorization checks (cannot access other users' content)

> 📋 **Full Test Plan:** [Manuscript Editor Testing](../06-testing/manuscript-editor-testing.md)

---

## 💡 Technical Highlights

### 1. CSS Variables untuk Dynamic Theming
```css
:root {
    --editor-font-family: 'Georgia', 'Times New Roman', serif;
    --editor-font-size: 18px;
    --editor-line-height: 1.8;
}
```

### 2. Composable Pattern untuk State Management
```typescript
// useEditorSettings.ts - Singleton pattern
const settings = ref<EditorSettings>({ ...defaultSettings });
// Shared across all components
```

### 3. Auto-save dengan Debounce
```typescript
const { saveStatus, triggerSave, forceSave } = useAutoSave({
    sceneId: props.activeScene?.id || 0,
    debounceMs: 500,
    onSaved: (newWordCount) => { wordCount.value = newWordCount; },
});
```

### 4. Drag & Drop dengan Position Updates
```typescript
// Update positions di database setelah drag
const scenes = chapter.scenes.map((s, index) => ({
    id: s.id,
    position: index,
}));
await axios.post('/api/chapters/' + chapterId + '/scenes/reorder', { scenes });
```

---

## 📦 Dependencies Added

### Frontend
- `vuedraggable@4.1.0` - Drag and drop functionality
- `@tiptap/extension-text-align@3.14.0` - Text alignment support

### Fonts (CDN)
- `Inter` - Sans-serif option
- `JetBrains Mono` - Monospace option
- `OpenDyslexic` - Dyslexia-friendly option

---

## ✅ Verification Commands

```bash
# Check routes
php artisan route:list --path=api/chapters
php artisan route:list --path=api/scenes

# Check migrations
php artisan migrate:status | grep -E "chapters|scenes"

# Run tests
php artisan test --filter=EditorTest
php artisan test --filter=ChapterTest
php artisan test --filter=SceneTest

# Lint PHP
vendor/bin/pint --dirty

# Lint JS
yarn run lint

# Build frontend
yarn run build

# All tests
php artisan test
```

**Verification Results:**
- ✅ All 59 tests passing (282 assertions)
- ✅ Lint passes (ESLint & Pint)
- ✅ Build succeeds with no errors
- ✅ All routes accessible

---

## 🔗 Related Documentation

- **API Reference:** 
  - [Authentication](../04-api-reference/authentication.md)
  - [Novels](../04-api-reference/novels.md)
  - [Profile](../04-api-reference/profile.md)
  - [Manuscript Editor](../04-api-reference/manuscript-editor.md) ✨ NEW
- **Testing Guide:** 
  - [Foundation Testing](../06-testing/foundation-testing.md)
  - [Manuscript Editor Testing](../06-testing/manuscript-editor-testing.md) ✨ NEW
- **User Journeys:** 
  - [Authentication Flow](../07-user-journeys/authentication/user-auth-flow.md)

---

## 📝 Sprint Retrospective

### What Went Well ✅
- TipTap integration smooth dengan custom extensions
- VueDraggable bekerja perfect dengan Vue 3 Composition API
- Motion-v memberikan iOS-like feel yang diinginkan
- Auto-save strategy efektif dengan debounce 500ms
- All tests passing setelah implementation

### Challenges Faced ⚠️
- CSS import order warning (resolved: fonts harus di-import pertama)
- EditorToolbar props mismatch (resolved: added new props for formatting)
- Reorder API format (resolved: array of {id, position} objects)

### Future Improvements 🚀
- Code splitting untuk mengurangi bundle size (570KB chunk)
- Server-side settings sync (currently localStorage only)
- Keyboard shortcuts untuk formatting (Ctrl+B works, add more)
- Offline support dengan IndexedDB cache
- Real-time collaboration preparation

---

**Last Updated:** 2026-01-01
