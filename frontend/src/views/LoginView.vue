<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const email = ref('')
const password = ref('')
const errorMessage = ref('')
const loading = ref(false)

const authStore = useAuthStore()
const router = useRouter()

async function handleLogin() {
  errorMessage.value = ''
  loading.value = true
  try {
    await authStore.login(email.value, password.value)
    router.push(authStore.user?.role === 'teacher' ? '/teacher/dashboard' : '/dashboard')
  } catch (error: any) {
    errorMessage.value = error.response?.data?.message || 'Identifiants invalides.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="login-page">
    <div class="login-card">
      <h1>🎓 Connexion à la Plateforme</h1>
      <p class="subtitle">Accédez à votre espace d'apprentissage</p>

      <form @submit.prevent="handleLogin">
        <label>Email</label>
        <input type="email" v-model="email" placeholder="exemple@email.com" required />

        <label>Mot de passe</label>
        <input type="password" v-model="password" placeholder="Mot de passe" required />

        <p v-if="errorMessage" class="error">{{ errorMessage }}</p>

        <button type="submit" :disabled="loading">
          {{ loading ? 'Connexion...' : 'Se connecter' }}
        </button>
      </form>

      <p class="switch-link">
        Pas de compte ? <router-link to="/register">S'inscrire</router-link>
      </p>
    </div>
  </div>
</template>

<style scoped>
.login-page {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  min-height: 100vh;
  background-color: #f9fafb;
}

.login-card {
  background: white;
  padding: 40px;
  border-radius: 16px;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
  width: 100%;
  max-width: 400px;
}

h1 {
  font-size: 24px;
  color: #4f46e5;
  text-align: center;
  margin-bottom: 8px;
}

.subtitle {
  text-align: center;
  color: #6b7280;
  margin-bottom: 24px;
}

label {
  display: block;
  margin-top: 16px;
  margin-bottom: 4px;
  font-size: 14px;
  color: #374151;
}

input {
  width: 100%;
  padding: 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  box-sizing: border-box;
  font-size: 14px;
}

button {
  width: 100%;
  margin-top: 24px;
  padding: 12px;
  background: #4f46e5;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 15px;
  font-weight: bold;
  cursor: pointer;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error {
  color: #ef4444;
  font-size: 14px;
  margin-top: 12px;
}

.switch-link {
  text-align: center;
  margin-top: 20px;
  font-size: 14px;
  color: #6b7280;
}

.switch-link a {
  color: #4f46e5;
  font-weight: bold;
  text-decoration: none;
}
</style>