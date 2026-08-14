<script setup lang="ts">
import { onMounted } from 'vue'
import { RouterView } from 'vue-router'
import AppNavbar from './components/AppNavbar.vue'
import AppFooter from './components/AppFooter.vue'
import CartDrawer from './components/CartDrawer.vue'
import { useAuthStore } from './stores/auth'
import { useWishlistStore } from './stores/wishlist'
import { useCartStore } from './stores/cart'
import { useLocaleStore } from './stores/locale'

const auth = useAuthStore()
const wishlist = useWishlistStore()
const cart = useCartStore()
useLocaleStore()

onMounted(async () => {
  if (auth.token) {
    await auth.fetchUser()
    if (auth.isAuthenticated) {
      await wishlist.fetch()
      await cart.pullFromServer()
    }
  }
})
</script>

<template>
  <AppNavbar />
  <main class="site-main">
    <RouterView />
  </main>
  <AppFooter />
  <CartDrawer />
</template>
