<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import { useAuthStore } from '../stores/auth'
import AppLayout from '../components/AppLayout.vue'

interface Course {
  id: number
  title: string
  description: string
  teacher: { name: string }
  category?: string
}

const courses = ref<Course[]>([])
const loading = ref(true)
const authStore = useAuthStore()
const router = useRouter()

const categoryColors: Record<string, string> = {
  'Développement Web': '#4F46E5',
  'IoT': '#0D9488',
  'Intelligence Artificielle': '#7C3AED',
  'Bases de données': '#EA580C',
  'Cybersécurité': '#DC2626',
  'UI/UX Design': '#DB2777',
}

function categoryColor(cat?: string) {
  return categoryColors[cat || ''] || '#4F46E5'
}

onMounted(async () => {
  try {
    await authStore.fetchUser()
    const response = await api.get('/courses')
    courses.value = response.data.data
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <AppLayout>
    <div v-if="authStore.user">
      <h1>Bonjour, {{ authStore.user.name.split(' ')[0] }} 👋</h1>
      <p class="subtitle">Continuez votre progression d'aujourd'hui</p>

      <div class="progress-card">
        <div class="progress-top">
          <div>
            <span class="progress-label">Ma progression</span>
            <div class="level-title">Niveau {{ authStore.user.level }}</div>
          </div>
          <div class="progress-numbers">
            <div class="xp-current">{{ authStore.user.xp }} / {{ authStore.user.level * 100 }} XP</div>
            <div class="xp-remaining">{{ authStore.user.level * 100 - authStore.user.xp }} XP restants</div>
          </div>
        </div>
        <div class="progress-track">
          <div class="progress-fill" :style="{ width: (authStore.user.xp % 100) + '%' }"></div>
        </div>
        <div class="stats-row">
          <div class="stat"><strong>{{ courses.length }}</strong><span>Cours disponibles</span></div>
          <div class="stat"><strong>—</strong><span>Quiz passés</span></div>
          <div class="stat"><strong>—</strong><span>Badges débloqués</span></div>
          <div class="stat"><strong>—</strong><span>Classement</span></div>
        </div>
      </div>

      <div class="section-header">
        <h2>Mes cours en cours</h2>
        <router-link to="/courses" class="see-all">Voir tous →</router-link>
      </div>

      <p v-if="loading">Chargement...</p>
      <p v-else-if="courses.length === 0">Aucun cours disponible pour le moment.</p>

      <div class="course-grid" v-else>
        <div class="course-card" v-for="course in courses" :key="course.id" @click="router.push(`/courses/${course.id}`)">
          <div class="card-icon" :style="{ background: categoryColor(course.category) }">📘</div>
          <span class="category-tag" :style="{ color: categoryColor(course.category), background: categoryColor(course.category) + '1A' }">
            {{ course.category || 'Général' }}
          </span>
          <h3>{{ course.title }}</h3>
          <p class="teacher">{{ course.teacher?.name || 'Enseignant' }}</p>
          <button class="continue-btn">Continuer →</button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
h1 { color: #111827; margin-bottom: 4px; font-size: 26px; }
.subtitle { color: #6B7280; margin-bottom: 24px; }

.progress-card {
  background: linear-gradient(135deg, #4F46E5, #6366F1);
  border-radius: 16px; padding: 28px 32px; color: white;
}
.progress-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; }
.progress-label { font-size: 13px; opacity: 0.85; }
.level-title { font-size: 28px; font-weight: 800; }
.progress-numbers { text-align: right; }
.xp-current { font-weight: 700; }
.xp-remaining { font-size: 12px; opacity: 0.8; }
.progress-track { background: rgba(255,255,255,0.25); height: 10px; border-radius: 6px; overflow: hidden; margin-bottom: 20px; }
.progress-fill { height: 100%; background: #F59E0B; border-radius: 6px; transition: width 0.4s ease; }
.stats-row { display: flex; gap: 40px; }
.stat { display: flex; flex-direction: column; }
.stat strong { font-size: 22px; font-weight: 800; }
.stat span { font-size: 12px; opacity: 0.85; }

.section-header { display: flex; justify-content: space-between; align-items: center; margin: 32px 0 16px; }
.section-header h2 { color: #111827; margin: 0; font-size: 18px; }
.see-all { color: #4F46E5; font-size: 14px; font-weight: 600; text-decoration: none; }

.course-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
.course-card { background: white; border-radius: 14px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); cursor: pointer; }
.card-icon {
  width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center;
  justify-content: center; font-size: 20px; margin-bottom: 12px;
}
.category-tag { display: inline-block; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 20px; margin-bottom: 8px; }
.course-card h3 { margin: 0 0 4px; color: #111827; font-size: 16px; }
.teacher { color: #6B7280; font-size: 13px; margin: 0 0 14px; }
.continue-btn { width: 100%; background: #4F46E5; color: white; border: none; padding: 10px; border-radius: 8px; font-weight: 600; cursor: pointer; }
</style>