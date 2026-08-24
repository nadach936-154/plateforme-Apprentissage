<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import { useAuthStore } from '../stores/auth'
import AppLayout from '../components/AppLayout.vue'

const courses = ref<any[]>([])
const authStore = useAuthStore()
const router = useRouter()

onMounted(async () => {
  await authStore.fetchUser()
  const response = await api.get('/courses')
  courses.value = response.data.data.filter((c: any) => c.teacher.id === authStore.user?.id)
})

async function deleteCourse(id: number, event: Event) {
  event.stopPropagation()
  if (!confirm('Supprimer ce cours ?')) return
  await api.delete(`/courses/${id}`)
  courses.value = courses.value.filter((c) => c.id !== id)
}
</script>

<template>
  <AppLayout>
    <div v-if="authStore.user">
      <div class="header-row">
        <div>
          <h1>Bonjour, {{ authStore.user.name }} 👋</h1>
          <p class="subtitle">Voici un aperçu de votre activité pédagogique</p>
        </div>
        <button class="create-btn" @click="router.push('/teacher/courses/new')">+ Créer un cours</button>
      </div>

      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon">📚</div>
          <div>
            <div class="stat-number">{{ courses.length }}</div>
            <div class="stat-label">Cours créés</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">👥</div>
          <div>
            <div class="stat-number">—</div>
            <div class="stat-label">Étudiants inscrits</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon">🏆</div>
          <div>
            <div class="stat-number">—</div>
            <div class="stat-label">Taux de réussite</div>
          </div>
        </div>
      </div>

      <div class="section-header">
        <h2>Mes cours</h2>
        <router-link to="/teacher/courses" class="see-all">Voir tous →</router-link>
      </div>

      <div class="course-grid">
        <div class="course-card" v-for="course in courses" :key="course.id" @click="router.push(`/teacher/courses/${course.id}`)">
          <span class="category-tag">{{ course.category || 'Général' }}</span>
          <h3>{{ course.title }}</h3>
          <p class="desc">{{ course.description }}</p>
          <div class="card-actions">
            <button @click.stop="router.push(`/teacher/courses/${course.id}`)">✏️ Modifier</button>
            <button class="danger" @click="deleteCourse(course.id, $event)">🗑️ Supprimer</button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.header-row { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px; }
h1 { color: #111827; margin: 0 0 4px; font-size: 24px; }
.subtitle { color: #6B7280; margin: 0; }
.create-btn { background: #4F46E5; color: white; border: none; padding: 12px 20px; border-radius: 10px; font-weight: 700; cursor: pointer; }

.stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 32px; }
.stat-card { background: white; border-radius: 14px; padding: 20px; display: flex; align-items: center; gap: 14px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.stat-icon { width: 44px; height: 44px; background: #F3F4F6; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
.stat-number { font-size: 22px; font-weight: 800; color: #111827; }
.stat-label { color: #6B7280; font-size: 13px; }

.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
.section-header h2 { color: #111827; margin: 0; font-size: 18px; }
.see-all { color: #4F46E5; font-size: 14px; font-weight: 600; text-decoration: none; }

.course-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
.course-card { background: white; border-radius: 14px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); cursor: pointer; }
.category-tag { display: inline-block; font-size: 11px; font-weight: 700; color: #4F46E5; background: #EEF2FF; padding: 3px 10px; border-radius: 20px; margin-bottom: 10px; }
.course-card h3 { margin: 0 0 6px; color: #111827; font-size: 16px; }
.desc { color: #6B7280; font-size: 13px; margin: 0 0 16px; }
.card-actions { display: flex; gap: 8px; }
.card-actions button { flex: 1; padding: 8px; font-size: 12px; border-radius: 8px; border: 1px solid #D1D5DB; background: white; cursor: pointer; }
.card-actions button.danger { color: #EF4444; border-color: #EF4444; }
</style>