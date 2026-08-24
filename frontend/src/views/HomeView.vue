<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '../services/api'

const message = ref('Chargement...')

onMounted(async () => {
  try {
    const response = await api.get('/courses')
    message.value = `Connexion réussie ! ${response.data.length ?? 0} cours trouvé(s).`
  } catch (error: any) {
    message.value = `Erreur de connexion : ${error.response?.status ?? error.message}`
  }
})
</script>

<template>
  <div style="padding: 40px; font-family: sans-serif;">
    <h1>Plateforme d'apprentissage</h1>
    <p>{{ message }}</p>
  </div>
</template>