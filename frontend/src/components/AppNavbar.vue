<script setup lang="ts">
import { RouterLink, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useCartStore } from '../stores/cart'
import { useI18n } from '../i18n'
import SearchBar from './SearchBar.vue'
import LanguageSwitch from './LanguageSwitch.vue'

const auth = useAuthStore()
const cart = useCartStore()
const { t } = useI18n()
const router = useRouter()

function openCart() {
  if (!auth.isAuthenticated) {
    router.push({ name: 'login', query: { redirect: '/cart' } })
    return
  }
  cart.openCart()
}
</script>

<template>
  <header class="nav">
    <div class="container nav-inner">
      <RouterLink class="brand" to="/">Slow Goods</RouterLink>
      <nav class="links">
        <RouterLink to="/products">{{ t('nav_collection') }}</RouterLink>
        <RouterLink to="/kits">{{ t('nav_kits') }}</RouterLink>
        <RouterLink to="/ask">{{ t('nav_ask') }}</RouterLink>
        <RouterLink v-if="auth.isAdmin" to="/admin">{{ t('nav_admin') }}</RouterLink>
      </nav>
      <div class="actions">
        <SearchBar compact />
        <LanguageSwitch />
        <RouterLink v-if="auth.isAuthenticated" to="/wishlist">{{ t('nav_wishlist') }}</RouterLink>
        <RouterLink v-if="auth.isAuthenticated" to="/profile">{{ t('nav_account') }}</RouterLink>
        <RouterLink v-else to="/login">{{ t('nav_signin') }}</RouterLink>
        <button class="cart-btn" type="button" @click="openCart">
          {{ t('nav_cart') }}
          <span>{{ cart.totalQuantity }}</span>
        </button>
      </div>
    </div>
  </header>
</template>

<style scoped>
.nav {
  position: sticky;
  top: 0;
  z-index: 40;
  backdrop-filter: blur(14px);
  background: rgba(232, 236, 230, 0.78);
  border-bottom: 1px solid var(--line);
}

.nav-inner {
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 1.25rem;
  align-items: center;
  min-height: 74px;
}

.brand {
  font-family: var(--font-display);
  font-size: 1.45rem;
  font-weight: 700;
}

.links,
.actions {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.links {
  justify-content: center;
}

.links a,
.actions a {
  color: var(--muted);
  font-size: 0.95rem;
}

.links a.router-link-active,
.actions a.router-link-active {
  color: var(--ink);
}

.cart-btn {
  border: 1px solid var(--line);
  background: rgba(255, 255, 255, 0.7);
  border-radius: 999px;
  padding: 0.55rem 0.9rem;
  cursor: pointer;
  display: inline-flex;
  gap: 0.45rem;
  align-items: center;
}

.cart-btn span {
  background: var(--accent);
  color: white;
  border-radius: 999px;
  min-width: 1.4rem;
  height: 1.4rem;
  display: grid;
  place-items: center;
  font-size: 0.75rem;
}

@media (max-width: 900px) {
  .nav-inner {
    grid-template-columns: 1fr auto;
  }

  .links {
    display: none;
  }
}
</style>
