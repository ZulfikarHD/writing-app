# 🔐 Authentication API Reference

## Overview

Authentication API untuk NovelWrite, yaitu: endpoints untuk registrasi user baru, login, dan logout.

---

## Endpoints

### Register User

Registrasi user baru ke sistem.

| Property | Value |
|----------|-------|
| **Method** | `POST` |
| **URL** | `/register` |
| **Auth Required** | No |
| **Middleware** | `guest` |

#### Request Body

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| `name` | string | ✅ | min:2, max:255 |
| `email` | string | ✅ | email, unique:users |
| `password` | string | ✅ | min:8, confirmed |
| `password_confirmation` | string | ✅ | same as password |

#### Example Request

```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

#### Response

**Success (302 Redirect)**
- Redirects to `/dashboard`
- Sets authentication session

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

### Login

Login user ke sistem.

| Property | Value |
|----------|-------|
| **Method** | `POST` |
| **URL** | `/login` |
| **Auth Required** | No |
| **Middleware** | `guest` |

#### Request Body

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| `email` | string | ✅ | email, exists:users |
| `password` | string | ✅ | - |
| `remember` | boolean | ❌ | - |

#### Example Request

```json
{
    "email": "john@example.com",
    "password": "password123",
    "remember": true
}
```

#### Response

**Success (302 Redirect)**
- Redirects to `/dashboard`
- Sets authentication session
- If `remember=true`, creates persistent session

**Error (422 Validation Error)**

```json
{
    "message": "These credentials do not match our records.",
    "errors": {
        "email": ["These credentials do not match our records."]
    }
}
```

---

### Logout

Logout user dari sistem.

| Property | Value |
|----------|-------|
| **Method** | `POST` |
| **URL** | `/logout` |
| **Auth Required** | Yes |
| **Middleware** | `auth` |

#### Response

**Success (302 Redirect)**
- Redirects to `/`
- Clears authentication session

---

## Frontend Pages

| Page | Path | Component |
|------|------|-----------|
| Register | `/register` | `pages/Auth/Register.vue` |
| Login | `/login` | `pages/Auth/Login.vue` |

---

## Related Documentation

- **Testing Guide:** [Foundation Testing](../06-testing/foundation-testing.md)
- **User Journeys:** [Authentication Flow](../07-user-journeys/authentication/user-auth-flow.md)
- **Sprint Documentation:** [Sprint 01 - Foundation](../10-sprints/sprint-01-foundation.md)

---

**Last Updated:** 2025-12-31
