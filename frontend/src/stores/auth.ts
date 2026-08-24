import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../services/api'

interface User {
  id: number
  name: string
  email: string
  role: 'teacher' | 'student'
  xp: number
  level: number
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('token'))

  async function login(email: string, password: string) {
    const response = await api.post('/login', { email, password })
    token.value = response.data.token
    user.value = response.data.user
    localStorage.setItem('token', response.data.token)
  }

  async function register(name: string, email: string, password: string, password_confirmation: string, role: string) {
    const response = await api.post('/register', {
      name,
      email,
      password,
      password_confirmation,
      role,
    })
    token.value = response.data.token
    user.value = response.data.user
    localStorage.setItem('token', response.data.token)
  }

  async function fetchUser() {
    const response = await api.get('/me')
    user.value = response.data
  }

  function logout() {
    api.post('/logout').catch(() => {})
    user.value = null
    token.value = null
    localStorage.removeItem('token')
  }

  return { user, token, login, register, fetchUser, logout }
})