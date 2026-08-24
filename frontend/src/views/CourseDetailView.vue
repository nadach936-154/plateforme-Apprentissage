<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../services/api'
import AppLayout from '../components/AppLayout.vue'

const route = useRoute()
const router = useRouter()
const course = ref<any>(null)
const loading = ref(true)

onMounted(async () => {
  try {
    const response = await api.get(`/courses/${route.params.id}`)
    course.value = response.data.data
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <AppLayout>
    <div v-if="course">
      <div class="header">
        <div class="icon">📘</div>
        <div>
          <span class="category-tag">{{ course.category || 'Général' }}</span>
          <h1>{{ course.title }}</h1>
          <p class="meta">Par {{ course.teacher?.name || 'Enseignant' }} · {{ course.chapters?.length || 0 }} chapitres</p>
        </div>
      </div>

      <div class="columns">
        <div class="left-col">
          <div class="box">
            <h3>Description</h3>
            <p>{{ course.description }}</p>
          </div>

          <div class="box">
            <h3>Chapitres</h3>
            <div class="chapter-row" v-for="(ch, i) in course.chapters" :key="ch.id">
              <div class="chapter-check">✓</div>
              <span>{{ ch.title }}</span>
            </div>
            <p v-if="!course.chapters?.length" class="empty">Aucun chapitre pour l'instant.</p>
          </div>
        </div>

        <div class="right-col">
          <div class="box ai-box" v-if="course.ai_summary">
            <div class="ai-header">
              <h3>✨ Résumé généré par IA</h3>
              <span class="ai-tag">IA</span>
            </div>
            <p>{{ course.ai_summary }}</p>
          </div>

          <div class="box">
            <h3>Quiz disponibles</h3>
            <div class="quiz-row" v-for="quiz in course.quizzes" :key="quiz.id">
              <div>
                <strong>{{ quiz.title }}</strong>
                <p class="quiz-meta">{{ quiz.questions?.length || 0 }} questions</p>
              </div>
              <button @click="router.push(`/quizzes/${quiz.id}`)">Passer le quiz</button>
            </div>
            <p v-if="!course.quizzes?.length" class="empty">Aucun quiz pour l'instant.</p>
          </div>
        </div>
      </div>
    </div>
    <p v-else-if="loading">Chargement...</p>
  </AppLayout>
</template>

<style scoped>
.header { display: flex; gap: 16px; margin-bottom: 24px; align-items: flex-start; }
.icon { width: 56px; height: 56px; background: #4F46E5; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 24px; color: white; flex-shrink: 0; }
.category-tag { display: inline-block; font-size: 11px; font-weight: 700; color: #4F46E5; background: #EEF2FF; padding: 3px 10px; border-radius: 20px; margin-bottom: 6px; }
h1 { margin: 0 0 4px; color: #111827; font-size: 22px; }
.meta { color: #6B7280; font-size: 13px; margin: 0; }

.columns { display: grid; grid-template-columns: 1.6fr 1fr; gap: 24px; align-items: start; }
.box { background: white; border-radius: 14px; padding: 22px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
.box h3 { margin: 0 0 14px; color: #111827; font-size: 15px; }
.box p { color: #374151; line-height: 1.6; margin: 0; }
.empty { color: #9CA3AF; font-size: 13px; }

.chapter-row { display: flex; align-items: center; gap: 10px; padding: 10px 0; border-bottom: 1px solid #F3F4F6; color: #374151; }
.chapter-row:last-child { border-bottom: none; }
.chapter-check { width: 22px; height: 22px; background: #D1FAE5; color: #10B981; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; flex-shrink: 0; }

.ai-box { background: #EEF2FF; }
.ai-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; }
.ai-header h3 { margin: 0; color: #4F46E5; }
.ai-tag { background: #4F46E5; color: white; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 10px; }
.ai-box p { color: #374151; }

.quiz-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid #F3F4F6; }
.quiz-row:last-child { border-bottom: none; }
.quiz-row strong { color: #111827; font-size: 14px; }
.quiz-meta { color: #9CA3AF; font-size: 12px; margin: 2px 0 0; }
.quiz-row button { background: #4F46E5; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; white-space: nowrap; }
</style>