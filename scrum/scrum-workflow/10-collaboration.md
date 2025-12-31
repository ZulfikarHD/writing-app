# 👥 Epic 10: Collaboration & Teams

**Epic ID:** EPIC-010  
**Prioritas:** 🔴 Tinggi  
**Sprint Target:** 8-9  
**Total Story Points:** 21

---

## 📋 Deskripsi Epic

Membangun fitur kolaborasi untuk memungkinkan penulis bekerja sama dengan coauthor, editor, atau beta readers. Termasuk sistem undangan, role management, dan team features untuk writing groups.

---

## 🎯 Goals

- Enable real-time atau async collaboration
- Role-based access (Viewer vs Editor)
- Team management untuk writing groups
- Shared projects tanpa shared AI credentials

---

## 📑 User Stories

### US-091: Collaboration & Coauthoring
**Prioritas:** 🔴 Tinggi | **Story Points:** 13

**Sebagai** penulis,  
**Saya ingin** mengundang collaborator untuk bekerja bersama di novel,  
**Agar** saya dapat coauthor atau mendapat feedback dari editor/beta reader.

#### Acceptance Criteria:
- [ ] Invite collaborator via email
- [ ] Role selection: Viewer (read-only) atau Editor (full access)
- [ ] Viewer bisa akses dengan free account
- [ ] Editor memerlukan paid subscription
- [ ] Shared projects ditandai dengan "shared with you" tag
- [ ] Manage invitations (revoke, change role)
- [ ] Collaboration untuk individual novel
- [ ] Collaboration untuk entire series (akses series codex)
- [ ] Notification system untuk invitations
- [ ] AI connections TIDAK shared (per-user)
- [ ] Activity log untuk collaborative edits

#### Technical Notes:
- Real-time sync dengan WebSockets atau polling
- Conflict resolution untuk concurrent edits
- Email notification system
- Permission middleware di backend

#### Source:
> [Collaboration and Coauthoring - NovelCrafter Docs](https://docs.novelcrafter.com/en/articles/8688048-collaboration-and-coauthoring)

---

### US-092: Teams Feature
**Prioritas:** 🔴 Tinggi | **Story Points:** 8

**Sebagai** penulis,  
**Saya ingin** membuat team untuk writing group atau editing team,  
**Agar** saya dapat share novels dengan multiple collaborators sekaligus.

#### Acceptance Criteria:
- [ ] Create team dengan nama
- [ ] Invite team members via email
- [ ] Set team visibility preferences
- [ ] Share novel/series dengan entire team (satu klik)
- [ ] Manage team members (add, remove, roles)
- [ ] Team dashboard menampilkan shared projects
- [ ] Leave team option untuk members

#### Technical Notes:
- Team = collection of users
- Team owner vs team member roles
- Batch sharing to all team members
- Team activity feed

#### Source:
> [Teams Feature - NovelCrafter](https://www.novelcrafter.com/features)

---

## 📊 Sprint Breakdown

### Sprint 8 (13 points)
- US-091: Collaboration & Coauthoring (13 pts) - **MAJOR FEATURE**

### Sprint 9 (8 points)
- US-092: Teams Feature (8 pts)

---

## 🔗 Dependencies

- **Requires:** Epic 1 (Foundation) - User account system
- US-092 (Teams) depends on US-091 (Collaboration)
- Notification system dari Epic 11 (System)

---

## 📝 Notes

- Collaboration adalah **growth driver** - word of mouth dari shared projects
- Consider "conflict resolution" strategy jika 2 editors edit same content
- AI connections tetap private per user untuk security
- Activity log penting untuk tracking who changed what
- Consider real-time presence indicators (who's online)
- Mobile experience untuk reviewing shared content
