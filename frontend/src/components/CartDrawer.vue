<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { money } from '../api/client'
import { useCartStore } from '../stores/cart'
import { useI18n } from '../i18n'
import { localizeProduct } from '../i18n/catalog'
import SafeImage from './SafeImage.vue'

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
  <div v-if="cart.open" class="overlay" @click.self="cart.open = false">
    <aside class="drawer fade-up">
      <div class="head">
        <h2>{{ t('cart') }}</h2>
        <button class="btn-ghost" type="button" @click="cart.open = false">{{ t('close') }}</button>
      </div>

      <div v-if="!cart.items.length" class="empty muted">{{ t('empty_cart_quiet') }}</div>

      <div v-else class="list">
        <div v-for="item in items" :key="item.product_id" class="row">
          <SafeImage :src="item.product?.image_url" :alt="item.product?.name" />
          <div>
            <strong>{{ item.product?.name }}</strong>
            <p class="muted">{{ money(item.product?.price || 0) }}</p>
            <div class="qty">
              <button type="button" @click="cart.decrement(item.product_id)">−</button>
              <span>{{ item.quantity }}</span>
              <button type="button" @click="cart.increment(item.product_id)">+</button>
            </div>
          </div>
          <button class="btn-ghost" type="button" @click="cart.remove(item.product_id)">{{ t('remove') }}</button>
        </div>
      </div>

      <div class="foot">
        <div class="totals">
          <span>{{ t('subtotal') }}</span>
          <strong>{{ money(cart.subtotal) }}</strong>
        </div>
        <RouterLink class="btn" to="/cart" @click="cart.open = false">{{ t('view_cart') }}</RouterLink>
        <RouterLink class="btn btn-secondary" to="/checkout" @click="cart.open = false">{{ t('checkout') }}</RouterLink>
      </div>
    </aside>
  </div>
</template>

<style scoped>
.overlay {
  position: fixed;
  inset: 0;
  background: rgba(20, 28, 24, 0.35);
  z-index: 50;
  display: flex;
  justify-content: flex-end;
}

.drawer {
  width: min(420px, 100%);
  height: 100%;
  background: var(--surface-solid);
  padding: 1.4rem;
  display: grid;
  grid-template-rows: auto 1fr auto;
  gap: 1rem;
  box-shadow: var(--shadow);
}

.head,
.totals {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.list {
  overflow: auto;
  display: grid;
  gap: 1rem;
  align-content: start;
}

.row {
  display: grid;
  grid-template-columns: 72px 1fr auto;
  gap: 0.8rem;
  align-items: start;
}

.row :deep(img) {
  width: 72px;
  height: 90px;
  object-fit: cover;
  border-radius: 10px;
}

.qty {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 0.4rem;
}

.qty button {
  width: 28px;
  height: 28px;
  border-radius: 999px;
  border: 1px solid var(--line);
  background: white;
  cursor: pointer;
}

.foot {
  display: grid;
  gap: 0.7rem;
}
</style>
