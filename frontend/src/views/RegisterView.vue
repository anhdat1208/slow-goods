<script setup lang="ts">
import { reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { ApiError } from '../api/client'
import { useAuthStore } from '../stores/auth'
import { useI18n } from '../i18n'

const auth = useAuthStore()
const router = useRouter()
const { t } = useI18n()
const error = ref('')
const form = reactive({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
})

async function submit() {
  error.value = ''
  try {
    await auth.register(form)
    router.push('/')
  } catch (e) {
    error.value = e instanceof ApiError ? Object.values(e.errors || { email: [e.message] }).flat().join(' ') : t('register_failed')
  }
}
</script>

<template>
  <section class="section">
    <div class="container narrow">
      <p class="eyebrow">{{ t('join') }}</p>
      <h2>{{ t('create_account') }}</h2>
      <form class="form" @submit.prevent="submit">
        <div class="field"><label>{{ t('name') }}</label><input v-model="form.name" required /></div>
        <div class="field"><label>{{ t('email') }}</label><input v-model="form.email" type="email" required /></div>
        <div class="field"><label>{{ t('phone') }}</label><input v-model="form.phone" /></div>
        <div class="field"><label>{{ t('password') }}</label><input v-model="form.password" type="password" required /></div>
        <div class="field"><label>{{ t('confirm_password') }}</label><input v-model="form.password_confirmation" type="password" required /></div>
        <p v-if="error" class="error-text">{{ error }}</p>
        <button class="btn" type="submit" :disabled="auth.loading">{{ t('register') }}</button>
      </form>
      <p class="muted">{{ t('have_account') }} <RouterLink to="/login">{{ t('sign_in') }}</RouterLink></p>
    </div>
  </section>
</template>

<style scoped>
.narrow { max-width: 460px; }
.form { display: grid; gap: 0.9rem; margin: 1.2rem 0; }
</style>
