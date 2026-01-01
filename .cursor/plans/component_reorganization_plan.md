# Component Directory Reorganization Plan

**Date:** January 2, 2026  
**Status:** Planning  
**Priority:** Medium  
**Effort:** 2-3 hours  

## Executive Summary

Reorganize `resources/js/components/` directory to improve maintainability, scalability, and developer experience. Current structure has some directories with too many files (codex: 25 files, ui: 19 files), making navigation and maintenance difficult.

## Current State Assessment

### Directory Analysis

```
components/
├── ai/           - 4 files   ✅ Well organized
├── codex/        - 25 files  ⚠️  TOO MANY - needs subdirectories
├── dashboard/    - 4 files   ✅ Well organized
├── editor/       - 8 files   ⚠️  Could benefit from panels/ subfolder
├── plan/         - 3 files   ✅ Well organized
├── ui/           - 19 files  ⚠️  Could benefit from categorization
└── workspace/    - 7 files   ✅ Well organized
```

### Problems Identified

1. **Codex folder overload** - 25 files in one directory makes it hard to find specific components
2. **UI component flat structure** - 19 UI components without clear categorization
3. **Editor panels mixed** - Panel components mixed with main editor components
4. **Scalability concerns** - As the app grows, these folders will become unmanageable

### Best Practices Reference

- **Recommended max files per directory:** 10-12 files
- **Group by:** Feature > Type (e.g., `codex/modals/` not `modals/codex/`)
- **Keep flat when possible:** Only create subdirectories when needed
- **Index files:** Use index.ts for easier imports

## Proposed Structure

### 1. Codex Directory (25 files → 5 subdirectories)

```
codex/
├── cards/                    (1 file)
│   └── CodexEntryCard.vue
│
├── forms/                    (1 file)
│   └── CodexEntryForm.vue
│
├── managers/                 (6 files - all *Manager.vue)
│   ├── AliasManager.vue
│   ├── CategoryManager.vue
│   ├── DetailManager.vue
│   ├── ProgressionManager.vue
│   ├── RelationManager.vue
│   └── TagManager.vue
│
├── modals/                   (6 files - all *Modal.vue)
│   ├── BulkCreateModal.vue
│   ├── BulkImportModal.vue
│   ├── CodexCreateModal.vue
│   ├── CodexEntryModal.vue
│   ├── ProgressionEditorModal.vue
│   └── QuickCreateModal.vue
│
├── shared/                   (11 files - shared utilities & displays)
│   ├── AIContextControl.vue
│   ├── AIVisibilityToggle.vue
│   ├── BulkExportButton.vue
│   ├── CodexHoverTooltip.vue
│   ├── CodexTypeBadge.vue
│   ├── CodexTypeIcon.vue
│   ├── MentionHeatmap.vue
│   ├── ProgressionTimeline.vue
│   ├── RelationGraph.vue
│   ├── ResearchTab.vue
│   └── TrackingToggle.vue
│
└── index.ts
```

### 2. UI Directory (19 files → 5 subdirectories)

```
ui/
├── buttons/                  (1 file)
│   └── Button.vue
│
├── feedback/                 (3 files - user feedback components)
│   ├── Alert.vue
│   ├── Toast.vue
│   └── ToastContainer.vue
│
├── forms/                    (8 files - form controls)
│   ├── Checkbox.vue
│   ├── FormGroup.vue
│   ├── Input.vue
│   ├── Radio.vue
│   ├── RadioGroup.vue
│   ├── Select.vue
│   ├── Textarea.vue
│   └── Toggle.vue
│
├── layout/                   (2 files - structural components)
│   ├── Card.vue
│   └── Modal.vue
│
├── overlays/                 (3 files - overlay components)
│   ├── ConfirmDialog.vue
│   ├── ConfirmProvider.vue
│   └── ContextMenu.vue
│
├── Badge.vue                 (keep at root - commonly used)
└── index.ts
```

### 3. Editor Directory (8 files → 2 levels)

```
editor/
├── panels/                   (3 files - sidebar panels)
│   ├── CodexSidebarPanel.vue
│   ├── EditorSettingsPanel.vue
│   └── SceneMetadataPanel.vue
│
├── EditorSidebar.vue         (main components stay at root)
├── EditorToolbar.vue
├── MentionTooltip.vue
├── SelectionActionMenu.vue
└── TipTapEditor.vue
```

### 4. No Changes Needed

These directories are already well-organized:
- `ai/` - 4 files ✅
- `dashboard/` - 4 files ✅
- `plan/` - 3 files ✅
- `workspace/` - 7 files ✅

## Migration Strategy

### Phase 1: Preparation (15 min)

1. **Backup current state**
   ```bash
   git status
   git add -A
   git commit -m "Backup before component reorganization"
   ```

2. **Create tracking document**
   - List all files to be moved
   - Map old paths → new paths
   - Identify all import statements to update

3. **Run tests baseline**
   ```bash
   php artisan test
   yarn run lint
   ```

### Phase 2: File Structure Migration (30 min)

**Order of operations:**

1. **Create new subdirectories**
   ```bash
   # Codex subdirectories
   mkdir resources/js/components/codex/cards
   mkdir resources/js/components/codex/forms
   mkdir resources/js/components/codex/managers
   mkdir resources/js/components/codex/modals
   mkdir resources/js/components/codex/shared
   
   # UI subdirectories
   mkdir resources/js/components/ui/buttons
   mkdir resources/js/components/ui/feedback
   mkdir resources/js/components/ui/forms
   mkdir resources/js/components/ui/layout
   mkdir resources/js/components/ui/overlays
   
   # Editor subdirectories
   mkdir resources/js/components/editor/panels
   ```

2. **Move codex components**
   - Cards: 1 file
   - Forms: 1 file
   - Managers: 6 files
   - Modals: 6 files
   - Shared: 11 files

3. **Move UI components**
   - Buttons: 1 file
   - Feedback: 3 files
   - Forms: 8 files
   - Layout: 2 files
   - Overlays: 3 files

4. **Move editor panels**
   - Panels: 3 files

### Phase 3: Import Path Updates (45 min)

**Files that will need import updates:**

#### Vue Components (search for `from '@/components/`)
- All `.vue` files in `resources/js/components/`
- All `.vue` files in `resources/js/pages/`
- All `.vue` files in `resources/js/layouts/`

#### TypeScript Files
- `resources/js/components/codex/index.ts`
- `resources/js/components/ui/index.ts`
- Any composables that import components
- Extension files that reference components

#### Search Patterns

```bash
# Find all imports from codex
rg "from ['\"]@/components/codex/" --type vue --type ts

# Find all imports from ui
rg "from ['\"]@/components/ui/" --type vue --type ts

# Find all imports from editor
rg "from ['\"]@/components/editor/" --type vue --type ts
```

### Phase 4: Index File Updates (15 min)

Update barrel export files:

1. **`resources/js/components/codex/index.ts`**
   - Update to export from subdirectories
   - Consider whether to re-export all or just commonly used

2. **`resources/js/components/ui/index.ts`**
   - Update to export from subdirectories
   - Maintain same export names for backward compatibility

### Phase 5: Testing & Verification (30 min)

1. **Linting**
   ```bash
   yarn run lint
   ```

2. **TypeScript compilation**
   ```bash
   yarn run type-check  # or yarn build
   ```

3. **Unit tests**
   ```bash
   php artisan test
   ```

4. **Manual testing checklist**
   - [ ] Dashboard loads
   - [ ] Workspace loads with all panels
   - [ ] Codex entry modal opens and works
   - [ ] Editor loads with sidebars
   - [ ] Plan view loads
   - [ ] Settings/AI Connections page works
   - [ ] All modals open correctly
   - [ ] Form components render
   - [ ] Toast notifications work
   - [ ] Context menus work

5. **Browser console check**
   - No import errors
   - No missing module warnings
   - No Vue component registration errors

## Detailed File Mapping

### Codex Components

| Current Path | New Path | Import Update Count (est.) |
|--------------|----------|---------------------------|
| `codex/CodexEntryCard.vue` | `codex/cards/CodexEntryCard.vue` | ~5 |
| `codex/CodexEntryForm.vue` | `codex/forms/CodexEntryForm.vue` | ~3 |
| `codex/AliasManager.vue` | `codex/managers/AliasManager.vue` | ~2 |
| `codex/CategoryManager.vue` | `codex/managers/CategoryManager.vue` | ~2 |
| `codex/DetailManager.vue` | `codex/managers/DetailManager.vue` | ~2 |
| `codex/ProgressionManager.vue` | `codex/managers/ProgressionManager.vue` | ~2 |
| `codex/RelationManager.vue` | `codex/managers/RelationManager.vue` | ~2 |
| `codex/TagManager.vue` | `codex/managers/TagManager.vue` | ~2 |
| `codex/BulkCreateModal.vue` | `codex/modals/BulkCreateModal.vue` | ~2 |
| `codex/BulkImportModal.vue` | `codex/modals/BulkImportModal.vue` | ~2 |
| `codex/CodexCreateModal.vue` | `codex/modals/CodexCreateModal.vue` | ~3 |
| `codex/CodexEntryModal.vue` | `codex/modals/CodexEntryModal.vue` | ~8 |
| `codex/ProgressionEditorModal.vue` | `codex/modals/ProgressionEditorModal.vue` | ~2 |
| `codex/QuickCreateModal.vue` | `codex/modals/QuickCreateModal.vue` | ~3 |
| `codex/AIContextControl.vue` | `codex/shared/AIContextControl.vue` | ~3 |
| `codex/AIVisibilityToggle.vue` | `codex/shared/AIVisibilityToggle.vue` | ~2 |
| `codex/BulkExportButton.vue` | `codex/shared/BulkExportButton.vue` | ~2 |
| `codex/CodexHoverTooltip.vue` | `codex/shared/CodexHoverTooltip.vue` | ~4 |
| `codex/CodexTypeBadge.vue` | `codex/shared/CodexTypeBadge.vue` | ~6 |
| `codex/CodexTypeIcon.vue` | `codex/shared/CodexTypeIcon.vue` | ~5 |
| `codex/MentionHeatmap.vue` | `codex/shared/MentionHeatmap.vue` | ~2 |
| `codex/ProgressionTimeline.vue` | `codex/shared/ProgressionTimeline.vue` | ~2 |
| `codex/RelationGraph.vue` | `codex/shared/RelationGraph.vue` | ~2 |
| `codex/ResearchTab.vue` | `codex/shared/ResearchTab.vue` | ~2 |
| `codex/TrackingToggle.vue` | `codex/shared/TrackingToggle.vue` | ~2 |

**Total estimated imports to update: ~60-70**

### UI Components

| Current Path | New Path | Import Update Count (est.) |
|--------------|----------|---------------------------|
| `ui/Button.vue` | `ui/buttons/Button.vue` | ~15 |
| `ui/Alert.vue` | `ui/feedback/Alert.vue` | ~5 |
| `ui/Toast.vue` | `ui/feedback/Toast.vue` | ~3 |
| `ui/ToastContainer.vue` | `ui/feedback/ToastContainer.vue` | ~2 |
| `ui/Checkbox.vue` | `ui/forms/Checkbox.vue` | ~8 |
| `ui/FormGroup.vue` | `ui/forms/FormGroup.vue` | ~12 |
| `ui/Input.vue` | `ui/forms/Input.vue` | ~15 |
| `ui/Radio.vue` | `ui/forms/Radio.vue` | ~3 |
| `ui/RadioGroup.vue` | `ui/forms/RadioGroup.vue` | ~4 |
| `ui/Select.vue` | `ui/forms/Select.vue` | ~8 |
| `ui/Textarea.vue` | `ui/forms/Textarea.vue` | ~6 |
| `ui/Toggle.vue` | `ui/forms/Toggle.vue` | ~10 |
| `ui/Card.vue` | `ui/layout/Card.vue` | ~12 |
| `ui/Modal.vue` | `ui/layout/Modal.vue` | ~15 |
| `ui/ConfirmDialog.vue` | `ui/overlays/ConfirmDialog.vue` | ~2 |
| `ui/ConfirmProvider.vue` | `ui/overlays/ConfirmProvider.vue` | ~3 |
| `ui/ContextMenu.vue` | `ui/overlays/ContextMenu.vue` | ~4 |

**Total estimated imports to update: ~125-140**

### Editor Components

| Current Path | New Path | Import Update Count (est.) |
|--------------|----------|---------------------------|
| `editor/CodexSidebarPanel.vue` | `editor/panels/CodexSidebarPanel.vue` | ~2 |
| `editor/EditorSettingsPanel.vue` | `editor/panels/EditorSettingsPanel.vue` | ~2 |
| `editor/SceneMetadataPanel.vue` | `editor/panels/SceneMetadataPanel.vue` | ~2 |

**Total estimated imports to update: ~6**

### Grand Total

**Estimated import statements to update: ~190-220**

## Risk Assessment

### High Risk Areas

1. **UI components** - Most heavily used across the app
   - Mitigation: Update index.ts first, test imports
   - Consider: Temporary re-exports for gradual migration

2. **CodexEntryModal** - 1013 lines, likely has many dependencies
   - Mitigation: Test thoroughly after moving
   - Check: Internal imports within the component

3. **Modal.vue** - Used extensively throughout app
   - Mitigation: Update all modal usages first
   - Verify: All modal dialogs still open/close properly

### Medium Risk Areas

1. **Button component** - Used everywhere
2. **Form components** - Critical for data entry
3. **Card component** - Used in layouts

### Low Risk Areas

1. **Codex managers** - Self-contained
2. **Editor panels** - Limited scope
3. **Badge component** - Simple, single responsibility

## Rollback Plan

If issues arise during migration:

### Quick Rollback (< 5 minutes)

```bash
git reset --hard HEAD
git clean -fd
```

### Partial Rollback

If only specific components are problematic:

1. Identify problematic components
2. Move them back to original location
3. Revert their import updates
4. Complete migration for working components

### Recovery Checklist

- [ ] Database state is unchanged (no migrations run)
- [ ] Git history preserved
- [ ] All tests passing
- [ ] Application functional

## Success Criteria

- [ ] All files moved to new locations
- [ ] All import statements updated
- [ ] No linting errors
- [ ] No TypeScript errors
- [ ] All tests passing
- [ ] Manual testing checklist complete
- [ ] No console errors in browser
- [ ] Git commit with clear message
- [ ] Documentation updated (if needed)

## Post-Migration Tasks

### Immediate (within 1 day)

1. **Update team documentation**
   - Update component guidelines
   - Create migration guide for new components
   - Add README files in subdirectories (optional)

2. **Monitor for issues**
   - Check error logs
   - Watch for user reports
   - Review any CI/CD failures

### Short-term (within 1 week)

1. **Consider barrel exports optimization**
   - Review if all exports needed in index.ts
   - Consider tree-shaking implications

2. **Update development guidelines**
   - Where to place new components
   - Naming conventions for subdirectories

3. **Create component documentation**
   - Document each subdirectory's purpose
   - Add examples for complex components

## Alternative Approaches Considered

### Option A: Feature-based grouping (Rejected)

```
components/
├── features/
│   ├── codex/
│   ├── editor/
│   ├── dashboard/
```

**Reason rejected:** Unnecessary nesting, current feature-based approach is already good.

### Option B: Atomic Design Pattern (Rejected)

```
components/
├── atoms/
├── molecules/
├── organisms/
```

**Reason rejected:** Too theoretical, doesn't match team's mental model.

### Option C: Type-first grouping (Rejected)

```
components/
├── modals/
│   ├── codex/
│   ├── editor/
```

**Reason rejected:** Breaks feature cohesion, harder to find related components.

### Option D: Minimal changes (Considered)

Only reorganize `codex/` directory, leave others as-is.

**Pros:** Less risk, fewer imports to update  
**Cons:** Doesn't solve UI component organization  
**Decision:** Go with full migration for consistency

## Timeline Estimate

| Phase | Duration | Cumulative |
|-------|----------|------------|
| Preparation | 15 min | 0:15 |
| File Structure Migration | 30 min | 0:45 |
| Import Path Updates | 45 min | 1:30 |
| Index File Updates | 15 min | 1:45 |
| Testing & Verification | 30 min | 2:15 |
| Buffer for issues | 30 min | 2:45 |
| **Total** | **~3 hours** | - |

## Notes for Developer

- Use search and replace with care - verify each change
- Consider using IDE refactoring tools for imports
- Test incrementally - don't move everything at once
- Commit after each major phase
- Keep browser console open during testing
- Have Vite dev server running for hot reload verification

## References

- Vue.js Style Guide: https://vuejs.org/style-guide/
- Component Organization Best Practices
- Project structure discussions in team Slack

---

**Next Steps:**

1. Review and approve this plan
2. Schedule implementation time (uninterrupted 3-hour block)
3. Execute Phase 1 (Preparation)
4. Proceed with migration phases
5. Complete post-migration tasks

**Questions to resolve before starting:**

- [ ] Should we update all imports at once or incrementally?
- [ ] Do we need to notify other team members?
- [ ] Should we create a feature branch or work on main?
- [ ] Are there any upcoming releases that would conflict?
