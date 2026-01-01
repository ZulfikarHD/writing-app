# 👥 EPIC 10: Collaboration

**Epic ID:** EPIC-10  
**Priority:** 🟢 Medium  
**Total Story Points:** ~50  
**Est. Duration:** 2-3 Sprints  
**Dependencies:** All Core Epics

---

## 📋 Epic Description

Build collaboration features that enable writers to share projects, co-author with others, manage teams, and track collaborative work. This is a growth-driver feature that expands the app's value proposition.

**Reference:** [Novelcrafter Collaboration](https://www.novelcrafter.com/help/docs/app/collaboration-and-coauthoring)

---

## 🎯 Epic Goals

1. Invite collaborators to projects
2. Role-based access control (Viewer, Editor, Admin)
3. Activity tracking and notifications
4. Team management
5. Shared workspaces

---

## 📑 Feature Groups

### FG-10.1: Collaboration Core

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-10.1.1 | Collaboration and Coauthoring | 🔴 Critical | 13 |
| F-10.1.2 | Invite Collaborators | 🔴 Critical | 5 |
| F-10.1.3 | Role-Based Access | 🔴 Critical | 5 |
| F-10.1.4 | Collaborator Management | 🟡 High | 3 |

### FG-10.2: Teams

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-10.2.1 | Teams Feature | 🟡 High | 8 |
| F-10.2.2 | Team Dashboard | 🟡 High | 5 |
| F-10.2.3 | Team Project Sharing | 🟡 High | 3 |

### FG-10.3: Activity & Sync

| Feature | Description | Priority | Points |
|---------|-------------|----------|--------|
| F-10.3.1 | Activity Log | 🟡 High | 5 |
| F-10.3.2 | Real-time Sync (Optional) | 🟢 Low | 8 |
| F-10.3.3 | Conflict Resolution | 🟡 High | 5 |

---

## 📝 Detailed User Stories

### US-10.1: Collaboration and Coauthoring
**Priority:** 🔴 Critical | **Points:** 13

**As a** writer collaborating with others,  
**I want to** share my novel with co-authors,  
**So that** we can work on the project together.

#### Acceptance Criteria:
- [ ] Share novel with other users
- [ ] Collaborators see shared project in their dashboard
- [ ] Multiple collaborators per project
- [ ] Collaborator list visible to all collaborators
- [ ] Owner can manage all collaborators
- [ ] Collaborators can leave project

**Reference:** [Collaboration and Coauthoring](https://www.novelcrafter.com/help/docs/app/collaboration-and-coauthoring)

---

### US-10.2: Invite Collaborators
**Priority:** 🔴 Critical | **Points:** 5

**As a** project owner,  
**I want to** invite collaborators by email,  
**So that** they can join my project.

#### Acceptance Criteria:
- [ ] Invite via email address
- [ ] Invite via share link (optional)
- [ ] Pending invitations list
- [ ] Resend invitation
- [ ] Cancel invitation
- [ ] Invitation email with instructions
- [ ] Accept/decline invitation flow

---

### US-10.3: Role-Based Access
**Priority:** 🔴 Critical | **Points:** 5

**As a** project owner,  
**I want to** set roles for collaborators,  
**So that** I can control what they can do.

#### Acceptance Criteria:
- [ ] Roles: Owner, Admin, Editor, Viewer
- [ ] Owner: Full control, can delete project
- [ ] Admin: Manage collaborators, edit everything
- [ ] Editor: Edit content, can't manage collaborators
- [ ] Viewer: Read-only access
- [ ] Change role after invitation
- [ ] Role displayed in collaborator list

---

### US-10.4: Collaborator Management
**Priority:** 🟡 High | **Points:** 3

**As a** project owner,  
**I want to** manage collaborators,  
**So that** I can control project access.

#### Acceptance Criteria:
- [ ] View all collaborators
- [ ] Change collaborator roles
- [ ] Remove collaborator
- [ ] Transfer ownership
- [ ] View last activity per collaborator

---

### US-10.5: Teams Feature
**Priority:** 🟡 High | **Points:** 8

**As a** writer in an organization,  
**I want to** create teams,  
**So that** I can share projects with groups easily.

#### Acceptance Criteria:
- [ ] Create team with name
- [ ] Invite members to team
- [ ] Team roles (Owner, Admin, Member)
- [ ] Share project with entire team
- [ ] Team member management
- [ ] Leave team

---

### US-10.6: Team Dashboard
**Priority:** 🟡 High | **Points:** 5

**As a** team member,  
**I want to** see team projects in a dashboard,  
**So that** I can access shared work easily.

#### Acceptance Criteria:
- [ ] Team section in main dashboard
- [ ] List team projects
- [ ] Team member list
- [ ] Team activity feed
- [ ] Switch between teams

---

### US-10.7: Team Project Sharing
**Priority:** 🟡 High | **Points:** 3

**As a** team owner,  
**I want to** share projects with my team,  
**So that** all team members get access automatically.

#### Acceptance Criteria:
- [ ] "Share with team" option
- [ ] Select which team(s)
- [ ] Default role for team members
- [ ] New team members get access automatically
- [ ] Remove team access

---

### US-10.8: Activity Log
**Priority:** 🟡 High | **Points:** 5

**As a** collaborator,  
**I want to** see project activity,  
**So that** I know what changes were made.

#### Acceptance Criteria:
- [ ] Log all significant actions
- [ ] Who did what, when
- [ ] Filter by user
- [ ] Filter by action type
- [ ] Activity notifications (optional)
- [ ] Export activity log

---

### US-10.9: Conflict Resolution
**Priority:** 🟡 High | **Points:** 5

**As a** collaborator,  
**I want to** handle editing conflicts,  
**So that** work isn't lost when multiple people edit.

#### Acceptance Criteria:
- [ ] Detect concurrent edits
- [ ] Notification when conflict detected
- [ ] Show conflicting versions
- [ ] Merge options
- [ ] Keep both versions option
- [ ] Last-write-wins option (with warning)

---

### US-10.10: Real-time Sync (Optional)
**Priority:** 🟢 Low | **Points:** 8

**As a** collaborator,  
**I want to** see changes in real-time,  
**So that** I know when others are editing.

#### Acceptance Criteria:
- [ ] Real-time presence indicators
- [ ] See who's currently editing
- [ ] Live cursor positions (optional)
- [ ] Instant content sync
- [ ] Graceful degradation if offline

---

## 🗄️ Database Schema

### Table: `project_collaborators`

```sql
CREATE TABLE project_collaborators (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    novel_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    role ENUM('owner', 'admin', 'editor', 'viewer') NOT NULL DEFAULT 'editor',
    invited_by BIGINT UNSIGNED NULL,
    accepted_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (novel_id) REFERENCES novels(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (invited_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY idx_novel_user (novel_id, user_id)
);
```

### Table: `project_invitations`

```sql
CREATE TABLE project_invitations (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    novel_id BIGINT UNSIGNED NOT NULL,
    email VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor', 'viewer') NOT NULL DEFAULT 'editor',
    token VARCHAR(100) NOT NULL,
    invited_by BIGINT UNSIGNED NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (novel_id) REFERENCES novels(id) ON DELETE CASCADE,
    FOREIGN KEY (invited_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY idx_token (token)
);
```

### Table: `teams`

```sql
CREATE TABLE teams (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    owner_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### Table: `team_members`

```sql
CREATE TABLE team_members (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    team_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    role ENUM('owner', 'admin', 'member') NOT NULL DEFAULT 'member',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY idx_team_user (team_id, user_id)
);
```

### Table: `team_projects`

```sql
CREATE TABLE team_projects (
    team_id BIGINT UNSIGNED NOT NULL,
    novel_id BIGINT UNSIGNED NOT NULL,
    default_role ENUM('admin', 'editor', 'viewer') NOT NULL DEFAULT 'editor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (team_id, novel_id),
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (novel_id) REFERENCES novels(id) ON DELETE CASCADE
);
```

### Table: `activity_logs`

```sql
CREATE TABLE activity_logs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    novel_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(100) NOT NULL,
    subject_type VARCHAR(100) NULL, -- 'scene', 'chapter', 'codex_entry', etc.
    subject_id BIGINT UNSIGNED NULL,
    details JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (novel_id) REFERENCES novels(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_novel_activity (novel_id, created_at)
);
```

---

## 🏗️ Technical Architecture

### Backend Structure

```
app/
├── Services/
│   └── Collaboration/
│       ├── CollaborationService.php
│       ├── InvitationService.php
│       ├── TeamService.php
│       └── ActivityLogger.php
├── Models/
│   ├── ProjectCollaborator.php
│   ├── ProjectInvitation.php
│   ├── Team.php
│   ├── TeamMember.php
│   └── ActivityLog.php
├── Http/
│   └── Controllers/
│       ├── CollaboratorController.php
│       ├── InvitationController.php
│       ├── TeamController.php
│       └── ActivityController.php
├── Policies/
│   └── NovelPolicy.php (enhanced)
├── Notifications/
│   ├── ProjectInvitation.php
│   └── CollaboratorActivity.php
```

### Frontend Structure

```
resources/js/
├── Pages/
│   └── Teams/
│       └── Index.vue
├── Components/
│   └── Collaboration/
│       ├── CollaboratorList.vue
│       ├── InviteModal.vue
│       ├── RoleSelector.vue
│       ├── TeamList.vue
│       ├── TeamMembers.vue
│       ├── ActivityFeed.vue
│       ├── ConflictResolver.vue
│       └── PresenceIndicator.vue
```

---

## 🔀 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/novels/{novel}/collaborators` | List collaborators |
| POST | `/api/novels/{novel}/collaborators` | Add collaborator |
| PATCH | `/api/novels/{novel}/collaborators/{id}` | Update role |
| DELETE | `/api/novels/{novel}/collaborators/{id}` | Remove collaborator |
| POST | `/api/novels/{novel}/invitations` | Create invitation |
| GET | `/api/invitations/{token}` | Get invitation details |
| POST | `/api/invitations/{token}/accept` | Accept invitation |
| GET | `/api/teams` | List teams |
| POST | `/api/teams` | Create team |
| GET | `/api/teams/{team}` | Get team |
| POST | `/api/teams/{team}/members` | Add member |
| POST | `/api/teams/{team}/projects` | Share project |
| GET | `/api/novels/{novel}/activity` | Get activity log |

---

## ✅ Definition of Done

- [ ] Invite collaborators by email working
- [ ] Role-based access control enforced
- [ ] Collaborator management complete
- [ ] Teams creation and management working
- [ ] Team project sharing functional
- [ ] Activity logging all significant actions
- [ ] Conflict detection and resolution
- [ ] Policies/permissions tested thoroughly
- [ ] Notifications sent correctly
- [ ] Feature tests for all collaboration scenarios

---

## ⚠️ Risks & Mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| Permission bypass vulnerabilities | Critical | Thorough policy testing, security audit |
| Concurrent edit conflicts | High | Optimistic locking, clear conflict UI |
| Real-time sync complexity | High | Start without real-time, add later |
| Notification spam | Medium | User preferences, digest options |

---

## 📎 References

- [Collaboration and Coauthoring](https://www.novelcrafter.com/help/docs/app/collaboration-and-coauthoring)
