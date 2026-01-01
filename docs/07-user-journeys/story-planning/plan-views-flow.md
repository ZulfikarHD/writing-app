# 🗺️ Story Planning User Journeys

## Overview

Dokumen ini menjelaskan user flows untuk Story Planning System, yaitu: bagaimana penulis menggunakan Plan interface untuk memvisualisasi, mengorganisir, dan merencanakan novel mereka.

---

## Journey 1: First-Time Plan Access

### 📍 Scenario
Penulis baru pertama kali mengakses Plan untuk melihat struktur cerita mereka.

```
📍 START: Workspace (Write mode)
    │
    ├─▶ Click "Plan" di mode navigation
    │   └─ Mode switches ke Plan
    │
    ├─▶ See Grid View (default)
    │   ├─ Scene cards visible
    │   ├─ Grouped by Chapter → Act
    │   └─ Word counts displayed
    │
    ├─▶ Click scene card
    │   └─ Navigate ke editor dengan scene tersebut
    │
    └─▶ ✅ SUCCESS: User understands story structure
```

### Key Screens
1. **Workspace with Mode Navigation** - Write/Plan/Codex toggle
2. **Grid View** - Scene cards in grid layout
3. **Scene Card** - Title, summary, word count, labels

---

## Journey 2: Create Story Structure from Outline

### 📍 Scenario
Penulis ingin membuat struktur novel dari outline yang sudah ada.

```
📍 START: Plan View (empty novel)
    │
    ├─▶ Click "From Outline" button
    │   └─ Modal opens
    │
    ├─▶ Option A: Select Template
    │   ├─ Browse 8 built-in templates
    │   ├─ Select "Three Act Structure"
    │   └─ Template text loaded
    │
    ├─▶ Option B: Paste Custom Outline
    │   ├─ Paste text dengan indentation
    │   └─ e.g.:
    │       Act 1: Setup
    │         Chapter 1: Opening
    │           Scene 1: Hook
    │           Scene 2: Introduction
    │
    ├─▶ Click "Preview"
    │   └─ See parsed structure in tree view
    │
    ├─▶ Click "Create"
    │   ├─ Acts created
    │   ├─ Chapters created
    │   └─ Scenes created
    │
    └─▶ ✅ SUCCESS: Novel structure ready
```

### Template Options
- Three Act Structure
- Save the Cat (15 beats)
- Hero's Journey (12 steps)
- Dan Harmon's Story Circle
- Freytag's Pyramid
- Seven Point Story Structure
- Fichtean Curve
- Derek Murphy's 24 Chapters

---

## Journey 3: Reorganize Scenes with Drag & Drop

### 📍 Scenario
Penulis ingin mengubah urutan scenes dan memindahkan scene ke chapter lain.

```
📍 START: Grid View dengan multiple scenes
    │
    ├─▶ Hover scene card
    │   └─ Drag handle appears
    │
    ├─▶ Action A: Reorder dalam chapter
    │   ├─ Drag scene
    │   ├─ See drop indicator
    │   └─ Drop di posisi baru
    │
    ├─▶ Action B: Move ke different chapter
    │   ├─ Drag scene
    │   ├─ Hover ke chapter lain
    │   └─ Drop di chapter baru
    │
    ├─▶ Verify
    │   ├─ Scene di posisi baru
    │   └─ Positions auto-updated
    │
    └─▶ ✅ SUCCESS: Story reorganized
```

---

## Journey 4: Track Elements with Matrix View

### 📍 Scenario
Penulis ingin melihat di mana character/location muncul di sepanjang cerita.

```
📍 START: Plan View → Grid
    │
    ├─▶ Click "Matrix" di View Switcher
    │   └─ Matrix view loads
    │
    ├─▶ Select "Entries" mode (default)
    │   ├─ Scenes as rows
    │   └─ Codex entries as columns
    │
    ├─▶ Filter by type
    │   ├─ Click "Characters" filter
    │   └─ Only character columns shown
    │
    ├─▶ Analyze
    │   ├─ See which scenes have character
    │   ├─ Spot gaps in character arc
    │   └─ Find scenes without characters
    │
    ├─▶ Assign codex ke scene
    │   ├─ Click empty cell
    │   └─ Entry assigned
    │
    └─▶ ✅ SUCCESS: Character appearances tracked
```

### Matrix Modes
| Mode | Rows | Columns | Action |
|------|------|---------|--------|
| Entries | Scenes | Codex entries | Click to assign |
| POV | Scenes | Characters | Click to set POV |
| Labels | Scenes | Status labels | Click to toggle |
| Custom | Scenes | Selected entries | Manual selection |

---

## Journey 5: Set POV for Scenes

### 📍 Scenario
Penulis ingin mengatur POV character untuk setiap scene.

```
📍 START: Matrix View
    │
    ├─▶ Select "POV" mode
    │   └─ Characters as columns
    │
    ├─▶ For each scene:
    │   ├─ Click cell di character column
    │   ├─ Select POV type:
    │   │   ├─ 1st Person
    │   │   ├─ 2nd Person
    │   │   ├─ 3rd Person Limited
    │   │   └─ 3rd Person Omniscient
    │   └─ POV set
    │
    ├─▶ Alternative: Scene Context Menu
    │   ├─ Right-click scene card
    │   ├─ Click "Set POV"
    │   └─ Select character + type
    │
    ├─▶ Verify in Grid View
    │   └─ POV indicator on cards
    │
    └─▶ ✅ SUCCESS: All scenes have POV
```

---

## Journey 6: Manage Scene Labels

### 📍 Scenario
Penulis ingin melacak status penulisan setiap scene (Draft, Revision, Final).

```
📍 START: Plan View → Grid
    │
    ├─▶ Right-click scene card
    │   └─ Context menu opens
    │
    ├─▶ Click "Add Label"
    │   ├─ Label selector opens
    │   └─ Choose "Draft" (yellow)
    │
    ├─▶ Label applied
    │   └─ Yellow badge on card
    │
    ├─▶ Create custom label
    │   ├─ Open label manager
    │   ├─ Click "Create Label"
    │   ├─ Name: "Needs Research"
    │   ├─ Color: Blue
    │   └─ Save
    │
    ├─▶ Filter by label
    │   ├─ Select "Draft" in filter
    │   └─ Only draft scenes shown
    │
    └─▶ ✅ SUCCESS: Writing progress tracked
```

---

## Journey 7: Use Outline View for Quick Editing

### 📍 Scenario
Penulis ingin review dan edit scene summaries dengan cepat.

```
📍 START: Plan View
    │
    ├─▶ Click "Outline" di View Switcher
    │   └─ Hierarchical list view
    │
    ├─▶ Collapse/Expand acts
    │   ├─ Click Act header
    │   └─ Chapters toggle visibility
    │
    ├─▶ Inline edit summary
    │   ├─ Click scene summary text
    │   ├─ Edit mode activates
    │   ├─ Type new summary
    │   └─ Click away (blur) → auto-save
    │
    ├─▶ Add scene from outline
    │   ├─ Click "+" button di chapter
    │   └─ New scene added
    │
    └─▶ ✅ SUCCESS: Summaries updated quickly
```

---

## Journey 8: Customize Scene Card Appearance

### 📍 Scenario
Penulis ingin menyesuaikan tampilan scene cards untuk preferensi mereka.

```
📍 START: Plan View → Grid
    │
    ├─▶ Click Settings (gear icon)
    │   └─ Settings panel opens
    │
    ├─▶ Adjust card size
    │   ├─ Compact: More cards, less detail
    │   ├─ Normal: Balanced (default)
    │   └─ Large: Fewer cards, more detail
    │
    ├─▶ Toggle visible elements
    │   ├─ ☑️ Show Summary
    │   ├─ ☑️ Show Labels
    │   ├─ ☑️ Show Word Count
    │   ├─ ☐ Show POV
    │   └─ ☑️ Show Codex Mentions
    │
    ├─▶ Change grid axis
    │   ├─ Vertical: Chapters as columns
    │   └─ Horizontal: Chapters as rows
    │
    ├─▶ Close settings
    │   └─ Preferences saved (localStorage)
    │
    └─▶ ✅ SUCCESS: Plan view customized
```

---

## Journey 9: Archive and Restore Scenes

### 📍 Scenario
Penulis ingin menghapus scene tanpa kehilangan konten secara permanen.

```
📍 START: Grid View dengan scene untuk archive
    │
    ├─▶ Right-click scene card
    │   └─ Context menu
    │
    ├─▶ Click "Archive Scene"
    │   ├─ Confirmation dialog
    │   └─ Scene archived
    │
    ├─▶ Scene disappears dari grid
    │   └─ Toast: "Scene archived"
    │
    ├─▶ Access archived scenes
    │   ├─ Click chapter's archive icon
    │   └─ Archived scenes list
    │
    ├─▶ Restore scene
    │   ├─ Find archived scene
    │   ├─ Click "Restore"
    │   └─ Scene back in chapter
    │
    └─▶ ✅ SUCCESS: Scene safely archived/restored
```

---

## Journey 10: Duplicate Scene

### 📍 Scenario
Penulis ingin membuat copy dari scene untuk variasi atau backup.

```
📍 START: Grid View
    │
    ├─▶ Right-click scene card
    │   └─ Context menu
    │
    ├─▶ Click "Duplicate Scene"
    │   └─ Scene copied
    │
    ├─▶ New scene created
    │   ├─ Title: "[Original] (Copy)"
    │   ├─ Same summary
    │   ├─ Same content
    │   └─ Position: After original
    │
    ├─▶ Edit duplicate
    │   └─ Modify untuk variation
    │
    └─▶ ✅ SUCCESS: Scene duplicated
```

---

## Mobile User Journey

### Touch-Optimized Interactions

```
📍 Mobile Device (375px width)
    │
    ├─▶ Plan View Layout
    │   ├─ Single column cards
    │   ├─ Stacked vertically
    │   └─ Full-width cards
    │
    ├─▶ View Switcher
    │   └─ Horizontal scroll if needed
    │
    ├─▶ Context Menu
    │   ├─ Long-press card
    │   └─ Bottom sheet opens
    │
    ├─▶ Drag & Drop
    │   ├─ Touch and hold
    │   ├─ Drag to new position
    │   └─ Release to drop
    │
    └─▶ ✅ SUCCESS: Full functionality on mobile
```

---

## Error Handling Journeys

### Delete Act with Chapters

```
📍 User tries to delete Act with existing chapters
    │
    ├─▶ Right-click Act → Delete
    │   └─ Error dialog
    │
    ├─▶ Message: "Cannot delete act with chapters"
    │   └─ Suggest: Move or delete chapters first
    │
    └─▶ Act remains (protected)
```

### Network Error During Save

```
📍 Drag & drop fails due to network
    │
    ├─▶ Drop scene
    │   └─ API fails
    │
    ├─▶ Toast: "Failed to save. Retrying..."
    │   └─ Auto-retry
    │
    ├─▶ If retry fails
    │   ├─ Toast: "Changes saved locally"
    │   └─ Queue untuk sync later
    │
    └─▶ Scene returns to original position if fatal
```

---

## Quick Reference: Navigation Shortcuts

| Action | Grid | Matrix | Outline |
|--------|------|--------|---------|
| Click card | Edit scene | - | Edit scene |
| Double-click | Inline edit summary | - | Inline edit |
| Right-click | Context menu | Context menu | Context menu |
| Drag | Reorder | - | Reorder |
| Click cell | - | Toggle/Set | - |
| ESC | Cancel edit | Close modal | Cancel edit |
| Enter | Save edit | Confirm | Save edit |

---

*Last Updated: 2026-01-02*
