# 📝 Epic 4: Snippets (Notes & Ideas)

**Epic ID:** EPIC-005  
**Prioritas:** 🟡 Sedang  
**Sprint Target:** 4-5  
**Total Story Points:** 13

---

## 📋 Deskripsi Epic

Membangun fitur Snippets untuk menyimpan catatan cepat, ideas, research, dan konten yang belum masuk ke novel utama. Snippets dapat di-pin untuk referensi mudah saat menulis.

---

## 🎯 Goals

- Repository untuk quick notes dan ideas
- Easy access saat menulis
- Extract ke Codex atau novel

---

## 📑 User Stories

### US-032: Create & Manage Snippets
**Prioritas:** 🔴 Tinggi | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** membuat dan mengelola snippets,  
**Agar** saya dapat menyimpan notes dan ideas terpisah dari novel.

#### Acceptance Criteria:
- [ ] Create new snippet dengan title dan content
- [ ] Rich text editor atau plain text mode
- [ ] Markdown support (optional)
- [ ] List semua snippets dengan search
- [ ] Edit snippet content
- [ ] Delete snippet dengan konfirmasi
- [ ] Auto-save saat editing
- [ ] Snippets terpisah per novel

#### Technical Notes:
- Simple CRUD operations
- Store as plain text atau JSON

---

### US-033: Pin & Quick Access
**Prioritas:** 🟡 Sedang | **Story Points:** 3

**Sebagai** penulis,  
**Saya ingin** pin snippets untuk quick access,  
**Agar** saya dapat melihat notes penting saat menulis.

#### Acceptance Criteria:
- [ ] Star/pin snippet dari list atau detail view
- [ ] Pinned snippets muncul di sidebar
- [ ] Drag snippet ke split view (side-by-side dengan editor)
- [ ] Maximum 3 pinned snippets di sidebar
- [ ] Unpin dengan click star lagi
- [ ] Pinned order dapat di-reorder

---

### US-034: Extract to Codex/Scene
**Prioritas:** 🟡 Sedang | **Story Points:** 5

**Sebagai** penulis,  
**Saya ingin** mengconvert snippet ke Codex entry atau scene,  
**Agar** saya dapat dengan mudah memindahkan content ke tempat yang tepat.

#### Acceptance Criteria:
- [ ] Select text di snippet → "Extract to Codex"
- [ ] Modal untuk pilih entry type dan set name
- [ ] Selected text menjadi description
- [ ] Whole snippet → "Convert to Scene"
- [ ] Choose which chapter untuk scene baru
- [ ] Option: delete snippet after extract atau keep
- [ ] Link back to original snippet (optional)

---

## 📊 Sprint Breakdown

### Sprint 4-5 (13 points)
- US-032: Create & Manage Snippets (5 pts)
- US-033: Pin & Quick Access (3 pts)
- US-034: Extract to Codex/Scene (5 pts)

---

## 🔗 Dependencies

- Epic 3 (Codex) untuk Extract to Codex feature
- Epic 1 (Manuscript Editor) untuk Convert to Scene

---

## 📝 Notes

- Keep snippets simple dan lightweight
- Consider folder organization jika banyak snippets
