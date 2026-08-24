<script setup lang="ts">
import { ref } from 'vue'
import api from '../services/api'

const isOpen = ref(false)
const messages = ref<{ role: string; text: string }[]>([])
const input = ref('')
const loading = ref(false)

async function send() {
  if (!input.value.trim()) return
  const userMsg = input.value
  messages.value.push({ role: 'user', text: userMsg })
  input.value = ''
  loading.value = true
  try {
    const response = await api.post('/chat', { message: userMsg })
    messages.value.push({ role: 'ai', text: response.data.response })
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="chat-widget">
    <button class="chat-toggle" @click="isOpen = !isOpen">💬</button>
    <div class="chat-box" v-if="isOpen">
      <div class="chat-header">Assistant IA</div>
      <div class="chat-messages">
        <div v-for="(m, i) in messages" :key="i" :class="['msg', m.role]">{{ m.text }}</div>
        <div v-if="loading" class="msg ai">...</div>
      </div>
      <div class="chat-input">
        <input v-model="input" @keyup.enter="send" placeholder="Pose ta question..." />
        <button @click="send">Envoyer</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.chat-widget { position: fixed; bottom: 24px; right: 24px; z-index: 1000; }
.chat-toggle { width: 56px; height: 56px; border-radius: 50%; background: #4F46E5; color: white; border: none; font-size: 24px; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
.chat-box { position: absolute; bottom: 70px; right: 0; width: 320px; height: 420px; background: white; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.15); display: flex; flex-direction: column; overflow: hidden; }
.chat-header { background: #4F46E5; color: white; padding: 12px 16px; font-weight: bold; }
.chat-messages { flex: 1; padding: 12px; overflow-y: auto; display: flex; flex-direction: column; gap: 8px; }
.msg { padding: 8px 12px; border-radius: 10px; max-width: 80%; font-size: 14px; }
.msg.user { background: #4F46E5; color: white; align-self: flex-end; }
.msg.ai { background: #F3F4F6; align-self: flex-start; }
.chat-input { display: flex; padding: 8px; border-top: 1px solid #E5E7EB; }
.chat-input input { flex: 1; border: 1px solid #D1D5DB; border-radius: 8px; padding: 8px; margin-right: 8px; }
.chat-input button { background: #4F46E5; color: white; border: none; border-radius: 8px; padding: 8px 12px; cursor: pointer; }
</style>