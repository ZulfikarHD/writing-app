# ⚙️ EPIC 11: System & Productivity Features

**Epic ID:** EPIC-11  
**Priority:** 🟢 Medium  
**Total Story Points:** ~35  
**Est. Duration:** 2-3 Sprints  
**Dependencies:** Foundation (Sprint 1-2)

---

## 📋 Epic Description

Build system-wide features and productivity tools including global search, pinning, theme customization, keyboard shortcuts, statistics, and other features that improve the overall user experience across the application.

**Reference:** [Novelcrafter App Layout](https://www.novelcrafter.com/help/docs/app/app-layout) & [Pinning](https://www.novelcrafter.com/help/docs/app/pinning)

---

## 🎯 Epic Goals

1. Global search across all content
2. Pinning panels for quick access
3. Theme and appearance customization
4. Keyboard shortcuts
5. Word statistics and writing goals
6. Settings export/import
7. Help and documentation

---

## 📑 Feature Groups

### FG-11.1: Search & Navigation

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-11.1.1 | Global Search | 🔴 Critical | 5 |
| F-11.1.2 | Pinning Feature | 🟡 High | 3 |
| F-11.1.3 | Collapsible UI Panels | 🟡 High | 3 |

### FG-11.2: Customization

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-11.2.1 | Theme & Appearance Settings | 🟡 High | 5 |
| F-11.2.2 | App Layout Customization | 🟢 Medium | 3 |
| F-11.2.3 | Settings Export/Import | 🟢 Medium | 3 |

### FG-11.3: Productivity

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-11.3.1 | Word Statistics Dashboard | 🟡 High | 5 |
| F-11.3.2 | Keyboard Shortcuts | 🟡 High | 3 |
| F-11.3.3 | Focus Mode | 🟢 Medium | 3 |

### FG-11.4: Help & Support

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-11.4.1 | Help & Documentation | 🟢 Medium | 3 |
| F-11.4.2 | Onboarding Experience | 🟡 High | 5 |

---

## 📝 Detailed User Stories

### US-11.1: Global Search
**Priority:** 🔴 Critical | **Points:** 5

**As a** writer,  
**I want to** search across all content,  
**So that** I can quickly find anything in my project.

#### Acceptance Criteria:
- [ ] Global search shortcut (Ctrl/Cmd + K)
- [ ] Search across scenes, Codex, chat, snippets
- [ ] Search results grouped by type
- [ ] Preview result content
- [ ] Navigate to result
- [ ] Recent searches
- [ ] Search filters

---

### US-11.2: Pinning Feature
**Priority:** 🟡 High | **Points:** 3

**As a** writer,  
**I want to** pin panels and content,  
**So that** I can keep frequently accessed items visible.

#### Acceptance Criteria:
- [ ] Pin chat panel
- [ ] Pin Codex entries
- [ ] Pin snippets panel
- [ ] Pinned items accessible across views
- [ ] Unpin option
- [ ] Pin limit (prevent clutter)

**Reference:** [Pinning](https://www.novelcrafter.com/help/docs/app/pinning)

---

### US-11.3: Collapsible UI Panels
**Priority:** 🟡 High | **Points:** 3

**As a** writer,  
**I want to** collapse UI panels,  
**So that** I can maximize writing space.

#### Acceptance Criteria:
- [ ] Collapse sidebar
- [ ] Collapse chat panel
- [ ] Collapse Codex panel
- [ ] Keyboard shortcuts for collapse
- [ ] Collapsed state persisted
- [ ] Quick expand on hover (optional)

---

### US-11.4: Theme & Appearance Settings
**Priority:** 🟡 High | **Points:** 5

**As a** writer,  
**I want to** customize the app appearance,  
**So that** I can write comfortably.

#### Acceptance Criteria:
- [ ] Light/Dark mode toggle
- [ ] System preference detection
- [ ] Font family selection (editor)
- [ ] Font size options
- [ ] Line height options
- [ ] Color accent customization
- [ ] Sepia mode option

---

### US-11.5: App Layout Customization
**Priority:** 🟢 Medium | **Points:** 3

**As a** writer,  
**I want to** customize the app layout,  
**So that** it fits my workflow.

#### Acceptance Criteria:
- [ ] Sidebar position (left/right)
- [ ] Panel sizes (resizable)
- [ ] Default view preferences
- [ ] Panel arrangement memory
- [ ] Reset to default option

**Reference:** [App Layout](https://www.novelcrafter.com/help/docs/app/app-layout)

---

### US-11.6: Settings Export/Import
**Priority:** 🟢 Medium | **Points:** 3

**As a** writer,  
**I want to** export and import settings,  
**So that** I can sync preferences across devices.

#### Acceptance Criteria:
- [ ] Export settings to JSON
- [ ] Import settings from file
- [ ] Include: theme, shortcuts, preferences
- [ ] Include: prompt personas, presets
- [ ] Selective import option

---

### US-11.7: Word Statistics Dashboard
**Priority:** 🟡 High | **Points:** 5

**As a** writer,  
**I want to** see word statistics,  
**So that** I can track my writing progress.

#### Acceptance Criteria:
- [ ] Total word count
- [ ] Word count by chapter/scene
- [ ] Daily/weekly/monthly word counts
- [ ] Writing streak tracking
- [ ] Word count goals
- [ ] Progress visualization (charts)
- [ ] Target word count setting

---

### US-11.8: Keyboard Shortcuts
**Priority:** 🟡 High | **Points:** 3

**As a** writer,  
**I want to** use keyboard shortcuts,  
**So that** I can work efficiently.

#### Acceptance Criteria:
- [ ] Comprehensive shortcut list
- [ ] Shortcuts reference panel
- [ ] Customizable shortcuts
- [ ] Platform-aware (Cmd vs Ctrl)
- [ ] Shortcuts for all major actions

---

### US-11.9: Focus Mode
**Priority:** 🟢 Medium | **Points:** 3

**As a** writer,  
**I want to** enter a distraction-free mode,  
**So that** I can focus on writing.

#### Acceptance Criteria:
- [ ] Fullscreen writing mode
- [ ] Hide all panels
- [ ] Typewriter mode (scroll line)
- [ ] Ambient sounds (optional)
- [ ] Easy exit (Esc key)
- [ ] Optional word count display

---

### US-11.10: Help & Documentation
**Priority:** 🟢 Medium | **Points:** 3

**As a** new user,  
**I want to** access help and documentation,  
**So that** I can learn how to use the app.

#### Acceptance Criteria:
- [ ] Help panel/modal
- [ ] Feature documentation
- [ ] Video tutorials links
- [ ] FAQ section
- [ ] Contact support option
- [ ] Contextual help tooltips

---

### US-11.11: Onboarding Experience
**Priority:** 🟡 High | **Points:** 5

**As a** new user,  
**I want to** be guided through the app,  
**So that** I can start writing quickly.

#### Acceptance Criteria:
- [ ] Welcome screen on first login
- [ ] Feature highlights tour
- [ ] First project creation wizard
- [ ] AI setup guidance
- [ ] Skip option
- [ ] Revisit tour option

---

## 🗄️ Database Schema

### Table: `user_settings`

```sql
CREATE TABLE user_settings (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    settings JSON NOT NULL, -- All user preferences
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY idx_user (user_id)
);
```

### Table: `writing_stats`

```sql
CREATE TABLE writing_stats (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    novel_id BIGINT UNSIGNED NULL,
    date DATE NOT NULL,
    words_written INT UNSIGNED DEFAULT 0,
    time_spent INT UNSIGNED DEFAULT 0, -- seconds
    sessions INT UNSIGNED DEFAULT 1,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (novel_id) REFERENCES novels(id) ON DELETE CASCADE,
    UNIQUE KEY idx_user_date (user_id, date, novel_id)
);
```

### Table: `writing_goals`

```sql
CREATE TABLE writing_goals (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    novel_id BIGINT UNSIGNED NULL,
    type ENUM('daily', 'weekly', 'monthly', 'total') NOT NULL,
    target_words INT UNSIGNED NOT NULL,
    current_words INT UNSIGNED DEFAULT 0,
    start_date DATE NULL,
    end_date DATE NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (novel_id) REFERENCES novels(id) ON DELETE CASCADE
);
```

---

## 🏗️ Technical Architecture

### Backend Structure

```
app/
├── Services/
│   └── System/
│       ├── SearchService.php
│       ├── StatsService.php
│       └── OnboardingService.php
├── Models/
│   ├── UserSetting.php
│   ├── WritingStat.php
│   └── WritingGoal.php
├── Http/
│   └── Controllers/
│       ├── SearchController.php
│       ├── SettingsController.php
│       └── StatsController.php
```

### Frontend Structure

```
resources/js/
├── Components/
│   └── System/
│       ├── GlobalSearch.vue
│       ├── PinManager.vue
│       ├── ThemeToggle.vue
│       ├── ThemeSettings.vue
│       ├── KeyboardShortcuts.vue
│       ├── FocusMode.vue
│       ├── StatsDashboard.vue
│       ├── GoalTracker.vue
│       ├── OnboardingWizard.vue
│       └── HelpPanel.vue
├── Composables/
│   └── useKeyboardShortcuts.ts
│   └── useTheme.ts
```

---

## 🔀 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/search` | Global search |
| GET | `/api/settings` | Get user settings |
| PATCH | `/api/settings` | Update settings |
| POST | `/api/settings/export` | Export settings |
| POST | `/api/settings/import` | Import settings |
| GET | `/api/stats` | Get writing stats |
| GET | `/api/stats/goals` | Get goals |
| POST | `/api/stats/goals` | Create goal |
| PATCH | `/api/stats/goals/{id}` | Update goal |
| POST | `/api/onboarding/complete` | Mark onboarding complete |

---

## ✅ Definition of Done

- [ ] Global search working across all content types
- [ ] Pinning feature functional
- [ ] Collapsible panels working
- [ ] Theme customization complete
- [ ] Keyboard shortcuts implemented
- [ ] Word statistics tracking
- [ ] Goals system working
- [ ] Focus mode functional
- [ ] Help documentation accessible
- [ ] Onboarding wizard complete
- [ ] Feature tests for all features

---

## 📎 References

- [App Layout](https://www.novelcrafter.com/help/docs/app/app-layout)
- [Pinning](https://www.novelcrafter.com/help/docs/app/pinning)
