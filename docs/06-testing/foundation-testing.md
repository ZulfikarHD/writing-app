# 🧪 Foundation Epic - Testing Guide

## Overview

Testing guide untuk Foundation Epic NovelWrite, yaitu: test plan komprehensif yang mencakup authentication, dashboard, novel management, profile settings, dan onboarding.

---

## Test Categories

| Category | Type | Coverage |
|----------|------|----------|
| Unit Tests | PHPUnit | Models, Services |
| Feature Tests | PHPUnit | Controllers, HTTP |
| Component Tests | Manual/E2E | Vue Components |
| Integration Tests | PHPUnit | Full flow |

---

## 🔐 Authentication Tests

### Register Flow

| Test Case | Expected Result | Priority |
|-----------|-----------------|----------|
| User dapat register dengan data valid | Redirect ke dashboard, user tersimpan | 🔴 High |
| Register dengan email duplicate | Error 422, message "email taken" | 🔴 High |
| Register dengan password < 8 char | Error 422, validation failed | 🟡 Medium |
| Register tanpa password confirmation | Error 422, passwords must match | 🟡 Medium |
| Register dengan name kosong | Error 422, name required | 🟡 Medium |

### Login Flow

| Test Case | Expected Result | Priority |
|-----------|-----------------|----------|
| User dapat login dengan kredensial valid | Redirect ke dashboard | 🔴 High |
| Login dengan email tidak terdaftar | Error 422, credentials invalid | 🔴 High |
| Login dengan password salah | Error 422, credentials invalid | 🔴 High |
| Login dengan remember me | Persistent session created | 🟡 Medium |

### Logout Flow

| Test Case | Expected Result | Priority |
|-----------|-----------------|----------|
| User dapat logout | Redirect ke home, session cleared | 🔴 High |
| Logout clears all session data | No auth data remains | 🟡 Medium |

---

## 📊 Dashboard Tests

### Dashboard Display

| Test Case | Expected Result | Priority |
|-----------|-----------------|----------|
| Dashboard menampilkan stats yang benar | Stats sesuai data user | 🔴 High |
| Dashboard menampilkan novel cards | Cards untuk setiap novel | 🔴 High |
| Empty state saat tidak ada novel | EmptyState component displayed | 🟡 Medium |
| Onboarding checklist untuk user baru | Checklist visible if not completed | 🟡 Medium |

### Stats Accuracy

| Test Case | Expected Result | Priority |
|-----------|-----------------|----------|
| Total novels count benar | Count sesuai jumlah novel user | 🟡 Medium |
| Total words count benar | Sum dari semua word_count | 🟡 Medium |
| In progress count benar | Count novels dengan status in_progress | 🟡 Medium |
| Completed count benar | Count novels dengan status completed | 🟡 Medium |

---

## 📖 Novel Management Tests

### Create Novel

| Test Case | Expected Result | Priority |
|-----------|-----------------|----------|
| User dapat create novel dengan data valid | Novel tersimpan, redirect dashboard | 🔴 High |
| Create novel dengan title kosong | Error 422, title required | 🔴 High |
| Create novel dengan pen name | Novel linked ke pen name | 🟡 Medium |
| Create novel tanpa description | Novel tersimpan dengan description null | 🟢 Low |
| Onboarding state updated setelah create | first_novel_created = true | 🟡 Medium |

### Delete Novel

| Test Case | Expected Result | Priority |
|-----------|-----------------|----------|
| User dapat delete novel miliknya | Novel deleted, redirect dashboard | 🔴 High |
| User tidak bisa delete novel orang lain | Error 403 forbidden | 🔴 High |
| Confirmation sebelum delete | Modal muncul dengan konfirmasi | 🟡 Medium |

---

## 👤 Profile Tests

### Update Profile

| Test Case | Expected Result | Priority |
|-----------|-----------------|----------|
| User dapat update name | Name updated, flash success | 🔴 High |
| User dapat update email | Email updated, flash success | 🔴 High |
| Update email ke email duplicate | Error 422, email taken | 🔴 High |
| Update dengan name kosong | Error 422, name required | 🟡 Medium |

### Update Password

| Test Case | Expected Result | Priority |
|-----------|-----------------|----------|
| User dapat update password | Password updated, flash success | 🔴 High |
| Update dengan current password salah | Error 422, current password incorrect | 🔴 High |
| Update dengan password < 8 char | Error 422, password too short | 🟡 Medium |
| Update tanpa confirmation | Error 422, passwords must match | 🟡 Medium |

### Delete Account

| Test Case | Expected Result | Priority |
|-----------|-----------------|----------|
| User dapat delete account | Account deleted, logout, redirect home | 🔴 High |
| Delete dengan password salah | Error 422, password incorrect | 🔴 High |
| Delete menghapus semua data user | Novels, pen names, onboarding deleted | 🔴 High |

---

## 🎯 Onboarding Tests

### Onboarding Flow

| Test Case | Expected Result | Priority |
|-----------|-----------------|----------|
| User baru melihat onboarding checklist | Checklist visible on dashboard | 🟡 Medium |
| User dapat skip onboarding | onboarding_skipped = true | 🟡 Medium |
| Checklist progress updates | Progress bar sesuai completion | 🟢 Low |

---

## 🎨 UI Component Tests

### Button Component

| Test Case | Expected Result | Priority |
|-----------|-----------------|----------|
| Primary variant render correctly | Violet background | 🟢 Low |
| Secondary variant render correctly | Gray background | 🟢 Low |
| Danger variant render correctly | Red background | 🟢 Low |
| Loading state shows spinner | Spinner visible, button disabled | 🟡 Medium |

### Input Component

| Test Case | Expected Result | Priority |
|-----------|-----------------|----------|
| Label renders correctly | Label visible | 🟢 Low |
| Error state shows error message | Red border, error text | 🟡 Medium |
| Required asterisk visible | Red asterisk after label | 🟢 Low |

### Card Component

| Test Case | Expected Result | Priority |
|-----------|-----------------|----------|
| Card renders with content | Content visible in card | 🟢 Low |
| Card has correct styling | Border, shadow, rounded | 🟢 Low |

---

## 📋 Manual QA Checklist

### Pre-Testing Setup

```
[ ] Database migrated fresh
[ ] Seeder dijalankan (opsional)
[ ] Frontend di-build (yarn build)
[ ] Server berjalan (php artisan serve)
```

### Authentication Checklist

```
[ ] Register page accessible dari /register
[ ] Login page accessible dari /login
[ ] Register → auto login → redirect dashboard
[ ] Login → redirect dashboard
[ ] Logout → redirect home
[ ] Guest tidak bisa akses /dashboard
```

### Dashboard Checklist

```
[ ] Dashboard menampilkan header dengan user name
[ ] Stats cards menampilkan angka yang benar
[ ] Novel cards clickable dan responsive
[ ] Empty state muncul jika tidak ada novel
[ ] "New Novel" button works
```

### Profile Checklist

```
[ ] Profile page accessible dari /profile
[ ] Name field pre-filled dengan data user
[ ] Email field pre-filled dengan data user
[ ] Update profile shows success message
[ ] Update password form works
[ ] Delete account modal works
```

---

## 🔧 Running Tests

### All Tests

```bash
php artisan test
```

### Specific File

```bash
php artisan test tests/Feature/AuthTest.php
```

### Specific Test

```bash
php artisan test --filter=testUserCanRegister
```

---

## Related Documentation

- **API Reference:** [Authentication](../04-api-reference/authentication.md) | [Novels](../04-api-reference/novels.md) | [Profile](../04-api-reference/profile.md)
- **User Journeys:** [Authentication Flow](../07-user-journeys/authentication/user-auth-flow.md)
- **Sprint Documentation:** [Sprint 01 - Foundation](../10-sprints/sprint-01-foundation.md)

---

**Last Updated:** 2025-12-31
