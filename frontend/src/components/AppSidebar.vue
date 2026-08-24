<script setup lang="ts">
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import AppLogo from './AppLogo.vue'
import {
  LayoutDashboard, BookOpen, Trophy, User, LogOut, GraduationCap
} from 'lucide-vue-next'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

function handleLogout() {
  authStore.logout()
  router.push('/login')
}

function isActive(path: string) {
  return route.path === path
}
</script>

<template>
  <aside class="sidebar">
    <div class="sidebar-top">
      <AppLogo :size="26" />

      <nav class="sidebar-nav" v-if="authStore.user">
        <template v-if="authStore.user.role === 'student'">
          <router-link to="/dashboard" :class="['nav-item', { active: isActive('/dashboard') }]">
            <LayoutDashboard :size="18" /> Mes cours
          </router-link>
          <router-link to="/leaderboard" :class="['nav-item', { active: isActive('/leaderboard') }]">
            <Trophy :size="18" /> Classement
          </router-link>
          <router-link to="/profile" :class="['nav-item', { active: isActive('/profile') }]">
            <User :size="18" /> Mon profil
          </router-link>
        </template>

        <template v-else-if="authStore.user.role === 'teacher'">
          <router-link to="/teacher/dashboard" :class="['nav-item', { active: isActive('/teacher/dashboard') }]">
            <BookOpen :size="18" /> Mes cours
          </router-link>
        </template>
      </nav>
    </div>

    <div class="sidebar-bottom" v-if="authStore.user">
      <div class="user-info">
        <div class="avatar">
          <GraduationCap :size="16" v-if="authStore.user.role === 'student'" />
          <span v-else>👨‍🏫</span>
        </div>
        <div class="user-text">
          <strong>{{ authStore.user.name }}</strong>
          <span v-if="authStore.user.role === 'student'">Niv. {{ authStore.user.level }} · {{ authStore.user.xp }} XP</span>
          <span v-else>Enseignant</span>
        </div>
      </div>
      <button class="logout-btn" @click="handleLogout">
        <LogOut :size="16" /> Déconnexion
      </button>
    </div>
  </aside>
</template>

<style scoped>
.sidebar {
  width: 240px; height: 100vh; background: white; border-right: 1px solid #E5E7EB;
  display: flex; flex-direction: column; justify-content: space-between;
  position: fixed; left: 0; top: 0; padding: 24px 16px; box-sizing: border-box;
}
.sidebar-nav { margin-top: 40px; display: flex; flex-direction: column; gap: 4px; }
.nav-item {
  display: flex; align-items: center; gap: 10px; padding: 10px 12px;
  border-radius: 8px; color: #374151; text-decoration: none; font-size: 14px; font-weight: 500;
}
.nav-item:hover { background: #F3F4F6; }
.nav-item.active { background: #EEF2FF; color: #4F46E5; font-weight: 700; }
.user-info { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-top: 1px solid #E5E7EB; padding-top: 16px; }
.avatar { width: 32px; height: 32px; border-radius: 50%; background: #EEF2FF; color: #4F46E5; display: flex; align-items: center; justify-content: center; }
.user-text { display: flex; flex-direction: column; font-size: 12px; }
.user-text strong { font-size: 13px; color: #111827; }
.user-text span { color: #6B7280; }
.logout-btn {
  width: 100%; margin-top: 12px; display: flex; align-items: center; justify-content: center; gap: 8px;
  background: none; border: 1px solid #D1D5DB; padding: 8px; border-radius: 8px; cursor: pointer;
  color: #374151; font-size: 13px;
}
</style>