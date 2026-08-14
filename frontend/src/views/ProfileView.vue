<script setup lang="ts">
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useCartStore } from '../stores/cart'
import { useWishlistStore } from '../stores/wishlist'
import { useI18n } from '../i18n'

const auth = useAuthStore()
const cart = useCartStore()
const wishlist = useWishlistStore()
const router = useRouter()
const { t } = useI18n()

async function logout() {
  await auth.logout()
  wishlist.productIds = []
  wishlist.items = []
  cart.clear()
  router.push('/')
}
</script>

<template>
  <section class="section">
    <div class="container narrow">
      <p class="eyebrow">{{ t('nav_account') }}</p>
      <h2>{{ auth.user?.name }}</h2>
      <p class="muted">{{ auth.user?.email }}</p>

      <div class="links">
        <RouterLink to="/orders">{{ t('order_history') }}</RouterLink>
        <RouterLink to="/wishlist">{{ t('nav_wishlist') }}</RouterLink>
        <RouterLink v-if="auth.isAdmin" to="/admin">{{ t('admin_dashboard') }}</RouterLink>
        <button class="btn btn-secondary" type="button" @click="logout">{{ t('sign_out') }}</button>
      </div>
    </div>
  </section>
</template>

<style scoped>
.narrow { max-width: 520px; }
.links { display: grid; gap: 0.8rem; margin-top: 1.5rem; }
</style>
