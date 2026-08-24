import CourseListView from '../views/CourseListView.vue'
import { createRouter, createWebHistory } from 'vue-router'
import ProfileView from '../views/ProfileView.vue'
import LeaderboardView from '../views/LeaderboardView.vue'
import QuizView from '../views/QuizView.vue'
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import DashboardView from '../views/DashboardView.vue'
import CourseDetailView from '../views/CourseDetailView.vue'
import TeacherDashboardView from '../views/TeacherDashboardView.vue'
import TeacherCourseFormView from '../views/TeacherCourseFormView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
{ path: '/teacher/dashboard', name: 'teacher-dashboard', component: TeacherDashboardView, meta: { requiresAuth: true } },
{ path: '/teacher/courses/:id', name: 'teacher-course-form', component: TeacherCourseFormView, meta: { requiresAuth: true } },
{ path: '/profile', name: 'profile', component: ProfileView, meta: { requiresAuth: true } },
{ path: '/leaderboard', name: 'leaderboard', component: LeaderboardView, meta: { requiresAuth: true } },
{ path: '/quizzes/:quizId', name: 'quiz', component: QuizView, meta: { requiresAuth: true } },
    { path: '/', redirect: '/login' },
    { path: '/login', name: 'login', component: LoginView },
    { path: '/register', name: 'register', component: RegisterView },
    { path: '/dashboard', name: 'dashboard', component: DashboardView, meta: { requiresAuth: true } },
    { path: '/courses/:id', name: 'course-detail', component: CourseDetailView, meta: { requiresAuth: true } },
    { path: '/courses', name: 'course-list', component: CourseListView, meta: { requiresAuth: true } },
  ],
})

router.beforeEach((to) => {
  const token = localStorage.getItem('token')
  if (to.meta.requiresAuth && !token) {
    return '/login'
  }
})

export default router