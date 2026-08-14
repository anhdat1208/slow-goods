<script setup lang="ts">
import { reactive, ref } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { ApiError } from '../api/client'
import { useAuthStore } from '../stores/auth'
import { useCartStore } from '../stores/cart'
import { useWishlistStore } from '../stores/wishlist'
import { useI18n } from '../i18n'

const auth = useAuthStore()
const cart = useCartStore()
const wishlist = useWishlistStore()
const router = useRouter()
const route = useRoute()
const { t } = useI18n()
const error = ref('')
const form = reactive({ email: '', password: '' })

async function submit() {
  error.value = ''
  try {
    await auth.login(form.email, form.password)
    await cart.adoptAccountCart()
    await wishlist.fetch()
    router.push(String(route.query.redirect || '/'))
  } catch (e) {
    error.value = e instanceof ApiError ? Object.values(e.errors || { email: [e.message] }).flat().join(' ') : t('login_failed')
  }
}
</script>

<template>
  <section class="section">
    <div class="container narrow">
      <p class="eyebrow">{{ t('welcome_back') }}</p>
      <h2>{{ t('sign_in') }}</h2>
      <p class="muted">{{ t('login_for_cart') }}</p>
      <p class="muted">{{ t('demo_member') }}</p>
      <form class="form" @submit.prevent="submit">
        <div class="field"><label>{{ t('email') }}</label><input v-model="form.email" type="email" required /></div>
        <div class="field"><label>{{ t('password') }}</label><input v-model="form.password" type="password" required /></div>
        <p v-if="error" class="error-text">{{ error }}</p>
        <button class="btn" type="submit" :disabled="auth.loading">{{ t('sign_in') }}</button>
      </form>
      <p class="muted">{{ t('no_account') }} <RouterLink to="/register">{{ t('register') }}</RouterLink></p>
    </div>
  </section>
</template>

<style scoped>
.narrow { max-width: 460px; }
.form { display: grid; gap: 0.9rem; margin: 1.2rem 0; }
</style>
