---
name: Foundation Epic Strategy
overview: Comprehensive cross-frontend implementation strategy for Epic 1 (Foundation & Core Setup) covering user authentication, dashboard, novel creation, and onboarding - the entry point for the AI-Assisted Novel Writing App.
todos:
  - id: auth-backend
    content: Create auth controllers, routes, and middleware setup
    status: pending
  - id: auth-frontend
    content: Build Register and Login Vue pages with useForm
    status: pending
  - id: novel-model
    content: Create Novel model, migration, factory, and relationships
    status: pending
  - id: penname-model
    content: Create PenName model, migration, factory (Sprint 7)
    status: pending
  - id: dashboard-backend
    content: Build DashboardController with novel listing logic
    status: pending
  - id: dashboard-frontend
    content: Create Dashboard page with NovelCard grid and empty state
    status: pending
  - id: novel-create-backend
    content: Build NovelController with store action and validation
    status: pending
  - id: novel-create-frontend
    content: Build multi-step creation wizard with all form fields
    status: pending
  - id: profile-page
    content: Create profile settings page with edit/password forms
    status: pending
  - id: onboarding-state
    content: Create UserOnboardingState tracking system
    status: pending
  - id: onboarding-ui
    content: Build onboarding welcome and checklist components
    status: pending
---

# Foundation & Core Setup - Development Strategy

## Current State Analysis

The project is a fresh Laravel 12 + Vue 3 + Inertia v2 setup with:

- Only a `Welcome.vue` page exists
- Default `users` table with basic auth columns
- No routes beyond home
- No models beyond User
- SQLite database

---

## Phase 1: Cross-Frontend Impact Mapping

| Feature | Owner (Creates) | Consumer (Views) | Data Flow |

|---------|-----------------|------------------|-----------|

| **US-072: User Account** | Auth Pages (Register/Login) | All authenticated pages | User -> DB -> Session -> All Pages |

| **US-065: Dashboard** | System (auto-generated) | Dashboard Page | Novels -> Controller -> Dashboard Grid |

| **US-066: Novel Creation** | Novel Creation Wizard | Dashboard, Editor | Form -> API -> DB -> Dashboard List |

| **US-067: Pen Names** | Settings/Modal | Dashboard filter, Novel card | Form -> DB -> Dashboard/Novel |

| **US-079: Novel Covers** | Novel Settings | Dashboard, Novel header | Upload -> Storage -> Display |

| **US-080: Templates** | "Save as Template" action | Novel creation wizard | Novel -> Template -> New Novel |

| **US-111: Onboarding** | System (first-time detection) | Welcome flow, Checklist | State tracking -> Progressive UI |

---

## Phase 2: Missing Implementation Detection

### US-072: User Account & Profile

**Owner Side:**

- [ ] Registration form (email, password, name)
- [ ] Login form
- [ ] Profile settings page
- [ ] Change password form
- [ ] Delete account confirmation

**Consumer Side:**

- [ ] Auth state in navbar/header
- [ ] Protected route middleware
- [ ] Avatar display component
- [ ] Session management UI

**Gaps Identified:**

- No social login (Google) UI specified - marked optional
- No email verification flow UI
- No "forgot password" flow mentioned

### US-065: Dashboard & Project List

**Owner Side:**

- [ ] Novel card component (display only)
- [ ] Sort/filter controls
- [ ] Search input

**Consumer Side:**

- [ ] Grid/List view toggle
- [ ] Empty state (no novels yet)
- [ ] Loading skeleton
- [ ] Quick stats section
- [ ] Mobile-responsive grid

**Gaps Identified:**

- No pagination specification for large novel counts
- No archive/trash view mentioned

### US-066: Novel Creation & Setup

**Owner Side:**

- [ ] Multi-step wizard component
- [ ] Title input with validation
- [ ] Genre selector
- [ ] POV/Tense selectors
- [ ] Cover upload zone
- [ ] Template preview modal

**Consumer Side:**

- [ ] Created novel appears in dashboard
- [ ] Navigation to editor after creation

**Gaps Identified:**

- No draft/unsaved wizard state handling
- No "create another" flow after completion

### US-111: Onboarding Experience

**Owner Side:**

- [ ] System tracks onboarding state per user
- [ ] "Skip" and "Reset" actions

**Consumer Side:**

- [ ] Welcome modal/page
- [ ] Interactive tour overlay
- [ ] Checklist widget on dashboard
- [ ] Progress indicators

**Gaps Identified:**

- No specification for tour library (recommend intro.js or vue-tour)
- No video tutorial integration details
- Mobile onboarding flow needs special attention

---

## Phase 3: Data Model Requirements

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│    User     │────<│   Novel     │────<│   PenName   │
└─────────────┘     └─────────────┘     └─────────────┘
       │                   │
       │                   └────<┌─────────────────┐
       │                         │  NovelTemplate  │
       │                         └─────────────────┘
       │
       └────<┌─────────────────────┐
             │  UserOnboardingState │
             └─────────────────────┘
```

**New Tables Required:**

- `novels` - Core novel data
- `pen_names` - Author identities
- `novel_templates` - Saved structures
- `user_onboarding_states` - Onboarding progress tracking

---

## Phase 4: Priority Matrix

### P0 - Critical (Blocks everything)

| Item | Reason |

|------|--------|

| User Registration/Login | All features require auth |

| Dashboard page route | Entry point after login |

| Novel model + migration | Core data entity |

| Basic novel creation form | Users need to create content |

### P1 - Important (Feature incomplete without)

| Item | Reason |

|------|--------|

| Profile settings | Users expect account management |

| Dashboard grid view | Main content display |

| Novel deletion | Users need data control |

| Search/sort novels | Usability for multiple novels |

| Onboarding welcome screen | First-time user experience |

### P2 - Enhancement (Can ship later)

| Item | Reason |

|------|--------|

| Pen names | Nice-to-have organization |

| Novel covers | Visual enhancement |

| Templates | Power user feature |

| Interactive tour | Onboarding polish |

| Social login | Alternative auth method |

---

## Phase 5: Implementation Sequence

### Sprint 1 Week 1: Authentication Foundation

```
Day 1-2: Database & Models
├── Novel migration
├── PenName migration  
├── UserOnboardingState migration
└── Model relationships

Day 3-4: Auth System
├── Registration page + controller
├── Login page + controller
├── Logout functionality
└── Auth middleware setup

Day 5: Profile Basics
├── Profile settings page
└── Change password
```

### Sprint 1 Week 2: Dashboard & Novel Creation

```
Day 1-2: Dashboard
├── Dashboard page component
├── Novel card component
├── Empty state component
└── Dashboard controller

Day 3-4: Novel Creation
├── Creation wizard component
├── Step components (Title, Genre, POV, Tense)
├── NovelController store action
└── Form validation

Day 5: Polish
├── Sort/filter implementation
├── Search functionality
└── Mobile responsive testing
```

---

## Phase 6: New Pages/Routes Needed

| Route | Page | Purpose | Priority |

|-------|------|---------|----------|

| `/register` | `Auth/Register.vue` | User signup | P0 |

| `/login` | `Auth/Login.vue` | User login | P0 |

| `/dashboard` | `Dashboard/Index.vue` | Main hub | P0 |

| `/novels/create` | `Novels/Create.vue` | Novel wizard | P0 |

| `/profile` | `Profile/Edit.vue` | Account settings | P1 |

| `/onboarding` | `Onboarding/Welcome.vue` | First-time flow | P1 |

---

## Phase 7: Component Library

| Component | Used By | Priority |

|-----------|---------|----------|

| `NovelCard.vue` | Dashboard | P0 |

| `EmptyState.vue` | Dashboard, any list | P0 |

| `WizardStep.vue` | Novel creation, onboarding | P0 |

| `SearchInput.vue` | Dashboard, future search | P1 |

| `ImageUpload.vue` | Novel cover, profile avatar | P1 |

| `TourOverlay.vue` | Onboarding | P2 |

| `ChecklistWidget.vue` | Dashboard onboarding | P2 |

---

## Phase 8: Key User Journeys

### Journey 1: New User Registration

1. User lands on `/` (Welcome page)
2. User clicks "Get Started" or "Sign Up"
3. User navigates to `/register`
4. User fills: name, email, password, confirm password
5. System validates and creates account
6. System redirects to `/onboarding` (first-time) or `/dashboard`
7. User sees welcome message and onboarding prompt

### Journey 2: Create First Novel

1. User is on `/dashboard`
2. User clicks "Create Novel" button
3. User navigates to `/novels/create`
4. User enters title (required)
5. User selects pen name (or creates new)
6. User selects genre, POV, tense
7. User optionally uploads cover
8. User clicks "Create"
9. System creates novel
10. User redirected to dashboard with new novel visible

### Journey 3: Dashboard Exploration

1. User lands on `/dashboard`
2. User sees grid of novel cards (or empty state if none)
3. User can search by title
4. User can sort: recent, alphabetical, word count
5. User clicks novel card to open editor (future)
6. User sees quick stats: total words, total novels

---

## Backend Implementation Checklist

**Controllers:**

- [ ] `Auth/RegisteredUserController.php`
- [ ] `Auth/AuthenticatedSessionController.php`
- [ ] `DashboardController.php`
- [ ] `NovelController.php`
- [ ] `PenNameController.php`
- [ ] `ProfileController.php`
- [ ] `OnboardingController.php`

**Form Requests:**

- [ ] `StoreNovelRequest.php`
- [ ] `UpdateNovelRequest.php`
- [ ] `StorePenNameRequest.php`

**Models:**

- [ ] `Novel.php` with relationships
- [ ] `PenName.php` with relationships
- [ ] Update `User.php` with relationships

---

## Design Considerations (per SKILL.md)

- Mobile-first hierarchy for all pages
- Spring physics animations using motion-v
- iOS-like press feedback (0.97 scale on tap)
- Glass effect for navbar with backdrop blur
- Staggered entrance animations for novel card grid
- Pull-to-refresh on dashboard (mobile)

---

## Risk Mitigation

| Risk | Mitigation |

|------|------------|

| Onboarding complexity | Start with simple checklist, add tour later |

| Cover upload performance | Use client-side compression before upload |

| Dashboard performance with many novels | Implement pagination early |

| Template feature scope | Defer to Sprint 7 as planned |
