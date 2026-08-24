<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '../services/api'

const leaderboard = ref<any[]>([])

onMounted(async () => {
  const response = await api.get('/leaderboard')
  leaderboard.value = response.data
})
</script>

<template>
  <div class="page">
    <nav class="navbar"><div class="logo">🎓 LearnQuest</div></nav>
    <div class="content">
      <h1>Classement général</h1>
      <div class="list">
        <div class="row" v-for="(u, i) in leaderboard" :key="u.id">
          <span class="rank">{{ i === 0 ? '🥇' : i === 1 ? '🥈' : i === 2 ? '🥉' : `#${i + 1}` }}</span>
          <span class="name">{{ u.name }}</span>
          <span class="xp">{{ u.xp }} XP</span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.page { background: #F9FAFB; min-height: 100vh; }
.navbar { background: white; padding: 16px 40px; border-bottom: 1px solid #E5E7EB; }
.logo { font-weight: bold; color: #4F46E5; font-size: 20px; }
.content { padding: 40px; max-width: 600px; }
.list { margin-top: 16px; }
.row { background: white; padding: 14px 20px; border-radius: 10px; margin-bottom: 8px; display: flex; align-items: center; gap: 16px; }
.rank { font-weight: bold; width: 30px; }
.name { flex: 1; }
.xp { font-weight: bold; color: #4F46E5; }
</style>