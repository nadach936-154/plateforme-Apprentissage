<script setup lang="ts">
import { ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../services/api'
import AppLayout from '../components/AppLayout.vue'
import { Trash2, Sparkles, Plus, Save } from 'lucide-vue-next'

interface Answer {
  answer_text: string
  is_correct: boolean
}

interface Question {
  question_text: string
  answers: Answer[]
}

const route = useRoute()
const router = useRouter()

const activeTab = ref<'info' | 'chapters' | 'quizzes'>('info')

const isNew = ref(route.params.id === 'new')
const title = ref('')
const description = ref('')
const content = ref('')
const aiSummary = ref('')
const quizzes = ref<any[]>([])
const chapters = ref<any[]>([])
const courseId = ref<number | null>(isNew.value ? null : Number(route.params.id))
const generatingSummary = ref(false)
const generatingQuiz = ref(false)
const saving = ref(false)

const newChapterTitle = ref('')
const newChapterContent = ref('')
const addingChapter = ref(false)

const showManualQuizForm = ref(false)
const newQuizTitle = ref('')
const newQuizQuestions = ref<Question[]>([])
const savingQuiz = ref(false)

async function loadCourse() {
  isNew.value = route.params.id === 'new'
  courseId.value = isNew.value ? null : Number(route.params.id)
  if (!isNew.value) {
    const response = await api.get(`/courses/${route.params.id}`)
    const c = response.data.data
    title.value = c.title
    description.value = c.description
    content.value = c.content
    aiSummary.value = c.ai_summary
    quizzes.value = c.quizzes || []
    const chaptersResponse = await api.get(`/courses/${route.params.id}/chapters`)
    chapters.value = chaptersResponse.data
  }
}
watch(() => route.params.id, loadCourse, { immediate: true })

async function saveCourse() {
  saving.value = true
  try {
    if (isNew.value) {
      const response = await api.post('/courses', { title: title.value, description: description.value, content: content.value })
      router.push(`/teacher/courses/${response.data.data.id}`)
    } else {
      await api.put(`/courses/${courseId.value}`, { title: title.value, description: description.value, content: content.value })
    }
  } finally {
    saving.value = false
  }
}

async function deleteCourse() {
  if (!confirm('Supprimer ce cours définitivement ?')) return
  await api.delete(`/courses/${courseId.value}`)
  router.push('/teacher/dashboard')
}

async function generateSummary() {
  if (!courseId.value) return
  generatingSummary.value = true
  try {
    const response = await api.post(`/courses/${courseId.value}/generate-summary`)
    aiSummary.value = response.data.ai_summary
  } finally {
    generatingSummary.value = false
  }
}

async function generateQuiz() {
  if (!courseId.value) return
  generatingQuiz.value = true
  try {
    const response = await api.post(`/courses/${courseId.value}/generate-quiz`)
    quizzes.value.push(response.data)
  } finally {
    generatingQuiz.value = false
  }
}

async function addChapter() {
  if (!courseId.value || !newChapterTitle.value.trim()) return
  addingChapter.value = true
  try {
    const response = await api.post(`/courses/${courseId.value}/chapters`, {
      title: newChapterTitle.value, content: newChapterContent.value, order: chapters.value.length,
    })
    chapters.value.push(response.data)
    newChapterTitle.value = ''
    newChapterContent.value = ''
  } finally {
    addingChapter.value = false
  }
}

async function deleteChapter(chapterId: number) {
  if (!confirm('Supprimer ce chapitre ?')) return
  await api.delete(`/courses/${courseId.value}/chapters/${chapterId}`)
  chapters.value = chapters.value.filter((c) => c.id !== chapterId)
}

function addQuestionToForm() {
  newQuizQuestions.value.push({
    question_text: '',
    answers: [
      { answer_text: '', is_correct: true },
      { answer_text: '', is_correct: false },
      { answer_text: '', is_correct: false },
      { answer_text: '', is_correct: false },
    ],
  })
}

function removeQuestionFromForm(i: number) { 
  newQuizQuestions.value.splice(i, 1) 
}

function setCorrectAnswer(qi: number, ai: number) {
  const targetQuestion = newQuizQuestions.value[qi]
  if (targetQuestion) {
    targetQuestion.answers.forEach((a, i) => { a.is_correct = i === ai })
  }
}

function resetManualQuizForm() {
  newQuizTitle.value = ''
  newQuizQuestions.value = []
  showManualQuizForm.value = false
}

async function saveManualQuiz() {
  if (!courseId.value || !newQuizTitle.value.trim() || newQuizQuestions.value.length === 0) return
  savingQuiz.value = true
  try {
    const quizResponse = await api.post(`/courses/${courseId.value}/quizzes`, { title: newQuizTitle.value })
    const quizId = quizResponse.data.id
    for (const q of newQuizQuestions.value) {
      const questionResponse = await api.post(`/quizzes/${quizId}/questions`, { question_text: q.question_text, type: 'single_choice' })
      for (const a of q.answers) {
        await api.post(`/questions/${questionResponse.data.id}/answers`, { answer_text: a.answer_text, is_correct: a.is_correct })
      }
    }
    quizzes.value.push(quizResponse.data)
    resetManualQuizForm()
  } finally {
    savingQuiz.value = false
  }
}
</script>

<template>
  <AppLayout>
    <div class="sticky-header">
      <h1>{{ isNew ? 'Nouveau cours' : title || 'Modifier le cours' }}</h1>
      <div class="header-actions">
        <button class="save-btn" @click="saveCourse" :disabled="saving">
          <Save :size="16" /> {{ saving ? 'Enregistrement...' : 'Enregistrer' }}
        </button>
        <button v-if="!isNew" class="delete-btn" @click="deleteCourse">
          <Trash2 :size="16" /> Supprimer
        </button>
      </div>
    </div>

    <div class="tabs">
      <button :class="['tab', { active: activeTab === 'info' }]" @click="activeTab = 'info'">Informations générales</button>
      <button :class="['tab', { active: activeTab === 'chapters' }]" @click="activeTab = 'chapters'" :disabled="isNew">Programme / Chapitres</button>
      <button :class="['tab', { active: activeTab === 'quizzes' }]" @click="activeTab = 'quizzes'" :disabled="isNew">Évaluations & Quiz</button>
    </div>

    <!-- TAB 1 : Informations générales -->
    <div v-if="activeTab === 'info'" class="tab-content">
      <label>Titre du cours</label>
      <input v-model="title" type="text" />
      <label>Description</label>
      <textarea v-model="description" rows="2"></textarea>
      <label>Contenu du cours</label>
      <textarea v-model="content" rows="8"></textarea>
    </div>

    <!-- TAB 2 : Chapitres -->
    <div v-if="activeTab === 'chapters'" class="tab-content">
      <div class="chapter-row" v-for="chapter in chapters" :key="chapter.id">
        <span>{{ chapter.order + 1 }}. {{ chapter.title }}</span>
        <button class="mini-danger" @click="deleteChapter(chapter.id)"><Trash2 :size="15" /></button>
      </div>
      <div class="add-form">
        <input v-model="newChapterTitle" type="text" placeholder="Titre du chapitre" />
        <textarea v-model="newChapterContent" rows="2" placeholder="Contenu (optionnel)"></textarea>
        <button class="outline-btn" @click="addChapter" :disabled="addingChapter">
          <Plus :size="15" /> {{ addingChapter ? 'Ajout...' : 'Ajouter un chapitre' }}
        </button>
      </div>
    </div>

    <!-- TAB 3 : Quiz -->
    <div v-if="activeTab === 'quizzes'" class="tab-content">
      <div class="ai-box">
        <strong><Sparkles :size="16" /> Aperçu rapide du cours</strong>
        <p v-if="aiSummary">{{ aiSummary }}</p>
        <button class="outline-btn" @click="generateSummary" :disabled="generatingSummary">
          {{ generatingSummary ? 'Génération...' : 'Générer aperçu' }}
        </button>
      </div>

      <div class="ai-box">
        <strong><Sparkles :size="16" /> Générateur automatique de quiz</strong>
        <ul v-if="quizzes.length"><li v-for="q in quizzes" :key="q.id">{{ q.title }}</li></ul>
        <button class="outline-btn" @click="generateQuiz" :disabled="generatingQuiz">
          {{ generatingQuiz ? 'Génération... (~20s)' : 'Générer un quiz' }}
        </button>
      </div>

      <div class="manual-section">
        <div class="header-row">
          <h3>Créer un quiz manuellement</h3>
          <button v-if="!showManualQuizForm" class="outline-btn" @click="showManualQuizForm = true">
            <Plus :size="15" /> Nouveau quiz
          </button>
        </div>

        <div v-if="showManualQuizForm">
          <input v-model="newQuizTitle" type="text" placeholder="Titre du quiz" />
          <div class="question-block" v-for="(q, qi) in newQuizQuestions" :key="qi">
            <div class="header-row">
              <label>Question {{ qi + 1 }}</label>
              <button class="mini-danger" @click="removeQuestionFromForm(qi)"><Trash2 :size="15" /></button>
            </div>
            <input v-model="q.question_text" type="text" placeholder="Énoncé" />
            <div class="answer-row" v-for="(a, ai) in q.answers" :key="ai">
              <input type="radio" :name="`c-${qi}`" :checked="a.is_correct" @change="setCorrectAnswer(qi, ai)" />
              <input v-model="a.answer_text" type="text" :placeholder="`Réponse ${ai + 1}`" />
            </div>
          </div>
          <button class="outline-btn" @click="addQuestionToForm"><Plus :size="15" /> Ajouter une question</button>
          <div class="quiz-actions">
            <button class="save-btn" @click="saveManualQuiz" :disabled="savingQuiz">
              {{ savingQuiz ? 'Enregistrement...' : 'Enregistrer le quiz' }}
            </button>
            <button class="cancel-btn" @click="resetManualQuizForm">Annuler</button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.sticky-header {
  position: sticky; top: 0; background: #F9FAFB; z-index: 10;
  display: flex; justify-content: space-between; align-items: center;
  padding: 16px 0; border-bottom: 1px solid #E5E7EB; margin-bottom: 20px;
}
.sticky-header h1 { margin: 0; font-size: 22px; color: #111827; }
.header-actions { display: flex; gap: 10px; }
.save-btn, .delete-btn, .outline-btn, .cancel-btn {
  display: flex; align-items: center; gap: 6px; padding: 9px 16px;
  border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 13px; border: none;
}
.save-btn { background: #4F46E5; color: white; }
.delete-btn { background: white; color: #EF4444; border: 1px solid #EF4444; }
.outline-btn { background: white; color: #4F46E5; border: 1px solid #4F46E5; margin-top: 12px; }
.cancel-btn { background: white; color: #374151; border: 1px solid #D1D5DB; }

.tabs { display: flex; gap: 4px; border-bottom: 1px solid #E5E7EB; margin-bottom: 24px; }
.tab { background: none; border: none; padding: 10px 18px; cursor: pointer; font-weight: 600; color: #6B7280; border-bottom: 2px solid transparent; }
.tab.active { color: #4F46E5; border-bottom-color: #4F46E5; }
.tab:disabled { opacity: 0.4; cursor: not-allowed; }

.tab-content { background: white; padding: 24px; border-radius: 12px; }
label { display: block; margin-top: 14px; margin-bottom: 4px; font-size: 13px; color: #374151; font-weight: 600; }
input[type="text"], textarea { width: 100%; padding: 10px 12px; border: 1px solid #D1D5DB; border-radius: 8px; box-sizing: border-box; font-family: inherit; }

.chapter-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; background: #F9FAFB; border-radius: 8px; margin-bottom: 8px; }
.mini-danger { background: none; border: none; cursor: pointer; color: #EF4444; }
.add-form { margin-top: 16px; display: flex; flex-direction: column; gap: 8px; }

.ai-box { background: #EEF2FF; padding: 18px; border-radius: 12px; margin-bottom: 16px; }
.ai-box strong { color: #4F46E5; display: flex; align-items: center; gap: 6px; margin-bottom: 8px; }
.manual-section { margin-top: 24px; }
.header-row { display: flex; justify-content: space-between; align-items: center; }
.question-block { background: #F9FAFB; padding: 14px; border-radius: 10px; margin-top: 14px; }
.answer-row { display: flex; align-items: center; gap: 8px; margin-top: 8px; }
.answer-row input[type="text"] { flex: 1; }
.quiz-actions { display: flex; gap: 10px; margin-top: 16px; }
</style>