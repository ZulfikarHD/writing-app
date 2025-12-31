# 🔐 User Authentication Flow

## Overview

Dokumentasi user journey untuk authentication flow NovelWrite, yaitu: alur lengkap dari user belum terdaftar hingga berhasil masuk ke dashboard.

---

## User Personas

| Persona | Description |
|---------|-------------|
| **New Writer** | User baru yang belum pernah menggunakan NovelWrite |
| **Returning Writer** | User yang sudah punya akun dan ingin login |

---

## Journey 1: Registration Flow

### Scenario
Seorang penulis baru ingin mulai menggunakan NovelWrite untuk menulis novel pertamanya.

### Flow Diagram

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  Landing    │────▶│  Register   │────▶│  Fill Form  │────▶│  Submit     │
│  Page (/)   │     │  Page       │     │             │     │             │
└─────────────┘     └─────────────┘     └─────────────┘     └─────────────┘
                                                                   │
                    ┌─────────────┐     ┌─────────────┐            │
                    │  Onboarding │◀────│  Dashboard  │◀───────────┘
                    │  Checklist  │     │             │
                    └─────────────┘     └─────────────┘
```

### Steps

| # | User Action | System Response | Page |
|---|-------------|-----------------|------|
| 1 | Kunjungi landing page | Tampilkan hero section dengan CTA | `/` |
| 2 | Klik "Get Started" atau "Register" | Navigate ke register page | `/register` |
| 3 | Isi form (name, email, password) | Validate input real-time | `/register` |
| 4 | Klik "Create Account" | Process registration | `/register` |
| 5 | - | Auto login & redirect | → `/dashboard` |
| 6 | Lihat dashboard | Tampilkan onboarding checklist | `/dashboard` |

### Error Scenarios

| Error | User Sees | Recovery |
|-------|-----------|----------|
| Email sudah terdaftar | "The email has already been taken." | Gunakan email lain atau login |
| Password terlalu pendek | "Password must be at least 8 characters." | Input password lebih panjang |
| Password tidak match | "Password confirmation doesn't match." | Re-enter confirmation |

---

## Journey 2: Login Flow

### Scenario
Seorang penulis yang sudah punya akun ingin melanjutkan menulis novelnya.

### Flow Diagram

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  Landing    │────▶│   Login     │────▶│  Fill Form  │────▶│  Submit     │
│  Page (/)   │     │   Page      │     │             │     │             │
└─────────────┘     └─────────────┘     └─────────────┘     └─────────────┘
                                                                   │
                                        ┌─────────────┐            │
                                        │  Dashboard  │◀───────────┘
                                        │  + Novels   │
                                        └─────────────┘
```

### Steps

| # | User Action | System Response | Page |
|---|-------------|-----------------|------|
| 1 | Kunjungi landing page | Tampilkan hero section | `/` |
| 2 | Klik "Login" | Navigate ke login page | `/login` |
| 3 | Isi email dan password | Validate input | `/login` |
| 4 | (Optional) Check "Remember me" | Enable persistent session | `/login` |
| 5 | Klik "Sign In" | Authenticate user | `/login` |
| 6 | - | Redirect to dashboard | → `/dashboard` |
| 7 | Lihat dashboard | Tampilkan novels dan stats | `/dashboard` |

### Error Scenarios

| Error | User Sees | Recovery |
|-------|-----------|----------|
| Email tidak terdaftar | "These credentials do not match our records." | Register atau cek email |
| Password salah | "These credentials do not match our records." | Coba password lain |

---

## Journey 3: Logout Flow

### Flow Diagram

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  Any Auth   │────▶│  Click      │────▶│  Landing    │
│  Page       │     │  Logout     │     │  Page (/)   │
└─────────────┘     └─────────────┘     └─────────────┘
```

### Steps

| # | User Action | System Response | Page |
|---|-------------|-----------------|------|
| 1 | Klik dropdown di header | Tampilkan menu | Any page |
| 2 | Klik "Log Out" | Process logout | - |
| 3 | - | Clear session & redirect | → `/` |

---

## UI Touchpoints

### Registration Page
- Clean, minimal form
- Social proof (optional)
- Link to login for existing users
- Password strength indicator (visual)

### Login Page
- Email input
- Password input with toggle visibility
- Remember me checkbox
- Link to register for new users

### Dashboard (Post-Auth)
- Personalized greeting
- Quick stats overview
- Novel grid/list
- New novel CTA

---

## Mobile Considerations

| Element | Mobile Adaptation |
|---------|-------------------|
| Forms | Full-width inputs, larger touch targets |
| Navigation | Hamburger menu dengan dropdown |
| Cards | Single column layout |
| Buttons | Full-width CTAs |

---

## Related Documentation

- **API Reference:** [Authentication API](../../04-api-reference/authentication.md)
- **Testing Guide:** [Foundation Testing](../../06-testing/foundation-testing.md)
- **Sprint Documentation:** [Sprint 01 - Foundation](../../10-sprints/sprint-01-foundation.md)

---

**Last Updated:** 2025-12-31
