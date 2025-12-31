# 📊 Gap Analysis: FRD vs Scrum Workflow

**Tanggal Analisis:** 31 Desember 2024  
**Status:** 🔴 Requires Updates

---

## 🎯 Tujuan Dokumen

Dokumen ini melakukan cross-reference antara Functional Requirements Document (FRD) dengan User Stories yang sudah dibuat untuk memastikan **TIDAK ADA** requirement yang terlewat.

---

## ⚠️ CRITICAL GAPS FOUND

### Fitur yang BELUM ADA di User Stories:

| No | Fitur dari FRD | Status | Prioritas |
|----|----------------|--------|-----------|
| 1 | **Codex Progression History** - Track how characters evolve over time | ❌ Missing | 🔴 Tinggi |
| 2 | **Collapsible Sections** - Insert AI content as collapsible/modular section | ❌ Missing | 🔴 Tinggi |
| 3 | **Novel Covers** - Upload/select cover image for novel | ❌ Missing | 🟡 Sedang |
| 4 | **Novel Templates** - Create novel from personal templates | ❌ Missing | 🟡 Sedang |
| 5 | **Settings Export/Import** - Export/import AI settings for multi-device | ❌ Missing | 🟡 Sedang |
| 6 | **Auto-recovery** - Recovery options when data loss occurs | ❌ Missing | 🔴 Tinggi |
| 7 | **Copy All Beats from Chapter** - Specific chapter action menu | ❌ Missing | 🟢 Rendah |
| 8 | **Delete Unused Empty Scenes** - Whole novel action | ❌ Missing | 🟢 Rendah |
| 9 | **Appearance Heatmap** - Visualize character appearances | ❌ Missing | 🟡 Sedang |
| 10 | **Word Statistics Dashboard** - Pacing analysis, trends | ❌ Missing | 🟡 Sedang |
| 11 | **Characters per Scene Analysis** - Identify overcrowded scenes | ❌ Missing | 🟡 Sedang |
| 12 | **Marker/Highlighter Tool** - Highlight important notes in manuscript | ❌ Missing | 🟡 Sedang |
| 13 | **Manual References in Plan** - Highlight key locations/timelines | ❌ Missing | 🟢 Rendah |
| 14 | **HTML Import** - Import dari HTML files | ❌ Missing | 🟢 Rendah |
| 15 | **Localization/i18n** - Multi-language UI support | ❌ Missing | 🟢 Rendah |
| 16 | **Context-help & Tooltips** - Inline help and documentation | ⚠️ Incomplete | 🟢 Rendah |
| 17 | **OpenRouter OAuth** - OAuth login for OpenRouter/Anthropic | ❌ Missing | 🟡 Sedang |
| 18 | **Scene Beats Detail** - Detailed beat crafting workflow | ⚠️ Incomplete | 🔴 Tinggi |

---

## 📋 Detailed FRD Traceability Matrix

### 1. Manuscript Editor (Writing Interface)

| FRD Requirement | User Story | Status | Notes |
|-----------------|------------|--------|-------|
| Rich text editing | US-001 | ✅ Complete | |
| Scene/Chapter structure | US-002 | ✅ Complete | |
| Scene metadata panel | US-003 | ✅ Complete | |
| Action menus for scenes/chapters | US-004 | ⚠️ Incomplete | Missing: copy all beats, delete empty scenes |
| Integrated AI tools in editor | US-005 | ✅ Complete | |
| Slash-command generation | US-006 | ⚠️ Incomplete | Missing: detailed beat crafting, sections |
| Text-replacement prompts | US-007 | ✅ Complete | |
| Focus mode | US-008 | ✅ Complete | |
| Theme and display options | US-009 | ✅ Complete | |
| **Collapsible sections** | ❌ None | ❌ Missing | Need new story |
| **Marker/Highlighter** | ❌ None | ❌ Missing | Need new story |

### 2. Story Planning & Outline Interface

| FRD Requirement | User Story | Status | Notes |
|-----------------|------------|--------|-------|
| Hierarchical story view (Acts/Chapters/Scenes) | US-010 | ✅ Complete | |
| Grid view | US-011 | ✅ Complete | |
| Matrix view | US-012 | ✅ Complete | |
| Outline view | US-013 | ✅ Complete | |
| Scene labels and custom fields | US-014 | ✅ Complete | |
| Scene subtitles and references | US-015 | ⚠️ Incomplete | Missing: manual references |
| Outlines and templates | US-016 | ✅ Complete | |
| Search & filter | US-017 | ✅ Complete | |
| Timeline overview | US-018 | ✅ Complete | |
| **Manual references** | ❌ None | ❌ Missing | Need enhancement to US-015 |

### 3. Codex (World & Character Database)

| FRD Requirement | User Story | Status | Notes |
|-----------------|------------|--------|-------|
| Structured entry editor | US-019, US-020 | ✅ Complete | |
| Tags/Labels | US-022 | ✅ Complete | |
| Thumbnails and appearance | US-023 | ✅ Complete | |
| Aliases/Nicknames | US-024 | ✅ Complete | |
| Description and custom fields | US-025 | ✅ Complete | |
| Research notes | US-026 | ✅ Complete | |
| Relations (nested entries) | US-027 | ✅ Complete | |
| Mentions tracking | US-028 | ✅ Complete | |
| AI context controls | US-030 | ✅ Complete | |
| **Progression history** | ❌ None | ❌ Missing | Track character evolution |
| Series support | US-031 | ✅ Complete | |

### 4. Snippets (Notes & Ideas)

| FRD Requirement | User Story | Status | Notes |
|-----------------|------------|--------|-------|
| Quick notes repository | US-032 | ✅ Complete | |
| Pinning and access | US-033 | ✅ Complete | |
| Import excerpt (Extract to Codex/Scene) | US-034 | ✅ Complete | |

### 5. AI/Chat Interface

| FRD Requirement | User Story | Status | Notes |
|-----------------|------------|--------|-------|
| Chat workspace | US-035 | ✅ Complete | |
| Context options | US-037 | ✅ Complete | |
| Prompt and model switching | US-039 | ✅ Complete | |
| Default and custom prompts | US-043-049 | ✅ Complete | In Prompt Management epic |
| Thread actions (pin, fork, archive) | US-036 | ⚠️ Incomplete | Missing: fork (duplicate), archive |
| Transfer to novel (Extract) | US-042 | ✅ Complete | |

### 6. Prompt and Template Management

| FRD Requirement | User Story | Status | Notes |
|-----------------|------------|--------|-------|
| Prompt library | US-043 | ✅ Complete | |
| Prompt structure (variables) | US-044, US-045 | ✅ Complete | |
| Prompt types | US-047 | ✅ Complete | |
| Preview and testing | US-048 | ✅ Complete | |
| Model settings per prompt | US-049 | ✅ Complete | |

### 7. Model/AI Connections

| FRD Requirement | User Story | Status | Notes |
|-----------------|------------|--------|-------|
| OpenAI connection | US-050 | ✅ Complete | |
| Anthropic/Claude connection | US-051 | ✅ Complete | |
| Google Gemini connection | US-052 | ✅ Complete | |
| OpenAI-compatible endpoints | US-053 | ✅ Complete | |
| Ollama connection | US-054 | ✅ Complete | |
| LM Studio connection | US-055 | ✅ Complete | |
| Model selection UI | US-056 | ✅ Complete | |
| Cost & limits tracking | US-057 | ✅ Complete | |
| **OpenRouter OAuth** | ❌ None | ❌ Missing | Need new story |
| Fallback & offline | ⚠️ Implied | ⚠️ Incomplete | Need explicit story |

### 8. Content Import/Export & Data Management

| FRD Requirement | User Story | Status | Notes |
|-----------------|------------|--------|-------|
| Import from Word (.docx) | US-058 | ✅ Complete | |
| Import from Markdown | US-059 | ✅ Complete | |
| **Import from HTML** | ❌ None | ❌ Missing | Need new story |
| Export to Word (.docx) | US-060 | ✅ Complete | |
| Export to Markdown | US-061 | ✅ Complete | |
| Export metadata | US-062 | ✅ Complete | |
| Archiving scenes | US-063 | ✅ Complete | |
| Revision history | US-064 | ✅ Complete | |
| **Novel covers** | ❌ None | ❌ Missing | Need new story |
| **Localization** | ❌ None | ❌ Missing | Need new story (optional) |

### 9. Organizational & Miscellaneous Features

| FRD Requirement | User Story | Status | Notes |
|-----------------|------------|--------|-------|
| Dashboard/project list | US-065 | ✅ Complete | |
| Novel creation & setup | US-066 | ⚠️ Incomplete | Missing: templates, covers |
| Pen names management | US-067 | ✅ Complete | |
| Theme and font settings | US-068 | ✅ Complete | |
| Collapsible UI panels | US-069 | ✅ Complete | |
| Search within project | US-070 | ✅ Complete | |
| Backup/restore | US-071 | ⚠️ Incomplete | Missing: auto-recovery |
| User account & profile | US-072 | ✅ Complete | |
| API keys settings | US-073 | ⚠️ Incomplete | Missing: export/import settings |
| Help & documentation | US-074 | ⚠️ Incomplete | Missing: tooltips, inline help |

---

## 📈 Additional NovelCrafter Features (from Web Research)

Fitur-fitur ini ditemukan dari research tambahan ke NovelCrafter:

| Feature | Description | Priority | Status |
|---------|-------------|----------|--------|
| **Appearance Heatmap** | Visualize where characters appear most | 🟡 Sedang | ❌ Missing |
| **Word Statistics** | Track word count trends, daily goals | 🟡 Sedang | ❌ Missing |
| **Characters per Scene** | Analyze scene balance | 🟡 Sedang | ❌ Missing |
| **Scene Sections** | Collapsible content blocks in editor | 🔴 Tinggi | ❌ Missing |
| **Beat Workflow** | Detailed scene beat crafting | 🔴 Tinggi | ⚠️ Incomplete |

---

## 🔧 Required Actions

### High Priority (Must Fix):
1. ✏️ Add US-075: Codex Progression History
2. ✏️ Add US-076: Collapsible Sections in Editor
3. ✏️ Add US-077: Auto-recovery & Data Safety
4. ✏️ Update US-006: Add detailed beat crafting workflow
5. ✏️ Update US-004: Add missing chapter actions

### Medium Priority (Should Fix):
6. ✏️ Add US-078: Novel Covers
7. ✏️ Add US-079: Novel Templates
8. ✏️ Add US-080: Appearance Heatmap
9. ✏️ Add US-081: Word Statistics Dashboard
10. ✏️ Add US-082: Characters per Scene Analysis
11. ✏️ Add US-083: Marker/Highlighter Tool
12. ✏️ Add US-084: Settings Export/Import
13. ✏️ Add US-085: OpenRouter OAuth
14. ✏️ Update US-036: Add fork & archive thread actions

### Low Priority (Nice to Have):
15. ✏️ Add US-086: HTML Import
16. ✏️ Add US-087: Manual References in Plan
17. ✏️ Add US-088: Localization (i18n)
18. ✏️ Update US-074: Enhanced inline help

---

## 📊 Coverage Summary

| Category | Total FRD Items | Covered | Partial | Missing | Coverage % |
|----------|-----------------|---------|---------|---------|------------|
| Manuscript Editor | 11 | 8 | 1 | 2 | 73% |
| Story Planning | 9 | 8 | 1 | 0 | 94% |
| Codex | 11 | 10 | 0 | 1 | 91% |
| Snippets | 3 | 3 | 0 | 0 | 100% |
| AI/Chat | 6 | 5 | 1 | 0 | 92% |
| Prompt Management | 5 | 5 | 0 | 0 | 100% |
| AI Connections | 10 | 8 | 1 | 1 | 85% |
| Import/Export | 10 | 7 | 0 | 3 | 70% |
| Organization | 10 | 6 | 4 | 0 | 80% |
| **TOTAL** | **75** | **60** | **8** | **7** | **80%** |

**Current Coverage: 80%**  
**Target Coverage: 100%**

---

## 📝 Conclusion

Dokumen scrum workflow saat ini mencakup **80%** dari requirements FRD. Terdapat **7 fitur yang completely missing** dan **8 fitur yang incomplete**. File `15-missing-features.md` akan berisi user stories baru untuk gap ini.
