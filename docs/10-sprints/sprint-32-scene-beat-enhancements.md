# 📦 Sprint 32: Scene Beat Feature & Writing Panel Enhancements

**Version:** 1.0.0  
**Date:** 2026-01-04  
**Duration:** 1 Sprint  
**Status:** ✅ Completed  
**Feature Group:** FG-06.2 (Enhancement)

---

## 📋 Sprint Goals

Sprint ini berfokus pada implementasi Scene Beat feature yang terintegrasi langsung di editor sebagai TipTap node, serta berbagai bug fixes dan UX improvements pada writing panel, yaitu:
1. Implementasi Scene Beat sebagai custom TipTap node dengan inline AI generation
2. Selection bubble menu untuk text replacement actions
3. Dark mode compatibility untuk writing panel
4. State management improvements untuk content persistence
5. UX enhancements pada sidebar dan UI components

---

## ✨ Features Implemented

### F-32.1: Scene Beat TipTap Node ✅

Scene Beat kini menjadi native TipTap extension yang dapat disisipkan langsung di editor, menggantikan beat sebagai section type terpisah.

**Key Features:**
- **Inline Beat Block**: Beat tampil sebagai purple block di editor dengan UI terintegrasi
- **AI Generation UI**: Generate prose langsung dari beat text dengan streaming output
- **Word Target Control**: Pilih target word count (200, 400, 600, atau custom)
- **Model Selection**: Pilih AI connection dan model untuk generation
- **Context Menu**: Actions untuk hide, delete, dan clear beat
- **Real-time Streaming**: Prose generation dengan SSE streaming (Server-Sent Events)
- **Inline Replacement**: Generated prose menggantikan beat block secara inline

**Implementation Details:**
```typescript
// SceneBeatNode.ts - TipTap custom node
- Node type: 'sceneBeat'
- Properties: beat text, isCollapsed, generationMetadata
- Commands: insertSceneBeat, replaceSceneBeatWithContent, removeSceneBeat
- Vue component renderer: SceneBeatNodeView.vue
```

**User Flow:**
1. User types `/` di editor → pilih "Scene Beat"
2. Beat block muncul dengan textarea untuk input
3. User mendeskripsikan scene (contoh: "John enters the dark room")
4. User memilih word target dan model (optional)
5. Klik "Generate" → AI generates prose dengan streaming
6. Generated prose menggantikan beat block di posisi yang sama

### F-32.2: Selection Bubble Menu ✅

Implementasi floating menu yang muncul saat user menyeleksi text di editor untuk quick actions.

**Key Features:**
- **Text Selection Detection**: Otomatis muncul saat text di-highlight
- **Quick Actions**: Format text, replace dengan AI, atau transform ke section
- **Responsive Positioning**: Menu menyesuaikan posisi berdasarkan selection location
- **Integration dengan Text Replacement**: Langsung connect ke text replacement flow

**Supported Actions:**
- Bold, Italic, Underline formatting
- Replace dengan AI (expand, rephrase, shorten)
- Convert to Section
- Highlight dengan warna

### F-32.3: Generated Section Header ✅

UI component baru untuk menampilkan section yang di-generate oleh AI dengan metadata generation.

**Key Features:**
- **Visual Indicator**: Distinct styling untuk AI-generated sections
- **Generation Metadata**: Tampilkan info model, word target, original beat
- **Action Menu**: Regenerate, edit, atau convert back to beat

### F-32.4: Dark Mode Text Color Fix ✅

Fixed issue dimana text di writing panel tetap hitam saat dark mode aktif.

**Changes:**
- Added `text-zinc-900 dark:text-zinc-100` classes ke TipTapEditor
- Ensured `prose-invert` working correctly untuk dark mode
- Improved contrast untuk better readability

### F-32.5: Sidebar Text Size Enhancement ✅

Increased sidebar text size untuk better readability, especially di mobile devices.

**Changes:**
- SidebarToolSection title: `text-sm` → `text-base`
- Improved font weight dan spacing
- Better hierarchy untuk section titles

### F-32.6: Write Panel State Management Fix ✅

Fixed critical issue dimana edited content di write panel kembali ke state lama saat user switch ke panel lain (chat, plan) lalu kembali ke write panel.

**Root Cause:**
- WritePanel component di-unmount saat mode berubah
- Saat remount, content diinit dari `props.scene.content` yang tidak ter-refresh
- Auto-save berhasil save ke backend, tapi UI menampilkan stale data

**Solution:**
- Refetch activeScene data via Inertia saat mode berubah ke 'write'
- Preserve visit state dengan `preserveScroll: true, preserveState: true`
- Ensure WritePanel selalu mount dengan fresh data dari server

**Implementation:**
```typescript
// Workspace/Index.vue
const handleModeChange = (newMode: WorkspaceMode) => {
    setMode(newMode);
    
    // Refetch scene data saat kembali ke write mode
    if (newMode === 'write' && currentScene.value) {
        router.visit(route('workspace', { 
            scene: currentScene.value.id 
        }), {
            only: ['activeScene'], // Only refetch activeScene prop
            preserveScroll: true,
            preserveState: true,
            replace: true,
        });
    }
    
    updateUrl();
};
```

### F-32.7: ModelSelector Auto-Fetch Fix ✅

Fixed issue dimana available models tidak otomatis di-fetch saat default AI connection dipilih.

**Changes:**
- ModelSelector.vue sekarang explicitly call `fetchModels()` setelah set default connection
- Ensure models list populated sebelum user interaction

### F-32.8: Prose Generation Composable Refactor ✅

Refactored `useProseGeneration.ts` untuk avoid dependency dengan Wayfinder yang causing runtime errors.

**Changes:**
- Direct URL usage: `/api/prose-generation/options` dan `/api/scenes/${sceneId}/generate-prose`
- Removed dependency pada Wayfinder-generated action functions
- Pattern matching dengan `useChat.ts` yang sudah proven working
- Proper CSRF token handling

---

## 📁 File Structure

### Backend Files

```
app/
├── Http/Controllers/
│   └── ProseGenerationController.php      ✏️ UPDATED - Support scene_beat mode dengan beat param
│
└── Services/Editor/
    └── ProseGenerationService.php         ✏️ UPDATED - Enhanced message building untuk beats

routes/
└── spa-api.php                             ✏️ UPDATED - Existing prose routes
```

### Frontend Files

```
resources/js/
├── extensions/
│   ├── SceneBeatNode.ts                   ✨ NEW - TipTap custom node untuk beat blocks
│   ├── SlashCommands.ts                   ✏️ UPDATED - Added Scene Beat command
│   └── SectionNode.ts                     ✏️ UPDATED - Enhanced untuk support generated sections
│
├── components/editor/
│   ├── SceneBeatEditor.vue                ✨ NEW - Main UI untuk beat editing & generation
│   ├── SceneBeatNodeView.vue              ✨ NEW - Vue renderer untuk SceneBeatNode
│   ├── SelectionBubbleMenu.vue            ✨ NEW - Floating menu on text selection
│   ├── GeneratedSectionHeader.vue         ✨ NEW - Header untuk AI-generated sections
│   ├── TipTapEditor.vue                   ✏️ UPDATED - Dark mode text fix
│   ├── SectionHeader.vue                  ✏️ UPDATED - Integration dengan generated sections
│   ├── SectionMenu.vue                    ✏️ UPDATED - Enhanced menu actions
│   ├── SectionNodeView.vue                ✏️ UPDATED - Support generated section rendering
│   └── SlashCommandsList.vue              ✏️ UPDATED - Added Scene Beat in AI section
│
├── components/workspace/
│   ├── WritePanel.vue                     ✏️ UPDATED - Props passing untuk sceneId
│   ├── SidebarToolSection.vue             ✏️ UPDATED - Text size enhancement (text-sm → text-base)
│   ├── WorkspaceSidebar.vue               ✏️ UPDATED - Minor style improvements
│   ├── CodexQuickList.vue                 ✏️ UPDATED - Minor style improvements
│   └── PromptsQuickList.vue               ✏️ UPDATED - Minor style improvements
│
├── components/ai/
│   └── ModelSelector.vue                  ✏️ UPDATED - Auto-fetch models on default connection
│
├── composables/
│   ├── useProseGeneration.ts              ✏️ UPDATED - Direct URL usage, removed Wayfinder dependency
│   └── useTextReplacement.ts              ✏️ UPDATED - Minor improvements
│
└── pages/
    ├── Editor/Index.vue                   ✏️ UPDATED - Minor improvements
    └── Workspace/Index.vue                ✏️ UPDATED - State management fix untuk write panel
```

---

## 🔌 API Endpoints Summary

Menggunakan existing endpoints dari Sprint 31, tanpa perubahan API contract:

| Method | Endpoint | Description | Controller@Method |
|--------|----------|-------------|-------------------|
| GET | `/api/prose-generation/options` | Get AI connections & prompts | `ProseGenerationController@options` |
| POST | `/api/scenes/{scene}/generate-prose` | Generate prose dengan streaming SSE | `ProseGenerationController@generate` |

**Request Payload Enhancement:**
```json
{
    "mode": "scene_beat",
    "beat": "John enters the dark room, his heart racing",
    "connection_id": 1,
    "model": "gpt-4",
    "max_tokens": 800
}
```

---

## 🧪 Testing Summary

### Manual Testing Checklist

- [x] Scene Beat insertion via `/` slash command
- [x] Beat text input dan editing
- [x] Word target selection (200, 400, 600, custom)
- [x] AI model selection via dropdown
- [x] Generate button enabled only saat beat text filled
- [x] Streaming prose generation dengan visual feedback
- [x] Prose replacement inline di editor
- [x] Beat collapse/expand functionality
- [x] Beat delete dan clear actions
- [x] Dark mode text visibility di writing panel
- [x] Sidebar text size improvement visible
- [x] Content persistence saat switch panel write → chat → write
- [x] Auto-save working correctly
- [x] Selection bubble menu muncul on text highlight
- [x] Text replacement actions working dari selection menu
- [x] Generated section header displaying metadata
- [x] Mobile responsive behavior

### Browser Testing

**Tested on:**
- ✅ Chrome/Edge (Chromium)
- ✅ Dark mode & Light mode
- ✅ Desktop & Mobile viewport

**Known Issues:**
- None reported in current sprint

---

## 🔗 Related Documentation

- **API Reference:** [AI Writing Features API](../04-api-reference/ai-writing-features.md)
- **Testing Guide:** [AI Writing Features Testing](../06-testing/ai-writing-features-testing.md)
- **User Journeys:** [Prose Generation Flow](../07-user-journeys/ai-writing-features/prose-generation-flow.md)
- **Previous Sprint:** [Sprint 31 - AI Writing Features](./sprint-31-ai-writing-features.md)

---

## 📊 Key Improvements

### Before Sprint 32
- ❌ Scene beats hanya tersedia sebagai section type terpisah
- ❌ Generate prose flow tidak inline, requires navigation
- ❌ Text di writing panel tidak visible di dark mode
- ❌ Sidebar text terlalu kecil di mobile
- ❌ Content loss saat switch antara workspace panels
- ❌ Manual model selection required setiap kali
- ❌ No quick text selection actions

### After Sprint 32
- ✅ Scene beats native di editor sebagai TipTap node
- ✅ Inline AI generation dengan streaming real-time
- ✅ Perfect dark mode compatibility
- ✅ Better sidebar readability
- ✅ Reliable content persistence across panel switches
- ✅ Smart defaults untuk AI model selection
- ✅ Quick actions via selection bubble menu

---

## 🐛 Bug Fixes Completed

### BUG-32.1: Dark Mode Text Color ✅
**Issue:** Text di writing panel tetap hitam (tidak visible) saat dark mode aktif  
**Root Cause:** Missing explicit text color classes untuk dark mode di TipTapEditor  
**Fix:** Added `text-zinc-900 dark:text-zinc-100` classes ke editor attributes  
**Files:** `TipTapEditor.vue`

### BUG-32.2: Write Panel Content Reversion ✅
**Issue:** Edited content kembali ke state lama saat switch panel  
**Root Cause:** WritePanel component unmount/remount dengan stale props  
**Fix:** Refetch activeScene via Inertia saat mode change ke 'write'  
**Files:** `Workspace/Index.vue`

### BUG-32.3: ModelSelector No Auto-Fetch ✅
**Issue:** Available models tidak di-fetch saat default connection selected  
**Root Cause:** Missing explicit `fetchModels()` call after default selection  
**Fix:** Call `fetchModels()` explicitly di ModelSelector mounted hook  
**Files:** `ModelSelector.vue`

### BUG-32.4: Prose Generation Wayfinder Error ✅
**Issue:** `Cannot read properties of undefined (reading 'definition')` error  
**Root Cause:** Dependency pada Wayfinder-generated actions yang broken  
**Fix:** Use direct URLs matching `useChat.ts` pattern  
**Files:** `useProseGeneration.ts`

---

## 💡 Technical Highlights

### 1. TipTap Custom Node Architecture

Scene Beat implemented sebagai custom TipTap node dengan:
- **Atom node**: Treated sebagai single unit, tidak bisa split
- **Vue renderer**: Full Vue component dengan reactive state
- **Custom commands**: Typed commands untuk insert, replace, remove
- **Metadata storage**: Generation info stored dalam node attributes

### 2. Server-Sent Events (SSE) Streaming

Prose generation menggunakan SSE untuk real-time streaming:
```php
// ProseGenerationController.php
return response()->stream(function () use ($scene, $validated) {
    foreach ($this->proseService->generate($scene, $user, $validated) as $chunk) {
        echo 'data: '.json_encode($chunk)."\n\n";
        flush();
    }
}, 200, [
    'Content-Type' => 'text/event-stream',
    'Cache-Control' => 'no-cache',
    'Connection' => 'keep-alive',
]);
```

### 3. State Management Pattern

Workspace content persistence menggunakan selective Inertia refetch:
```typescript
router.visit(route('workspace', { scene: sceneId }), {
    only: ['activeScene'],      // Only refetch specific prop
    preserveScroll: true,        // Maintain scroll position
    preserveState: true,         // Keep other state intact
    replace: true,               // Don't add history entry
});
```

---

## 📝 Developer Notes

### Adding New Beat Commands
Untuk menambah commands ke Scene Beat:
1. Update `SceneBeatNode.ts` → `addCommands()`
2. Implement command logic dengan editor chain
3. Update TypeScript types di `Commands<ReturnType>` interface
4. Add UI button/action di `SceneBeatEditor.vue`

### Extending AI Generation Options
Untuk menambah generation options:
1. Backend: Update `ProseGenerationService::buildMessages()`
2. Frontend: Update `SceneBeatEditor.vue` options UI
3. Add validation di `ProseGenerationController::generate()`
4. Update API documentation

### Dark Mode Guidelines
Untuk ensure dark mode compatibility:
- Always use `dark:` variant untuk text colors
- Test dengan both light and dark mode
- Use semantic color classes (`text-zinc-900 dark:text-zinc-100`)
- Check contrast ratios meet accessibility standards

---

## 🎯 Success Metrics

- ✅ Scene Beat adoption: Writers dapat insert dan generate beats inline di editor
- ✅ Zero content loss: No reported issues dengan content persistence after fix
- ✅ Dark mode usability: 100% text visibility di dark mode
- ✅ Mobile UX: Better readability dengan increased sidebar text size
- ✅ Generation reliability: SSE streaming working consistently
- ✅ Developer experience: Reduced debugging time dengan direct URL usage

---

*Last Updated: 2026-01-04*  
*Sprint Duration: 1 Sprint*  
*Team: Zulfikar Hidayatullah*
