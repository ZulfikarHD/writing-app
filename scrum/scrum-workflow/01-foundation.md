# 🏠 Epic 1: Foundation & Core Setup

**Epic ID:** EPIC-001  
**Prioritas:** 🔴 Tinggi  
**Sprint Target:** 1, 7  
**Total Story Points:** 34

---

## 📋 Deskripsi Epic

Membangun fondasi aplikasi termasuk dashboard, project management, user authentication, novel setup, dan onboarding experience. Epic ini adalah **entry point** utama untuk pengguna - tanpa ini, user tidak bisa menggunakan aplikasi.

---

## 🎯 Goals

- User authentication dan account management
- Dashboard sebagai entry point utama
- Novel creation dan setup workflow
- Guided onboarding untuk new users
- Project organization dengan pen names dan covers

---

## 📑 User Stories

### US-065: Dashboard & Project List
**Prioritas:** 🔴 Tinggi | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** dashboard menampilkan semua novel saya,  
**Agar** saya dapat mengelola projects dengan mudah.

#### Acceptance Criteria:
- [ ] Grid/list view semua novels
- [ ] Display: cover, title, pen name, word count, last edited
- [ ] Create new novel button
- [ ] Delete novel dengan konfirmasi
- [ ] Duplicate novel option
- [ ] Sort by: recent, alphabetical, word count
- [ ] Search novels by title
- [ ] Quick stats: total words, total novels

---

### US-066: Novel Creation & Setup
**Prioritas:** 🔴 Tinggi | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** wizard untuk membuat novel baru,  
**Agar** saya dapat setup project dengan benar.

#### Acceptance Criteria:
- [ ] Novel title (required)
- [ ] Pen name selection/creation
- [ ] Genre selection
- [ ] POV default (first/third person)
- [ ] Tense default (past/present)
- [ ] Upload cover image (optional)
- [ ] Start from scratch atau from template
- [ ] Templates: blank, 3-act structure, hero's journey
- [ ] Preview template structure sebelum create

---

### US-067: Pen Names Management
**Prioritas:** 🟢 Rendah | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** mengelola pen names,  
**Agar** saya dapat group novels by author identity.

#### Acceptance Criteria:
- [ ] Create pen name dengan nama
- [ ] Assign pen name ke novel
- [ ] Filter novels by pen name
- [ ] Pen name avatar/image (optional)
- [ ] Default pen name setting
- [ ] Edit/delete pen names

---

### US-072: User Account & Profile
**Prioritas:** 🔴 Tinggi | **Story Points:** 5

**Sebagai** pengguna,  
**Saya ingin** account management,  
**Agar** saya dapat login dan manage profile.

#### Acceptance Criteria:
- [ ] Sign up dengan email
- [ ] Login dengan email/password
- [ ] Social login (Google) - optional
- [ ] Profile settings: name, email, avatar
- [ ] Change password
- [ ] Delete account option
- [ ] Email verification

#### Technical Notes:
- Use Laravel Sanctum atau Passport untuk auth
- Secure password hashing

---

### US-079: Novel Covers
**Prioritas:** 🟡 Sedang | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** upload cover image untuk novel,  
**Agar** project saya lebih visual dan mudah diidentifikasi.

#### Acceptance Criteria:
- [ ] Upload cover image di novel settings
- [ ] Supported formats: JPG, PNG, WebP
- [ ] Image crop/resize tool
- [ ] Cover displayed di:
  - Dashboard project list
  - Novel header
  - Export (embed in docs)
- [ ] Default placeholder cover
- [ ] Remove/replace cover
- [ ] Cover aspect ratio: 2:3 (book standard)

#### FRD Reference:
> "The user can upload or select a cover image for the novel."

---

### US-080: Novel Templates
**Prioritas:** 🟡 Sedang | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** membuat dan menggunakan novel templates,  
**Agar** saya dapat jumpstart proyek baru dengan struktur yang saya suka.

#### Acceptance Criteria:
- [ ] "Save as Template" option untuk novel yang ada
- [ ] Template menyimpan: structure, Codex entries, settings (not prose)
- [ ] Create novel dari template
- [ ] Built-in templates: Blank, 3-Act, 5-Act, Hero's Journey, Romance, Mystery
- [ ] Personal template library
- [ ] Edit/delete personal templates
- [ ] Export/import templates
- [ ] Template preview sebelum create

#### FRD Reference:
> "The user can create a new novel from scratch or from a template."

---

### US-111: Getting Started / Onboarding Experience
**Prioritas:** 🔴 Tinggi | **Story Points:** 8

**Sebagai** pengguna baru,  
**Saya ingin** guided onboarding experience saat pertama kali menggunakan app,  
**Agar** saya dapat memahami fitur-fitur utama dan mulai menulis dengan cepat.

#### Acceptance Criteria:

**Quick Start Flow:**
- [ ] Welcome screen setelah first login/signup
- [ ] "Create Your First Novel" guided wizard
- [ ] Step-by-step tutorial yang dapat di-skip
- [ ] Progress indicator untuk onboarding steps
- [ ] "Skip tutorial" option yang accessible

**App Layout Introduction:**
- [ ] Interactive tour menjelaskan main areas:
  - Dashboard/Project list
  - Editor workspace
  - Plan view
  - Codex panel
  - Chat panel
- [ ] Highlight dan tooltip untuk setiap area
- [ ] "Next" / "Back" navigation dalam tour

**First Writing Session Guide:**
- [ ] Panduan menulis scene pertama
- [ ] Pengenalan slash commands (/)
- [ ] Pengenalan formatting tools
- [ ] Pengenalan scene/chapter structure
- [ ] Sample content option untuk practice

**AI Setup Walkthrough:**
- [ ] "Getting Started with AI" section
- [ ] Pilihan AI vendor dengan pros/cons
- [ ] Step-by-step API key setup
- [ ] Test connection confirmation
- [ ] First AI interaction demo

**Import Existing Work:**
- [ ] Option untuk import existing novel
- [ ] Supported formats explanation (Word, Markdown)
- [ ] Preview hasil import sebelum confirm

**Checklist & Progress:**
- [ ] Getting Started checklist di dashboard
- [ ] Track completed steps
- [ ] "Mark as done" untuk setiap milestone
- [ ] Hide checklist setelah selesai
- [ ] Option untuk revisit tutorial

#### Technical Notes:
- Store onboarding state per user
- Allow reset onboarding dari settings
- Consider video tutorials (optional)
- Mobile-optimized onboarding flow

#### Source:
> [NovelCrafter Quick Start Guide](https://www.novelcrafter.com/help/getting-started/quick-start/creating-your-first-novel)

---

## 📊 Sprint Breakdown

### Sprint 1 (Foundation - 15 points)
- US-072: User Account & Profile (5 pts) - **MUST BE FIRST**
- US-065: Dashboard & Project List (5 pts)
- US-066: Novel Creation & Setup (5 pts)

### Sprint 7 (Enhancement - 19 points)
- US-111: Getting Started / Onboarding (8 pts)
- US-080: Novel Templates (5 pts)
- US-079: Novel Covers (3 pts)
- US-067: Pen Names Management (3 pts)

---

## 🔗 Dependencies

- **US-072 (User Account) MUST be Sprint 1** - everything depends on authentication
- US-065 (Dashboard) depends on US-072
- US-066 (Novel Creation) depends on US-065
- US-111 (Onboarding) depends on most features being ready
- All other epics depend on Epic 1 being complete

---

## 📝 Notes

- This epic contains the **ENTRY POINT** of the application
- User Account (US-072) adalah **blocking dependency** untuk semua fitur lain
- Dashboard adalah first thing user sees after login
- Onboarding is crucial for user retention
- Consider progressive disclosure untuk complex features
- Mobile-first approach untuk dashboard dan creation flow
