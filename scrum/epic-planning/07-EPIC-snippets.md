# 📝 EPIC 7: Snippets

**Epic ID:** EPIC-07  
**Priority:** 🟡 High  
**Total Story Points:** ~25  
**Est. Duration:** 1-2 Sprints  
**Dependencies:** EPIC-04 (Workshop Chat)

---

## 📋 Epic Description

Build a snippets system that allows writers to save, organize, and reuse text fragments, AI-generated content, research notes, and other useful content across their writing projects.

**Reference:** [Novelcrafter Snippets Documentation](https://www.novelcrafter.com/help/docs/snippets/snippets)

---

## 🎯 Epic Goals

1. Create and manage text snippets
2. Categorize and organize snippets
3. Quick access and insertion
4. Save from chat/AI responses
5. Extract to Codex or scenes
6. Pin frequently used snippets

---

## 📑 Feature Groups

### FG-07.1: Snippets Core

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-07.1.1 | Creating a Snippet | 🔴 Critical | 5 |
| F-07.1.2 | Anatomy of a Snippet | 🔴 Critical | 3 |
| F-07.1.3 | Snippet List View | 🔴 Critical | 3 |
| F-07.1.4 | Snippet Detail View | 🟡 High | 2 |

### FG-07.2: Organization

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-07.2.1 | Snippet Categories/Tags | 🟡 High | 3 |
| F-07.2.2 | Search & Filter | 🟡 High | 2 |
| F-07.2.3 | Pin Snippets | 🟡 High | 2 |

### FG-07.3: Integration

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-07.3.1 | Save from Chat | 🟡 High | 3 |
| F-07.3.2 | Insert into Editor | 🟡 High | 3 |
| F-07.3.3 | Extract to Codex/Scene | 🟢 Medium | 3 |

---

## 📝 Detailed User Stories

### US-07.1: Creating a Snippet
**Priority:** 🔴 Critical | **Points:** 5

**As a** writer,  
**I want to** create and save snippets,  
**So that** I can reuse content later.

#### Acceptance Criteria:
- [ ] Create new snippet with title and content
- [ ] Rich text content (formatting preserved)
- [ ] Add tags/categories
- [ ] Save from editor selection
- [ ] Save from chat response
- [ ] Quick create (just content)

**Reference:** [Creating a snippet](https://www.novelcrafter.com/help/docs/snippets/snippets)

---

### US-07.2: Anatomy of a Snippet
**Priority:** 🔴 Critical | **Points:** 3

**As a** writer,  
**I want to** see snippet details clearly,  
**So that** I can understand what each snippet contains.

#### Acceptance Criteria:
- [ ] Title (editable)
- [ ] Content preview
- [ ] Tags/categories
- [ ] Created date
- [ ] Source (chat, editor, manual)
- [ ] Related novel (optional)
- [ ] Pin status

**Reference:** [Anatomy of a snippet](https://www.novelcrafter.com/help/docs/snippets/anatomy-of-a-snippet)

---

### US-07.3: Snippet List View
**Priority:** 🔴 Critical | **Points:** 3

**As a** writer,  
**I want to** browse all my snippets,  
**So that** I can find and manage them.

#### Acceptance Criteria:
- [ ] Grid/list view toggle
- [ ] Snippet cards with preview
- [ ] Sort by date, name, usage
- [ ] Filter by tag/category
- [ ] Filter by novel
- [ ] Pinned snippets at top

---

### US-07.4: Snippet Categories/Tags
**Priority:** 🟡 High | **Points:** 3

**As a** writer,  
**I want to** categorize snippets,  
**So that** I can organize them by topic.

#### Acceptance Criteria:
- [ ] Create categories/tags
- [ ] Assign multiple tags per snippet
- [ ] Tag colors
- [ ] Filter by tag
- [ ] Manage tags (rename, delete)

---

### US-07.5: Pin Snippets
**Priority:** 🟡 High | **Points:** 2

**As a** writer,  
**I want to** pin frequently used snippets,  
**So that** I can access them quickly.

#### Acceptance Criteria:
- [ ] Pin/unpin toggle
- [ ] Pinned snippets section
- [ ] Quick access from editor
- [ ] Limit number of pins

---

### US-07.6: Save from Chat
**Priority:** 🟡 High | **Points:** 3

**As a** writer,  
**I want to** save chat responses as snippets,  
**So that** I can keep useful AI-generated content.

#### Acceptance Criteria:
- [ ] "Save as snippet" button on chat messages
- [ ] Auto-title from first line
- [ ] Auto-tag as "From Chat"
- [ ] Link to source chat thread
- [ ] Edit before saving (optional)

---

### US-07.7: Insert into Editor
**Priority:** 🟡 High | **Points:** 3

**As a** writer,  
**I want to** insert snippets into my manuscript,  
**So that** I can reuse saved content.

#### Acceptance Criteria:
- [ ] Snippet picker in editor
- [ ] Insert at cursor position
- [ ] Preview before insert
- [ ] Recent snippets list
- [ ] Search snippets
- [ ] Keyboard shortcut

---

### US-07.8: Extract to Codex/Scene
**Priority:** 🟢 Medium | **Points:** 3

**As a** writer,  
**I want to** extract snippet content to Codex or scenes,  
**So that** I can turn snippets into proper story elements.

#### Acceptance Criteria:
- [ ] "Create Codex entry" action
- [ ] "Create scene" action
- [ ] Pre-fill content
- [ ] Edit before creating
- [ ] Keep snippet after extraction

---

## 🗄️ Database Schema

### Table: `snippets`

```sql
CREATE TABLE snippets (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    novel_id BIGINT UNSIGNED NULL, -- NULL for global snippets
    title VARCHAR(255) NOT NULL,
    content LONGTEXT NOT NULL,
    source_type ENUM('manual', 'chat', 'editor', 'import') DEFAULT 'manual',
    source_id BIGINT UNSIGNED NULL, -- chat_message_id, scene_id, etc.
    is_pinned BOOLEAN DEFAULT FALSE,
    usage_count INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (novel_id) REFERENCES novels(id) ON DELETE SET NULL,
    INDEX idx_user_snippets (user_id, is_pinned),
    FULLTEXT idx_search (title, content)
);
```

### Table: `snippet_tags`

```sql
CREATE TABLE snippet_tags (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    color VARCHAR(7) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY idx_user_tag (user_id, name)
);
```

### Table: `snippet_tag_pivot`

```sql
CREATE TABLE snippet_tag_pivot (
    snippet_id BIGINT UNSIGNED NOT NULL,
    tag_id BIGINT UNSIGNED NOT NULL,
    
    PRIMARY KEY (snippet_id, tag_id),
    FOREIGN KEY (snippet_id) REFERENCES snippets(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES snippet_tags(id) ON DELETE CASCADE
);
```

---

## 🏗️ Technical Architecture

### Backend Structure

```
app/
├── Models/
│   ├── Snippet.php
│   └── SnippetTag.php
├── Http/
│   └── Controllers/
│       └── SnippetController.php
```

### Frontend Structure

```
resources/js/
├── Pages/
│   └── Snippets/
│       └── Index.vue
├── Components/
│   └── Snippets/
│       ├── SnippetCard.vue
│       ├── SnippetForm.vue
│       ├── SnippetDetail.vue
│       ├── SnippetPicker.vue
│       ├── SnippetTagManager.vue
│       └── SnippetPanel.vue
```

---

## 🔀 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/snippets` | List snippets |
| POST | `/api/snippets` | Create snippet |
| GET | `/api/snippets/{snippet}` | Get snippet |
| PATCH | `/api/snippets/{snippet}` | Update snippet |
| DELETE | `/api/snippets/{snippet}` | Delete snippet |
| POST | `/api/snippets/{snippet}/pin` | Toggle pin |
| GET | `/api/snippet-tags` | List tags |
| POST | `/api/snippet-tags` | Create tag |

---

## ✅ Definition of Done

- [ ] CRUD operations for snippets
- [ ] Tags/categories working
- [ ] Pin functionality working
- [ ] Save from chat working
- [ ] Insert into editor working
- [ ] Extract to Codex/Scene working
- [ ] Search and filter functional
- [ ] Mobile-responsive
- [ ] Unit tests for models
- [ ] Feature tests for endpoints

---

## 📎 References

- [Creating a snippet](https://www.novelcrafter.com/help/docs/snippets/snippets)
- [Anatomy of a snippet](https://www.novelcrafter.com/help/docs/snippets/anatomy-of-a-snippet)
