# 🏗️ Sprint 01 - Foundation Epic

## Overview

Sprint Foundation untuk NovelWrite, yaitu: implementasi core features yang menjadi dasar aplikasi termasuk authentication, dashboard, novel management, profile settings, dan onboarding system.

---

## Sprint Info

| Property | Value |
|----------|-------|
| **Sprint Name** | Foundation Epic |
| **Status** | ✅ Complete |
| **Start Date** | 2025-12-31 |
| **End Date** | 2025-12-31 |
| **Total Story Points** | 21 |

---

## User Stories Completed

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

---

## Technical Implementation

### Backend Files

| Category | Files |
|----------|-------|
| **Controllers** | `AuthenticatedSessionController.php`, `RegisteredUserController.php`, `DashboardController.php`, `NovelController.php`, `ProfileController.php`, `OnboardingController.php` |
| **Models** | `User.php`, `Novel.php`, `PenName.php`, `UserOnboardingState.php` |
| **Form Requests** | `LoginRequest.php`, `RegisterRequest.php`, `StoreNovelRequest.php`, `UpdateProfileRequest.php` |
| **Migrations** | `create_users_table.php`, `create_pen_names_table.php`, `create_novels_table.php`, `create_user_onboarding_states_table.php` |
| **Factories** | `UserFactory.php`, `NovelFactory.php`, `PenNameFactory.php` |

### Frontend Files

| Category | Files |
|----------|-------|
| **Layouts** | `AuthenticatedLayout.vue`, `GuestLayout.vue` |
| **Pages** | `Welcome.vue`, `Login.vue`, `Register.vue`, `Dashboard/Index.vue`, `Novels/Create.vue`, `Profile/Edit.vue` |
| **UI Components** | `Button.vue`, `Input.vue`, `Card.vue` |
| **Dashboard Components** | `NovelCard.vue`, `StatsCard.vue`, `EmptyState.vue`, `OnboardingChecklist.vue` |

---

## Routes Summary

| Method | URI | Name | Controller |
|--------|-----|------|------------|
| GET | `/` | home | Closure (Welcome) |
| GET | `/register` | register | RegisteredUserController@create |
| POST | `/register` | - | RegisteredUserController@store |
| GET | `/login` | login | AuthenticatedSessionController@create |
| POST | `/login` | - | AuthenticatedSessionController@store |
| POST | `/logout` | logout | AuthenticatedSessionController@destroy |
| GET | `/dashboard` | dashboard | DashboardController |
| GET | `/novels/create` | novels.create | NovelController@create |
| POST | `/novels` | novels.store | NovelController@store |
| DELETE | `/novels/{novel}` | novels.destroy | NovelController@destroy |
| GET | `/profile` | profile.edit | ProfileController@edit |
| PATCH | `/profile` | profile.update | ProfileController@update |
| PUT | `/profile/password` | profile.password | ProfileController@updatePassword |
| DELETE | `/profile` | profile.destroy | ProfileController@destroy |
| POST | `/onboarding/welcome` | onboarding.welcome | OnboardingController@completeWelcome |
| POST | `/onboarding/skip` | onboarding.skip | OnboardingController@skip |

---

## Database Schema

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

---

## Key Design Decisions

### 1. Inertia.js untuk SPA-like Experience
- Single Page Application feel tanpa kompleksitas full SPA
- Server-side routing dengan client-side rendering
- Seamless page transitions dengan motion-v

### 2. Tailwind CSS v4 untuk Styling
- Utility-first CSS framework
- Dark mode support dengan `dark:` prefix
- Konsisten design system

### 3. Motion-V untuk Animations
- Vue 3 compatible animation library
- Spring-based animations untuk feel natural
- Staggered animations untuk list items

### 4. Session-based Authentication
- Laravel built-in session auth
- Remember me functionality
- Secure password hashing dengan bcrypt

---

## Verification Commands

```bash
# Check routes
php artisan route:list

# Check migrations
php artisan migrate:status

# Run tests
php artisan test

# Lint PHP
vendor/bin/pint --dirty

# Lint JS
yarn run lint

# Build frontend
yarn build
```

---

## Related Documentation

- **API Reference:** [Authentication](../04-api-reference/authentication.md) | [Novels](../04-api-reference/novels.md) | [Profile](../04-api-reference/profile.md)
- **Testing Guide:** [Foundation Testing](../06-testing/foundation-testing.md)
- **User Journeys:** [Authentication Flow](../07-user-journeys/authentication/user-auth-flow.md)

---

**Last Updated:** 2025-12-31
