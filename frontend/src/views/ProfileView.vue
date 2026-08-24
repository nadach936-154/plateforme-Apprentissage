<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import api from '../services/api'
import { useAuthStore } from '../stores/auth'
import AppLayout from '../components/AppLayout.vue'

const badges = ref<any[]>([])
const authStore = useAuthStore()

onMounted(async () => {
  await authStore.fetchUser()
  const response = await api.get('/my-badges')
  badges.value = response.data
})

const xpInCurrentLevel = computed(() => (authStore.user?.xp ?? 0) % 100)
const progressPercent = computed(() => xpInCurrentLevel.value)
</script>

<template>
  <AppLayout>
    <div v-if="authStore.user">
      <h1>{{ authStore.user.name }}</h1>

      <div class="level-card">
        <div class="level-row">
          <span class="level-label">Niveau {{ authStore.user.level }}</span>
          <span class="xp-label">{{ xpInCurrentLevel }} / 100 XP</span>
        </div>
        <div class="progress-track">
          <div class="progress-fill" :style="{ width: progressPercent + '%' }"></div>
        </div>
        <p class="next-level">Encore {{ 100 - xpInCurrentLevel }} XP pour le niveau {{ authStore.user.level + 1 }}</p>
      </div>

      <h2>Mes badges</h2>
      <div class="badges-grid">
        <div v-for="badge in badges" :key="badge.id" :class="['badge-card', { locked: !badge.unlocked }]">
          <div class="badge-icon">{{ badge.unlocked ? badge.icon : '🔒' }}</div>
          <p>{{ badge.name }}</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
h1 { color: #111827; margin-bottom: 24px; }
h2 { color: #111827; margin-top: 32px; }

.level-card { background: white; border-radius: 16px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.level-row { display: flex; justify-content: space-between; margin-bottom: 10px; }
.level-label { font-weight: 700; color: #111827; font-size: 18px; }
.xp-label { color: #6B7280; font-size: 14px; }
.progress-track { background: #E5E7EB; height: 12px; border-radius: 6px; overflow: hidden; }
.progress-fill {
  height: 100%; background: linear-gradient(90deg, #F59E0B, #FBBF24);
  border-radius: 6px; transition: width 0.4s ease;
}
.next-level { color: #6B7280; font-size: 13px; margin-top: 10px; margin-bottom: 0; }

.badges-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 16px; margin-top: 16px; }
.badge-card { background: white; border-radius: 12px; padding: 20px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.badge-card.locked { opacity: 0.5; }
.badge-icon { font-size: 32px; margin-bottom: 8px; }
</style>