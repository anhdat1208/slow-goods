<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useWishlistStore } from '../stores/wishlist'
import { useI18n } from '../i18n'

const props = defineProps<{ productId: number }>()
const auth = useAuthStore()
const wishlist = useWishlistStore()
const router = useRouter()
const { t } = useI18n()

async function onClick() {
  if (!auth.isAuthenticated) {
    router.push({ name: 'login', query: { redirect: router.currentRoute.value.fullPath } })
    return
  }
  await wishlist.toggle(props.productId)
}
</script>

<template>
  <button class="wish" type="button" :aria-pressed="wishlist.has(productId)" @click="onClick">
    {{ wishlist.has(productId) ? t('saved') : t('save') }}
  </button>
</template>

<style scoped>
.wish {
  border: none;
  background: transparent;
  color: var(--muted);
  cursor: pointer;
  font-size: 0.85rem;
}
</style>
