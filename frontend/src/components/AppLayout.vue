<script setup lang="ts">
import AppSidebar from './AppSidebar.vue'
</script>

<template>
  <div class="app-layout">
    <AppSidebar />
    <main class="main-content">
      <slot />
    </main>
  </div>
</template>

<style scoped>
.app-layout { display: flex; min-height: 100vh; background: #F9FAFB; }
.main-content { margin-left: 240px; flex: 1; padding: 32px 48px; box-sizing: border-box; }
</style>