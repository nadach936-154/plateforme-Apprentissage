<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const role = ref<'student' | 'teacher'>('student')
const errorMessage = ref('')
const loading = ref(false)

const authStore = useAuthStore()
const router = useRouter()

async function handleRegister() {
  errorMessage.value = ''
  loading.value = true
  try {
    await authStore.register(name.value, email.value, password.value, passwordConfirmation.value, role.value)
    router.push('/dashboard')
  } catch (error: any) {
    const errors = error.response?.data?.errors
    errorMessage.value = errors ? Object.values(errors).flat().join(' ') : 'Erreur lors de l\'inscription.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="login-page">
    <div class="login-card">
      <h1>🎓 Créer un compte</h1>
      <p class="subtitle">Rejoignez la plateforme d'apprentissage</p>

      <form @submit.prevent="handleRegister">
        <label>Nom complet</label>
        <input type="text" v-model="name" required />

        <label>Email</label>
        <input type="email" v-model="email" required />

        <label>Mot de passe</label>
        <input type="password" v-model="password" required />

        <label>Confirmer le mot de passe</label>
        <input type="password" v-model="passwordConfirmation" required />

        <label>Je suis :</label>
        <div class="role-selector">
          <div :class="['role-card', { active: role === 'student' }]" @click="role = 'student'">
            🎓 Étudiant
          </div>
          <div :class="['role-card', { active: role === 'teacher' }]" @click="role = 'teacher'">
            👨‍🏫 Enseignant
          </div>
        </div>

        <p v-if="errorMessage" class="error">{{ errorMessage }}</p>

        <button type="submit" :disabled="loading">
          {{ loading ? 'Inscription...' : 'S\'inscrire' }}
        </button>
      </form>

      <p class="switch-link">
        Déjà un compte ? <router-link to="/login">Se connecter</router-link>
      </p>
    </div>
  </div>
</template>

<style scoped>
.login-page {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background: #F9FAFB;
}
.login-card {
  background: white;
  padding: 40px;
  border-radius: 16px;
  box-shadow: 0 4px 24px rgba(0,0,0,0.1);
  width: 420px;
}
h1 { font-size: 24px; color: #4F46E5; text-align: center; margin-bottom: 8px; }
.subtitle { text-align: center; color: #6B7280; margin-bottom: 24px; }
label { display: block; margin-top: 16px; margin-bottom: 4px; font-size: 14px; color: #374151; }
input {
  width: 100%; padding: 12px; border: 1px solid #D1D5DB;
  border-radius: 8px; box-sizing: border-box; font-size: 14px;
}
.role-selector { display: flex; gap: 12px; }
.role-card {
  flex: 1; padding: 16px; border: 2px solid #D1D5DB; border-radius: 10px;
  text-align: center; cursor: pointer; font-weight: bold; color: #374151;
}
.role-card.active { border-color: #4F46E5; background: #EEF2FF; color: #4F46E5; }
button {
  width: 100%; margin-top: 24px; padding: 12px; background: #4F46E5;
  color: white; border: none; border-radius: 8px; font-size: 15px;
  font-weight: bold; cursor: pointer;
}
button:disabled { opacity: 0.6; cursor: not-allowed; }
.error { color: #EF4444; font-size: 14px; margin-top: 12px; }
.switch-link { text-align: center; margin-top: 20px; font-size: 14px; color: #6B7280; }
.switch-link a { color: #4F46E5; font-weight: bold; text-decoration: none; }
</style>