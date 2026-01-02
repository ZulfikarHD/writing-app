# Performance Mode - Developer Guide

> **📚 Quick Links:**  
> [Sprint Documentation](./10-sprints/sprint-19-performance-mode.md) | [Bug Fixes](./bug-fixes/)

---

## Overview

Performance Mode merupakan system yang memungkinkan users untuk toggle antara full animations (beautiful, GPU-intensive) dan reduced animations (faster, lightweight) untuk better performance pada lower-spec devices, yaitu: meningkatkan frame rate 2-3x pada integrated GPU, menghilangkan animation jank, dan memberikan smooth 60fps experience pada perangkat dengan spesifikasi rendah.

## How It Works

- **Full Mode**: All animations, backdrop blur, spring physics, scale transforms - beautiful experience untuk high-spec devices
- **Reduced Mode**: Simple fade transitions, no backdrop blur, no scale transforms, CSS transitions instead of spring physics - optimal performance untuk low-spec devices

Settings are automatically stored in `localStorage` dengan key `novelwrite-performance-settings` dan persist across sessions.

---

## Implementation

### 1. The Composable

Located at: `resources/js/composables/usePerformanceMode.ts`

Provides reactive flags and helper classes/configs:

```typescript
const { 
  isReducedMotion,           // boolean
  isFullAnimation,           // boolean
  springConfig,              // Motion config object
  sidebarSpringConfig,       // Motion config for sidebars
  backdropBlurClass,         // 'backdrop-blur-sm' or ''
  buttonTransitionClass,     // 'transition-all' or 'transition-colors'
  scaleActiveClass,          // 'active:scale-95' or ''
  setMode,                   // (mode: 'full' | 'reduced') => void
  toggleMode,                // () => void
} = usePerformanceMode();
```

### 2. User Interface

The toggle is located in:
`resources/js/components/editor/panels/EditorSettingsPanel.vue`

Users can access it by:
1. Opening the editor
2. Clicking the settings gear icon
3. Selecting "Full" or "Reduced" under Performance section

### 3. Using in Components

#### Example 1: Conditional Motion Animations

```vue
<script setup lang="ts">
import { Motion, AnimatePresence } from 'motion-v';
import { usePerformanceMode } from '@/composables/usePerformanceMode';

const { isReducedMotion, springConfig } = usePerformanceMode();
</script>

<template>
  <Motion
    :initial="isReducedMotion ? { opacity: 0 } : { opacity: 0, y: 20, scale: 0.95 }"
    :animate="isReducedMotion ? { opacity: 1 } : { opacity: 1, y: 0, scale: 1 }"
    :exit="isReducedMotion ? { opacity: 0 } : { opacity: 0, y: 20, scale: 0.95 }"
    :transition="springConfig"
  >
    <!-- Content -->
  </Motion>
</template>
```

#### Example 2: Conditional CSS Classes

```vue
<script setup lang="ts">
import { usePerformanceMode } from '@/composables/usePerformanceMode';

const { backdropBlurClass, buttonTransitionClass, scaleActiveClass } = usePerformanceMode();
</script>

<template>
  <!-- Backdrop with optional blur -->
  <div :class="['bg-zinc-50/95', backdropBlurClass]">
    
    <!-- Button with optimized transitions -->
    <button :class="['px-4 py-2', buttonTransitionClass, scaleActiveClass]">
      Click me
    </button>
  </div>
</template>
```

#### Example 3: Sidebar/Panel Animations

```vue
<script setup lang="ts">
import { Motion, AnimatePresence } from 'motion-v';
import { usePerformanceMode } from '@/composables/usePerformanceMode';

const { sidebarSpringConfig, backdropBlurClass } = usePerformanceMode();
</script>

<template>
  <AnimatePresence>
    <Motion
      v-if="isOpen"
      :initial="{ x: '100%' }"
      :animate="{ x: 0 }"
      :exit="{ x: '100%' }"
      :transition="sidebarSpringConfig"
      :class="['sidebar', backdropBlurClass]"
    >
      <!-- Sidebar content -->
    </Motion>
  </AnimatePresence>
</template>
```

## What to Optimize

### High Priority (Biggest Performance Impact)
1. ✅ **backdrop-blur** - Use `backdropBlurClass` (HUGE GPU impact)
2. ✅ **Spring animations** - Use `springConfig` or `sidebarSpringConfig`
3. ✅ **Scale transforms** - Use `scaleActiveClass`
4. **transition-all** - Use `buttonTransitionClass` (animates ALL properties)

### Medium Priority
- Complex Motion animations (opacity + scale + y transforms)
- AnimatePresence with height animations
- Multiple simultaneous animations

### Low Priority
- Simple opacity fades
- CSS color transitions
- Small icon rotations

## Components Already Optimized

- ✅ `Modal.vue` - Removed backdrop blur, simplified animations
- ✅ `ScenesRightSidebar.vue` - Removed backdrop blur, simplified spring animations
- ✅ `EditorSettingsPanel.vue` - Added performance toggle UI

## Components That Need Optimization

These components have heavy animations and should be updated:

- `CodexEntryModal.vue` - Has backdrop blur
- `FormModal.vue` - Has backdrop blur
- `EditorToolbar.vue` - Has backdrop blur and many transition-all
- `SceneDetailsSidebar.vue` - Has backdrop blur
- All components with `transition-all` (142 instances found)

## Testing

To test performance improvements:

1. Open dev tools → Performance tab
2. Toggle between Full and Reduced mode
3. Perform actions (open modal, collapse/expand, scroll sidebar)
4. Compare frame rates and jank

**Expected improvements in Reduced Mode:**
- 2-3x better frame rates on integrated GPUs
- Smoother scrolling
- Faster modal open/close
- No janky animations on low-end devices

## Future Improvements

Potential enhancements yang bisa dilakukan:

1. **Auto-detection**: Detect low-end devices dan automatically suggest Reduced mode
2. **Auto Mode**: Performance mode yang automatically adjust based on GPU performance metrics
3. **Granular Controls**: Separate toggles untuk backdrop blur, spring animations, scale transforms
4. **Performance Metrics Dashboard**: Show FPS dan GPU usage untuk monitoring
5. **More Components**: Extend performance mode ke 142+ components dengan `transition-all`

---

## Related Documentation

- **Sprint Implementation:** [Sprint 19 - Performance Mode](./10-sprints/sprint-19-performance-mode.md)
- **Composable Location:** `resources/js/composables/usePerformanceMode.ts`
- **UI Toggle Location:** Editor → Settings Icon (gear) → Performance Section

---

*Last Updated: 2026-01-02*
