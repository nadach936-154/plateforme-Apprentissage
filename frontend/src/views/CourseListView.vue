<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import AppLayout from '../components/AppLayout.vue'

const courses = ref<any[]>([])
const search = ref('')
const activeCategory = ref('Tous')
const router = useRouter()

const categories = ['Tous', 'Développement Web', 'IoT', 'Intelligence Artificielle', 'Bases de données', 'Cybersécurité', 'UI/UX Design']

onMounted(async () => {
  const response = await api.get('/courses')
  courses.value = response.data.data
})

const filteredCourses = computed(() => {
  return courses.value.filter((c) => {
    const matchCategory = activeCategory.value === 'Tous' || c.category === activeCategory.value
    const matchSearch = c.title.toLowerCase().includes(search.value.toLowerCase())
    return matchCategory && matchSearch
  })
})
</script>

<template>
  <AppLayout>
    <h1>Tous les cours</h1>

    <div class="search-bar">
      <span>🔍</span>
      <input v-model="search" type="text" placeholder="Rechercher un cours..." />
    </div>

    <div class="filters">
      <button
        v-for="cat in categories"
        :key="cat"
        :class="['filter-btn', { active: activeCategory === cat }]"
        @click="activeCategory = cat"
      >
        {{ cat }}
      </button>
    </div>

    <div class="course-grid">
      <div class="course-card" v-for="course in filteredCourses" :key="course.id" @click="router.push(`/courses/${course.id}`)">
        <span class="category-tag">{{ course.category || 'Général' }}</span>
        <h3>{{ course.title }}</h3>
        <p class="teacher">{{ course.teacher?.name }}</p>
        <button class="continue-btn">S'inscrire</button>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
h1 { color: #111827; margin-bottom: 20px; }
.search-bar { display: flex; align-items: center; gap: 10px; background: white; border: 1px solid #D1D5DB; border-radius: 10px; padding: 12px 16px; max-width: 500px; margin-bottom: 20px; }
.search-bar input { border: none; outline: none; flex: 1; font-size: 14px; }
.filters { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 24px; }
.filter-btn { background: white; border: 1px solid #D1D5DB; padding: 8px 16px; border-radius: 20px; cursor: pointer; font-size: 13px; color: #374151; }
.filter-btn.active { background: #4F46E5; color: white; border-color: #4F46E5; }

.course-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
.course-card { background: white; border-radius: 14px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); cursor: pointer; }
.category-tag { display: inline-block; font-size: 11px; font-weight: 700; color: #4F46E5; background: #EEF2FF; padding: 3px 10px; border-radius: 20px; margin-bottom: 10px; }
.course-card h3 { margin: 0 0 4px; color: #111827; font-size: 16px; }
.teacher { color: #6B7280; font-size: 13px; margin: 0 0 14px; }
.continue-btn { width: 100%; background: #4F46E5; color: white; border: none; padding: 10px; border-radius: 8px; font-weight: 600; cursor: pointer; }
</style>