<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../services/api'

const route = useRoute()
const router = useRouter()
const quiz = ref<any>(null)
const currentIndex = ref(0)
const selectedAnswers = ref<Record<number, number>>({})
const submitting = ref(false)
const result = ref<any>(null)

const seconds = ref(0)
let timerInterval: ReturnType<typeof setInterval> | null = null

const letters = ['A', 'B', 'C', 'D', 'E', 'F']

onMounted(async () => {
  try {
    const response = await api.get(`/quizzes/${route.params.quizId}`)
    quiz.value = response.data
    timerInterval = setInterval(() => { seconds.value++ }, 1000)
  } catch (error) {
    console.error('Erreur lors du chargement du quiz :', error)
  }
})

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval)
})

function formattedTime() {
  const m = Math.floor(seconds.value / 60).toString().padStart(2, '0')
  const s = (seconds.value % 60).toString().padStart(2, '0')
  return `${m}:${s}`
}

function selectAnswer(questionId: number, answerId: number) {
  selectedAnswers.value[questionId] = answerId
}

function goPrevious() {
  if (currentIndex.value > 0) currentIndex.value--
}

function nextQuestion() {
  if (quiz.value?.questions && currentIndex.value < quiz.value.questions.length - 1) {
    currentIndex.value++
  }
}

async function submitQuiz() {
  if (timerInterval) clearInterval(timerInterval)
  submitting.value = true

  try {
    const answers = Object.entries(selectedAnswers.value).map(([question_id, answer_id]) => ({
      question_id: Number(question_id),
      answer_id,
    }))
    const response = await api.post(`/quizzes/${route.params.quizId}/attempt`, { answers })
    result.value = response.data
  } catch (error) {
    console.error('Erreur lors de l\'envoi des réponses :', error)
  } finally {
    submitting.value = false
  }
}
</script>

<template>
 <div class="quiz-page" v-if="quiz && quiz.questions && quiz.questions.length > 0 && !result">
    <div class="quiz-header">
      <button class="quit-btn" @click="router.back()">✕ Quitter</button>
      <div class="progress-info">
        <span class="q-count">Question {{ currentIndex + 1 }}/{{ quiz.questions.length }}</span>
        <div class="dots">
          <span
            v-for="(q, i) in quiz.questions"
            :key="i"
            :class="{ dot: true, done: Number(i) < currentIndex, active: Number(i) === currentIndex }"
          ></span>
        </div>
      </div>
      <div class="timer">⏱ {{ formattedTime() }}</div>
    </div>

    <div class="quiz-body" v-if="quiz.questions[currentIndex]">
      <span class="tag">{{ quiz.title }}</span>
      <h2>{{ quiz.questions[currentIndex].question_text }}</h2>

      <div
        v-for="(answer, i) in quiz.questions[currentIndex].answers"
        :key="answer.id"
        :class="['option', { selected: selectedAnswers[quiz.questions[currentIndex].id] === answer.id }]"
        @click="selectAnswer(quiz.questions[currentIndex].id, answer.id)"
      >
        <span class="letter">{{ letters[Number(i)] }}</span>
        {{ answer.answer_text }}
      </div>

      <div class="nav-buttons">
        <button class="prev-btn" @click="goPrevious" :disabled="currentIndex === 0">← Précédente</button>
        
        <button
          v-if="currentIndex < quiz.questions.length - 1"
          class="next-btn"
          @click="nextQuestion"
          :disabled="!selectedAnswers[quiz.questions[currentIndex].id]"
        >
          Question suivante →
        </button>

        <button
          v-else
          class="next-btn"
          @click="submitQuiz"
          :disabled="!selectedAnswers[quiz.questions[currentIndex].id] || submitting"
        >
          {{ submitting ? 'Envoi...' : 'Terminer le quiz' }}
        </button>
      </div>
    </div>
  </div>

  <div class="result-page" v-else-if="result">
    <div class="result-card">
      <div class="score-circle">{{ result.score }}/10</div>
      <h2>Excellent travail ! 🎉</h2>
      <p>Vous avez répondu correctement à {{ result.correct_answers }} questions sur {{ result.total_questions }}.</p>

      <div class="result-grid">
        <div class="result-box xp-box">
          <div class="big">⭐ +{{ result.xp_earned }} XP</div>
          <span>Points d'expérience gagnés</span>
        </div>
        <div class="result-box badge-box" v-if="result.badges_debloques?.length">
          <div class="big">🏆</div>
          <strong>Badge débloqué !</strong>
          <span>"{{ result.badges_debloques[0].name }}"</span>
        </div>
      </div>

      <div class="result-actions">
        <button class="outline-btn" @click="router.back()">Revoir les erreurs</button>
        <button class="primary-btn" @click="router.push('/dashboard')">Retour au cours</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.quiz-page { min-height: 100vh; background: #F9FAFB; }
.quiz-header { display: flex; justify-content: space-between; align-items: center; padding: 16px 40px; background: white; border-bottom: 1px solid #E5E7EB; }
.quit-btn { background: #FEE2E2; color: #EF4444; border: none; padding: 8px 14px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 13px; }
.progress-info { display: flex; flex-direction: column; align-items: center; gap: 6px; }
.q-count { font-weight: 700; color: #111827; font-size: 14px; }
.dots { display: flex; gap: 4px; }
.dot { width: 24px; height: 5px; border-radius: 3px; background: #E5E7EB; }
.dot.done { background: #10B981; }
.dot.active { background: #4F46E5; }
.timer { color: #F59E0B; font-weight: 700; font-size: 14px; }

.quiz-body { max-width: 700px; margin: 60px auto; background: white; border-radius: 16px; padding: 40px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
.tag { background: #EEF2FF; color: #4F46E5; font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 20px; }
h2 { color: #111827; margin: 14px 0 24px; }
.option {
  display: flex; align-items: center; gap: 14px; padding: 16px; border: 1px solid #D1D5DB;
  border-radius: 10px; margin-bottom: 12px; cursor: pointer; color: #111827;
}
.option.selected { border-color: #4F46E5; background: #EEF2FF; color: #4F46E5; font-weight: 600; }
.letter {
  width: 28px; height: 28px; border-radius: 50%; background: #F3F4F6; color: #6B7280;
  display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; flex-shrink: 0;
}
.option.selected .letter { background: #4F46E5; color: white; }
.nav-buttons { display: flex; justify-content: space-between; margin-top: 24px; }
.prev-btn { background: none; border: none; color: #6B7280; font-weight: 600; cursor: pointer; }
.prev-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.next-btn { background: #4F46E5; color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 700; cursor: pointer; }
.next-btn:disabled { opacity: 0.5; cursor: not-allowed; }

.result-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #F9FAFB; }
.result-card { background: white; border-radius: 16px; padding: 48px; width: 480px; text-align: center; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
.score-circle {
  width: 100px; height: 100px; border-radius: 50%; background: #10B981; color: white;
  display: flex; align-items: center; justify-content: center; font-size: 26px; font-weight: 800;
  margin: 0 auto 20px;
}
.result-card h2 { color: #111827; margin: 0 0 8px; }
.result-card p { color: #6B7280; margin: 0 0 24px; }
.result-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 24px; }
.result-box { border-radius: 12px; padding: 16px; }
.xp-box { background: #FEF3C7; color: #B45309; }
.badge-box { background: #EEF2FF; color: #4F46E5; }
.big { font-size: 20px; font-weight: 800; margin-bottom: 4px; }
.result-box span { font-size: 12px; }
.result-actions { display: flex; gap: 12px; }
.outline-btn { flex: 1; background: white; border: 1px solid #4F46E5; color: #4F46E5; padding: 12px; border-radius: 8px; font-weight: 700; cursor: pointer; }
.primary-btn { flex: 1; background: #4F46E5; color: white; border: none; padding: 12px; border-radius: 8px; font-weight: 700; cursor: pointer; }
</style>