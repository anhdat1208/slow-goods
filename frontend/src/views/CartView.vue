<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { money } from '../api/client'
import { useCartStore } from '../stores/cart'
import { useI18n } from '../i18n'
import { localizeProduct } from '../i18n/catalog'
import SafeImage from '../components/SafeImage.vue'

const cart = useCartStore()
const { t, locale } = useI18n()
const items = computed(() =>
  cart.items.map((item) => ({
    ...item,
    product: item.product ? localizeProduct(item.product, locale.value) : item.product,
  })),
)
</script>

<template>
  <section class="section">
    <div class="container narrow">
      <div class="section-head">
        <div>
          <p class="eyebrow">{{ t('cart') }}</p>
          <h2>{{ t('your_selection') }}</h2>
        </div>
      </div>

      <div v-if="!cart.items.length" class="empty">
        <p class="muted">{{ t('empty_cart') }}</p>
        <RouterLink class="btn" to="/products">{{ t('cta_explore') }}</RouterLink>
      </div>

      <div v-else class="list">
        <div v-for="item in items" :key="item.product_id" class="row">
          <SafeImage :src="item.product?.image_url" :alt="item.product?.name" />
          <div>
            <h3>{{ item.product?.name }}</h3>
            <p class="muted">{{ money(item.product?.price || 0) }}</p>
            <div class="qty">
              <button type="button" @click="cart.decrement(item.product_id)">−</button>
              <span>{{ item.quantity }}</span>
              <button type="button" @click="cart.increment(item.product_id)">+</button>
            </div>
          </div>
          <div class="side">
            <strong>{{ money(Number(item.product?.price || 0) * item.quantity) }}</strong>
            <button class="btn-ghost" type="button" @click="cart.remove(item.product_id)">{{ t('remove') }}</button>
          </div>
        </div>

        <div class="summary">
          <span>{{ t('items_count', { n: cart.totalQuantity }) }}</span>
          <strong>{{ money(cart.subtotal) }}</strong>
        </div>
        <RouterLink class="btn" to="/checkout">{{ t('proceed_checkout') }}</RouterLink>
      </div>
    </div>
  </section>
</template>

<style scoped>
.narrow { max-width: 820px; }
.empty, .list { display: grid; gap: 1rem; }
.row {
  display: grid;
  grid-template-columns: 96px 1fr auto;
  gap: 1rem;
  padding: 1rem 0;
  border-bottom: 1px solid var(--line);
}
:deep(img) { width: 96px; height: 120px; object-fit: cover; border-radius: 12px; }
.qty, .summary { display: flex; align-items: center; gap: 0.7rem; }
.qty button {
  width: 28px; height: 28px; border-radius: 999px; border: 1px solid var(--line); background: white; cursor: pointer;
}
.summary { justify-content: space-between; margin-top: 0.5rem; }
.side { display: grid; justify-items: end; gap: 0.4rem; }
</style>
