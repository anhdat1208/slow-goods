<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import type { Product } from '../types'
import { money } from '../api/client'
import WishlistButton from './WishlistButton.vue'
import SafeImage from './SafeImage.vue'
import { useCartStore } from '../stores/cart'
import { useI18n } from '../i18n'
import { localizeProduct } from '../i18n/catalog'

const props = defineProps<{ product: Product }>()
const cart = useCartStore()
const { t, locale } = useI18n()
const localized = computed(() => localizeProduct(props.product, locale.value))

async function addToCart() {
  try {
    await cart.addProduct(props.product)
  } catch (e) {
    if (e instanceof Error && e.message === 'LOGIN_REQUIRED') return
    if (e instanceof Error && e.message.startsWith('STOCK:')) return
  }
}
</script>

<template>
  <article class="card">
    <RouterLink :to="`/products/${product.slug}`" class="media">
      <SafeImage :src="product.image_url" :alt="localized.name" />
    </RouterLink>
    <div class="body">
      <div class="top">
        <p class="eyebrow">{{ localized.category?.name }}</p>
        <WishlistButton :product-id="product.id" />
      </div>
      <RouterLink :to="`/products/${product.slug}`">
        <h3>{{ localized.name }}</h3>
      </RouterLink>
      <p class="muted">{{ localized.short_description }}</p>
      <div class="bottom">
        <strong>{{ money(product.price) }}</strong>
        <button class="btn" type="button" @click="addToCart">{{ t('add') }}</button>
      </div>
    </div>
  </article>
</template>

<style scoped>
.card {
  display: grid;
  gap: 0.9rem;
}

.media {
  overflow: hidden;
  border-radius: 18px;
  aspect-ratio: 4 / 5;
  background: var(--bg-deep);
}

.media :deep(img) {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.6s ease;
}

.card:hover .media :deep(img) {
  transform: scale(1.04);
}

.top {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

h3 {
  margin: 0.15rem 0 0.35rem;
  font-size: 1.2rem;
}

.bottom {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 0.8rem;
}

.btn {
  padding: 0.55rem 0.95rem;
}
</style>
