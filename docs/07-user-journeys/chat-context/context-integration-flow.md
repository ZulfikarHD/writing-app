# User Journey: Chat Context Integration Flow

**Feature:** FG-04.2 Context & Integration  
**User Persona:** Novel Writer using Workshop Chat  
**Last Updated:** 2026-01-02

---

## Journey Overview

This document maps out how writers discover, add, manage, and use context in their AI chat conversations to get more relevant and contextually-aware responses.

---

## Journey 1: Adding Scene Context to Chat

### Scenario
Sarah is working on Chapter 5 and wants the AI to help her brainstorm the next plot point while being aware of what happened in Chapter 3.

### User Goal
Get AI suggestions that are consistent with previously written scenes.

### Journey Map

```
📍 START: Chat Panel in Workshop Mode
    │
    ├─▶ [Step 1] Open existing or create new chat thread
    │   └─ UI: Chat window with message history
    │
    ├─▶ [Step 2] Notice "Add Context" button in chat input area
    │   └─ UI: Button with "+" icon next to model selector
    │
    ├─▶ [Step 3] Click "Add Context" button
    │   └─ ACTION: Opens Context Selector modal
    │   └─ UI: Modal with 3 tabs: Scenes, Codex, Custom
    │   └─ Default tab: Scenes
    │
    ├─▶ [Step 4] See list of scenes grouped by chapters
    │   └─ UI: Expandable chapter list showing:
    │          - Chapter titles
    │          - Scene titles
    │          - Word count per scene
    │          - Estimated token count
    │
    ├─▶ [Step 5] Use search to find "Chapter 3"
    │   └─ UI: Search input at top filters results real-time
    │   └─ RESULT: Only Chapter 3 scenes visible
    │
    ├─▶ [Step 6] Select 2 scenes from Chapter 3
    │   └─ ACTION: Click checkboxes next to scene titles
    │   └─ UI: Checkboxes turn blue, token count updates
    │   └─ FEEDBACK: Bottom shows "2 items selected • 2.5K tokens"
    │
    ├─▶ [Step 7] Click "Add Selected (2)" button
    │   └─ ACTION: API call to add contexts
    │   └─ UI: Button shows loading spinner
    │   └─ WAIT: ~500ms
    │   └─ SUCCESS: Modal closes
    │
    ├─▶ [Step 8] See context badge update in chat input
    │   └─ UI: Badge shows "2 contexts • 2.5K tokens"
    │   └─ FEEDBACK: Small success animation
    │
    ├─▶ [Step 9] Type message: "What should happen next?"
    │   └─ UI: Normal text input
    │
    ├─▶ [Step 10] Click Send button
    │   └─ ACTION: Message sent with context attached
    │   └─ UI: Message appears in chat
    │   └─ FEEDBACK: AI starts streaming response
    │
    └─▶ [Step 11] Receive AI response aware of Chapter 3 context
        └─ RESULT: AI mentions specific events from selected scenes
        └─ SUCCESS: Contextually relevant suggestions
        └─ UI: Response fully displayed

✅ GOAL ACHIEVED: AI provided suggestions consistent with Chapter 3 events
```

### Screenshots References (if applicable)
- Context Selector Modal (Scenes Tab)
- Token Badge in Chat Input
- Context Badge Hover State

---

## Journey 2: Managing Context for Token Limits

### Scenario
David has added many scenes to context and notices the AI responses are getting slower. He wants to optimize context usage to stay within token limits.

### User Goal
Reduce token usage by disabling unnecessary context items while keeping important ones active.

### Journey Map

```
📍 START: Chat thread with multiple context items
    │
    ├─▶ [Step 1] Notice token badge shows warning color (yellow/orange)
    │   └─ UI: Badge "12 contexts • 45K tokens" in orange
    │   └─ AWARENESS: Approaching token limit
    │
    ├─▶ [Step 2] Click on token badge
    │   └─ ACTION: Opens Context Preview modal
    │   └─ UI: Modal titled "Context Preview"
    │   └─ Shows: Token usage bar (75% used) in orange
    │
    ├─▶ [Step 3] Review list of context items
    │   └─ UI: List shows:
    │          - Context name (scene/codex title)
    │          - Type badge (Scene/Codex/Custom)
    │          - Token count per item
    │          - Toggle switch (on/off)
    │          - Remove button (X)
    │
    ├─▶ [Step 4] Identify less relevant contexts
    │   └─ THOUGHT: "I don't need Chapter 1 scenes anymore"
    │   └─ UI: Scan list for Chapter 1 items
    │
    ├─▶ [Step 5] Toggle OFF 4 Chapter 1 scenes
    │   └─ ACTION: Click toggle switches to disable
    │   └─ UI: Items turn gray/dimmed
    │   └─ IMMEDIATE FEEDBACK:
    │          - Token count decreases per toggle
    │          - Usage bar updates: 75% → 65% → 55% → 45%
    │          - Bar color changes: Orange → Yellow → Green
    │
    ├─▶ [Step 6] Observe updated token usage
    │   └─ UI: Now shows "8 contexts (4 inactive) • 28K tokens"
    │   └─ Usage bar: 45% (green)
    │   └─ SATISFACTION: "Much better!"
    │
    ├─▶ [Step 7] Remove one completely irrelevant item
    │   └─ ACTION: Click X button on an item
    │   └─ UI: Confirmation prompt: "Remove this context?"
    │   └─ ACTION: Click "Remove"
    │   └─ RESULT: Item disappears, tokens update
    │
    ├─▶ [Step 8] Close Context Preview
    │   └─ ACTION: Click Close or click outside modal
    │   └─ UI: Modal closes with fade animation
    │
    ├─▶ [Step 9] See updated badge in chat input
    │   └─ UI: Badge now shows "7 contexts • 26K tokens" (green)
    │
    └─▶ [Step 10] Send new message
        └─ RESULT: AI response faster, still contextually aware
        └─ SUCCESS: Optimized token usage

✅ GOAL ACHIEVED: Reduced token usage from 75% to 45% while keeping relevant context
```

---

## Journey 3: Adding Codex Context for Character Consistency

### Scenario
Emma is brainstorming dialogue for her protagonist and wants the AI to remember the character's personality traits, backstory, and speech patterns from her codex.

### User Goal
Get AI-generated dialogue that matches character's established personality.

### Journey Map

```
📍 START: Chat thread discussing protagonist dialogue
    │
    ├─▶ [Step 1] Realize AI doesn't know character details
    │   └─ PROBLEM: AI suggestion doesn't match character
    │   └─ THOUGHT: "I need to give the AI my character profile"
    │
    ├─▶ [Step 2] Click "Add Context" button
    │   └─ ACTION: Context Selector modal opens
    │   └─ UI: Currently on "Scenes" tab
    │
    ├─▶ [Step 3] Switch to "Codex" tab
    │   └─ ACTION: Click "Codex" tab
    │   └─ UI: Tab content changes to show codex entries
    │   └─ Shows: Entries grouped by type (Characters, Locations, Items, etc.)
    │
    ├─▶ [Step 4] Expand "Characters" group
    │   └─ UI: Accordion opens showing list of characters
    │   └─ Each entry shows:
    │          - Character name
    │          - Small description preview
    │          - Token count estimate
    │
    ├─▶ [Step 5] Search for protagonist "Alex Chen"
    │   └─ ACTION: Type "Alex" in search box
    │   └─ UI: List filters to show only matching entries
    │   └─ RESULT: "Alex Chen" entry visible
    │
    ├─▶ [Step 6] Select "Alex Chen" character
    │   └─ ACTION: Click checkbox
    │   └─ UI: Checkbox selected, shows "680 tokens"
    │
    ├─▶ [Step 7] Also select related character "Lisa (Alex's mentor)"
    │   └─ ACTION: Click checkbox for "Lisa"
    │   └─ UI: Now "2 items selected • 1.2K tokens"
    │
    ├─▶ [Step 8] Click "Add Selected (2)"
    │   └─ ACTION: API call adds codex contexts
    │   └─ UI: Loading → Success → Modal closes
    │
    ├─▶ [Step 9] Verify contexts added
    │   └─ UI: Badge shows "2 contexts • 1.2K tokens"
    │   └─ ACTION: Click badge to preview
    │   └─ UI: Context Preview shows:
    │          ▫ "Alex Chen" (Codex - Character) - 680 tokens
    │          ▫ "Lisa" (Codex - Character) - 520 tokens
    │
    ├─▶ [Step 10] Send message: "Write dialogue where Alex confronts Lisa"
    │   └─ ACTION: Type and send
    │   └─ WAIT: AI processing with codex context
    │
    └─▶ [Step 11] Receive AI-generated dialogue
        └─ RESULT: Dialogue matches:
               ▫ Alex's sarcastic speech pattern (from codex)
               ▫ Lisa's calm demeanor (from codex)
               ▫ Their mentor-student relationship (from codex)
        └─ SUCCESS: Character-consistent dialogue
        └─ SATISFACTION: "Perfect! This sounds like my characters!"

✅ GOAL ACHIEVED: AI generated dialogue consistent with character profiles
```

---

## Journey 4: Using Custom Context for Writing Guidelines

### Scenario
Mark wants the AI to always remember specific writing instructions for this particular story (e.g., "Keep tone dark and gritty", "Avoid purple prose").

### User Goal
Give AI persistent instructions that apply to all messages in this thread.

### Journey Map

```
📍 START: New chat thread for story brainstorming
    │
    ├─▶ [Step 1] Create new chat thread
    │   └─ UI: Empty chat window
    │
    ├─▶ [Step 2] Click "Add Context" button
    │   └─ ACTION: Context Selector opens
    │
    ├─▶ [Step 3] Switch to "Custom" tab
    │   └─ ACTION: Click "Custom" tab
    │   └─ UI: Shows textarea with placeholder:
    │          "Add custom instructions, notes, or reminders for the AI..."
    │
    ├─▶ [Step 4] Type writing guidelines
    │   └─ ACTION: Type multi-line instructions:
    │      ```
    │      Writing Guidelines for "Neon Shadows":
    │      - Tone: Dark, gritty, cyberpunk noir
    │      - Avoid: Purple prose, overly flowery descriptions
    │      - Focus: Action, dialogue, atmosphere
    │      - POV: Third-person limited, Alex's perspective
    │      - Setting: Neo-Tokyo, 2087
    │      ```
    │   └─ UI: Character counter updates (e.g., "245 / 100000 chars")
    │   └─ UI: Token estimate updates: "~65 tokens"
    │
    ├─▶ [Step 5] Click "Add Custom Context" button
    │   └─ ACTION: API call creates custom context
    │   └─ UI: Success feedback
    │   └─ RESULT: Modal closes
    │
    ├─▶ [Step 6] See custom context badge
    │   └─ UI: Badge "1 context • 65 tokens"
    │
    ├─▶ [Step 7] Send first message: "Describe a street market scene"
    │   └─ ACTION: Type and send
    │   └─ AI PROCESSING: With custom guidelines in context
    │
    ├─▶ [Step 8] Receive response
    │   └─ RESULT: Description is:
    │          ▫ Dark and gritty (✓)
    │          ▫ Concise, no purple prose (✓)
    │          ▫ Cyberpunk atmosphere (✓)
    │   └─ SUCCESS: AI followed guidelines
    │
    ├─▶ [Step 9] Send follow-up: "Now describe a fight scene"
    │   └─ ACTION: Send new message
    │   └─ AI PROCESSING: Still has guidelines in context
    │
    └─▶ [Step 10] Verify persistent guidelines
        └─ RESULT: Fight scene also follows guidelines
        └─ SUCCESS: Guidelines persist across messages
        └─ BENEFIT: Don't need to repeat instructions every time

✅ GOAL ACHIEVED: AI consistently follows writing guidelines for entire thread
```

---

## Journey 5: Clearing Context for Fresh Start

### Scenario
Julia has been chatting about plot development with lots of context, but now wants to switch topics to character names. She wants a fresh start without old context influencing suggestions.

### User Goal
Remove all context to get unbiased AI suggestions for new topic.

### Journey Map

```
📍 START: Chat thread with 8 context items (plot-related)
    │
    ├─▶ [Step 1] Decide to switch topics
    │   └─ THOUGHT: "I don't need all this plot context for naming characters"
    │
    ├─▶ [Step 2] Click context badge
    │   └─ ACTION: Opens Context Preview
    │   └─ UI: Shows 8 items with total token usage
    │
    ├─▶ [Step 3] Notice "Clear All" button at bottom
    │   └─ UI: Button in red/warning color
    │   └─ HOVER: Tooltip "Remove all context items"
    │
    ├─▶ [Step 4] Click "Clear All" button
    │   └─ ACTION: Triggers action
    │   └─ UI: Confirmation dialog appears:
    │          "Remove all 8 context items?"
    │          [Cancel] [Clear All]
    │   └─ WARNING: Shows total tokens that will be removed
    │
    ├─▶ [Step 5] Confirm action
    │   └─ ACTION: Click "Clear All" in dialog
    │   └─ UI: Loading state briefly
    │   └─ API: DELETE /chat/threads/{thread}/context/clear
    │
    ├─▶ [Step 6] See results
    │   └─ UI: Context Preview now shows:
    │          - Empty state: "No context items added yet"
    │          - Token usage: 0 tokens
    │   └─ SUCCESS FEEDBACK: "All context cleared"
    │
    ├─▶ [Step 7] Close Context Preview
    │   └─ UI: Modal closes
    │
    ├─▶ [Step 8] See updated badge
    │   └─ UI: Badge now hidden or shows "0 contexts"
    │   └─ VISUAL: "Add Context" button more prominent
    │
    ├─▶ [Step 9] Send new message: "Suggest names for a hacker character"
    │   └─ ACTION: Type and send
    │   └─ AI PROCESSING: Without any context
    │
    └─▶ [Step 10] Receive fresh suggestions
        └─ RESULT: AI gives general name suggestions
        └─ NOT INFLUENCED: By previous plot context
        └─ SUCCESS: Clean slate for new topic

✅ GOAL ACHIEVED: Cleared context for unbiased AI suggestions on new topic
```

---

## Journey 6: Discovering Context Feature (First Time User)

### Scenario
New user Rachel has been using chat for basic questions, but hasn't discovered context feature yet. She wonders why the AI doesn't know about her characters.

### User Goal
Discover and understand the context feature.

### Journey Map

```
📍 START: Using chat, frustrated that AI doesn't know story details
    │
    ├─▶ [Step 1] Notice AI gives generic responses
    │   └─ PROBLEM: "Why doesn't the AI know my character?"
    │   └─ FEELING: Frustrated
    │
    ├─▶ [Step 2] See "+ Add Context" button in chat input
    │   └─ DISCOVERY: "What's this button?"
    │   └─ CURIOSITY: Hovers over button
    │   └─ UI: Tooltip appears: "Add scenes, codex, or custom context"
    │
    ├─▶ [Step 3] Click button to explore
    │   └─ ACTION: Context Selector opens
    │   └─ UI: Clean modal with tabs
    │   └─ FIRST IMPRESSION: "Oh! I can add my story content"
    │
    ├─▶ [Step 4] Read the helper text
    │   └─ UI: Header shows:
    │          "Add context to help AI understand your story better"
    │   └─ UNDERSTANDING: "This is how AI learns about my novel!"
    │
    ├─▶ [Step 5] Explore Scenes tab
    │   └─ UI: Sees all her chapters and scenes listed
    │   └─ REALIZATION: "I can select specific scenes!"
    │   └─ OBSERVES: Token counts next to each scene
    │
    ├─▶ [Step 6] Switch to Codex tab
    │   └─ UI: Sees her character "Emma" listed
    │   └─ EXCITEMENT: "Perfect! This is what I need!"
    │
    ├─▶ [Step 7] Select character "Emma"
    │   └─ ACTION: Check the box
    │   └─ UI: Feedback shows "1 item selected • 450 tokens"
    │
    ├─▶ [Step 8] Add context
    │   └─ ACTION: Click "Add Selected"
    │   └─ ANTICIPATION: "Let's see if this works..."
    │
    ├─▶ [Step 9] Send message again: "What would Emma do in this situation?"
    │   └─ ACTION: Same question as before
    │   └─ EXPECTATION: Hoping for better response
    │
    └─▶ [Step 10] Receive contextually aware response
        └─ RESULT: AI references Emma's personality traits from codex
        └─ DELIGHT: "Wow! It knows my character now!"
        └─ AHA MOMENT: Understanding how context works
        └─ SATISFACTION: Problem solved
        └─ LIKELIHOOD: Will use context feature regularly now

✅ GOAL ACHIEVED: Discovered and successfully used context feature
📈 IMPACT: User adoption of key feature, increased satisfaction
```

---

## Common Pain Points & Solutions

| Pain Point | User Need | Solution in Design |
|------------|-----------|-------------------|
| "AI doesn't know my story" | Context awareness | Context feature prominently placed |
| "Too many tokens" | Token management | Visual usage bar with warnings |
| "Don't know which scenes to add" | Guidance | Search + grouped by chapters |
| "AI response too slow" | Performance | Disable unnecessary context |
| "Lost track of what's added" | Visibility | Context badge with count |
| "Need to update context often" | Easy management | Quick toggle on/off |
| "Forgot to add context" | Reminder | Empty state prompts |

---

## User Feedback Quotes (Anticipated)

> "Finally! The AI understands my characters without me repeating everything." - Fiction Writer

> "The token counter is genius. I can see exactly how much context I'm using." - Fantasy Author

> "Being able to toggle contexts on and off is perfect for different types of conversations." - Sci-Fi Writer

---

## Success Metrics

| Metric | Target | Measurement |
|--------|--------|-------------|
| Context usage adoption | 60% of chat users | % of threads with context items |
| Average contexts per thread | 3-5 items | Avg count of active contexts |
| Token optimization | <50% usage | Avg token usage percentage |
| Feature discovery time | <2 sessions | Time from first chat to first context use |

---

## Related Documentation

- **API Reference:** [Chat Context API](../../04-api-reference/chat.md#context-management-endpoints)
- **Testing Guide:** [Context Testing](../../06-testing/chat-context-testing.md)
- **Sprint Documentation:** [Sprint FG-04.2](../../10-sprints/sprint-fg-04.2-context-integration.md)

---

*Last Updated: 2026-01-02*
