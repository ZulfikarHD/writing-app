# 👤 Profile API Reference

## Overview

Profile API untuk NovelWrite, yaitu: endpoints untuk mengelola profil user, update password, dan delete account.

---

## Endpoints

### Edit Profile Page

Menampilkan halaman edit profile.

| Property | Value |
|----------|-------|
| **Method** | `GET` |
| **URL** | `/profile` |
| **Auth Required** | Yes |
| **Middleware** | `auth` |

#### Response

**Success (200)**
- Renders `pages/Profile/Edit.vue` with Inertia
- Returns current user data

---

### Update Profile

Update profile information (name, email).

| Property | Value |
|----------|-------|
| **Method** | `PATCH` |
| **URL** | `/profile` |
| **Auth Required** | Yes |
| **Middleware** | `auth` |

#### Request Body

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| `name` | string | ✅ | min:2, max:255 |
| `email` | string | ✅ | email, unique:users (except current) |

#### Example Request

```json
{
    "name": "John Updated",
    "email": "john.updated@example.com"
}
```

#### Response

**Success (302 Redirect)**
- Redirects back to `/profile`
- Flash message: "Profile updated successfully!"

**Error (422 Validation Error)**

```json
{
    "message": "The email has already been taken.",
    "errors": {
        "email": ["The email has already been taken."]
    }
}
```

---

### Update Password

Update user password.

| Property | Value |
|----------|-------|
| **Method** | `PUT` |
| **URL** | `/profile/password` |
| **Auth Required** | Yes |
| **Middleware** | `auth` |

#### Request Body

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| `current_password` | string | ✅ | current_password |
| `password` | string | ✅ | min:8, confirmed |
| `password_confirmation` | string | ✅ | same as password |

#### Example Request

```json
{
    "current_password": "oldpassword123",
    "password": "newpassword456",
    "password_confirmation": "newpassword456"
}
```

#### Response

**Success (302 Redirect)**
- Redirects back to `/profile`
- Flash message: "Password updated successfully!"

**Error (422 Validation Error)**

```json
{
    "message": "The current password is incorrect.",
    "errors": {
        "current_password": ["The current password is incorrect."]
    }
}
```

---

### Delete Account

Menghapus akun user secara permanen.

| Property | Value |
|----------|-------|
| **Method** | `DELETE` |
| **URL** | `/profile` |
| **Auth Required** | Yes |
| **Middleware** | `auth` |

#### Request Body

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| `password` | string | ✅ | current_password |

#### Example Request

```json
{
    "password": "currentpassword123"
}
```

#### Response

**Success (302 Redirect)**
- Redirects to `/`
- Logs out user
- Deletes all user data (novels, pen names, onboarding state)

**Error (422 Validation Error)**

```json
{
    "message": "The password is incorrect.",
    "errors": {
        "password": ["The password is incorrect."]
    }
}
```

---

## Frontend Components

| Component | Path | Description |
|-----------|------|-------------|
| Edit Page | `pages/Profile/Edit.vue` | Profile settings page |
| Input | `components/ui/Input.vue` | Reusable input component |
| Button | `components/ui/Button.vue` | Reusable button component |
| Card | `components/ui/Card.vue` | Reusable card component |

---

## Profile Page Sections

### 1. Profile Information
- Name field
- Email field
- Save Changes button

### 2. Update Password
- Current Password field
- New Password field
- Confirm New Password field
- Update Password button

### 3. Delete Account
- Warning message
- Delete Account button
- Confirmation modal with password verification

---

## Related Documentation

- **Testing Guide:** [Foundation Testing](../06-testing/foundation-testing.md)
- **Sprint Documentation:** [Sprint 01 - Foundation](../10-sprints/sprint-01-foundation.md)

---

**Last Updated:** 2025-12-31
