<script setup lang="ts">
defineProps<{ size?: number }>()
</script>

<template>
  <div class="app-logo" :style="{ gap: (size || 28) / 3.5 + 'px' }">
    <svg :width="size || 28" :height="size || 28" viewBox="0 0 32 32" fill="none">
      <rect width="32" height="32" rx="8" fill="#4F46E5"/>
      <path d="M16 8L26 13L16 18L6 13L16 8Z" fill="white"/>
      <path d="M10 15.5V21C10 21 12.5 23 16 23C19.5 23 22 21 22 21V15.5" stroke="white" stroke-width="1.8" stroke-linecap="round"/>
    </svg>
    <span class="app-logo-text" :style="{ fontSize: (size || 28) * 0.6 + 'px' }">LearnQuest</span>
  </div>
</template>

<style scoped>
.app-logo { display: flex; align-items: center; }
.app-logo-text { font-weight: 800; color: #4F46E5; letter-spacing: -0.3px; }
</style>